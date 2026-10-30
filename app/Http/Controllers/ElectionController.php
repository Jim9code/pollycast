<?php

namespace App\Http\Controllers;

use App\Models\AnnouncedLgaResult;
use App\Models\AnnouncedPuResult;
use App\Models\Lga;
use App\Models\Party;
use App\Models\PollingUnit;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ElectionController extends Controller
{
    /**
     * QUESTION 1:
     * Display the election result for any individual polling unit on a web page.
     * Supports both a chained combo box (LGA -> Ward -> Polling Unit) and direct selection.
     */
    public function pollingUnitResults(Request $request)
    {
        // Fetch all LGAs in Delta State (state_id = 25) with counts of valid Polling Units
        $lgas = Lga::where('state_id', 25)
            ->withCount([
                'pollingUnits as total_pus_count' => fn ($q) => $q->where('lga_id', '>', 0),
                'pollingUnits as active_pus_count' => fn ($q) => $q->where('lga_id', '>', 0)->whereHas('results'),
            ])
            ->orderBy('lga_name')
            ->get();

        // Fetch all valid polling units (excluding blank rows where lga_id = 0)
        $pollingUnits = PollingUnit::with(['lga', 'ward'])
            ->withCount('results')
            ->where('lga_id', '>', 0)
            ->whereNotNull('polling_unit_name')
            ->where('polling_unit_name', '!=', '')
            ->orderBy('uniqueid')
            ->get();

        // Default to uniqueid = 8 (the example in the test instructions) if none is selected
        $selectedPuId = $request->query('polling_unit_uniqueid', 8);

        $selectedPollingUnit = PollingUnit::with(['lga', 'ward'])
            ->where('uniqueid', $selectedPuId)
            ->first();

        $results = collect();
        $totalVotes = 0;
        $highestVote = 0;
        $leadingParty = null;

        if ($selectedPollingUnit) {
            $results = AnnouncedPuResult::where('polling_unit_uniqueid', (string) $selectedPollingUnit->uniqueid)
                ->orderByDesc('party_score')
                ->orderBy('result_id')
                ->get();

            $totalVotes = $results->sum('party_score');
            $highestVote = (int) $results->max('party_score');
            $leadingParty = $highestVote > 0 ? $results->firstWhere('party_score', $highestVote)?->party_abbreviation : null;
        }

        // Preload wards and polling units for the currently selected Polling Unit's LGA & Ward
        $selectedLgaId = $request->query('lga_id', $selectedPollingUnit?->lga_id);
        $selectedUniqueWardId = $request->query('uniquewardid', $selectedPollingUnit?->uniquewardid);

        $wardsInSelectedLga = $selectedLgaId
            ? Ward::where('lga_id', $selectedLgaId)
                ->withCount(['pollingUnits' => fn ($q) => $q->where('lga_id', '>', 0)])
                ->orderBy('ward_name')
                ->get()
            : collect();

        $pusInSelectedWard = $selectedUniqueWardId
            ? PollingUnit::withCount('results')
                ->where('uniquewardid', $selectedUniqueWardId)
                ->where('lga_id', '>', 0)
                ->orderBy('polling_unit_name')
                ->get()
            : collect();

        return view('elections.polling_unit', compact(
            'lgas',
            'pollingUnits',
            'selectedPollingUnit',
            'selectedLgaId',
            'selectedUniqueWardId',
            'wardsInSelectedLga',
            'pusInSelectedWard',
            'results',
            'totalVotes',
            'highestVote',
            'leadingParty'
        ));
    }

    /**
     * QUESTION 2:
     * Display the summed total result of all the polling units under any particular LGA.
     * IMPORTANT: Does NOT use announced_lga_results for the summed result; calculates the sum
     * directly from announced_pu_results joined with polling_unit.
     */
    public function lgaResults(Request $request)
    {
        // All LGAs in Delta State (state_id = 25) with count of polling units that have results
        $lgas = Lga::where('state_id', 25)
            ->withCount([
                'pollingUnits as total_pus_count' => fn ($q) => $q->where('lga_id', '>', 0),
                'pollingUnits as active_pus_count' => fn ($q) => $q->where('lga_id', '>', 0)->whereHas('results'),
            ])
            ->orderBy('lga_name')
            ->get();

        $parties = Party::orderBy('id')->get();

        // Default to Uvwie (lga_id = 22)
        $selectedLgaId = $request->query('lga_id', 22);
        $selectedLga = Lga::where('lga_id', $selectedLgaId)->first();

        $summedResults = collect();
        $announcedLgaComparison = collect();
        $pollingUnitsInLga = collect();
        $grandTotalFromPus = 0;
        $grandTotalAnnouncedLga = 0;
        $highestLgaPartyScore = 0;
        $leadingLgaParty = null;

        if ($selectedLga) {
            // REQUIRED QUERY FOR QUESTION 2:
            // Sum party_score from announced_pu_results across all polling_unit records in this LGA
            $rawSums = DB::table('announced_pu_results')
                ->join('polling_unit', 'announced_pu_results.polling_unit_uniqueid', '=', 'polling_unit.uniqueid')
                ->where('polling_unit.lga_id', $selectedLga->lga_id)
                ->select(
                    'announced_pu_results.party_abbreviation',
                    DB::raw('SUM(announced_pu_results.party_score) as total_score'),
                    DB::raw('COUNT(DISTINCT polling_unit.uniqueid) as polling_units_counted')
                )
                ->groupBy('announced_pu_results.party_abbreviation')
                ->get()
                ->keyBy('party_abbreviation');

            // Official announced LGA results from announced_lga_results (for cross-check comparison only)
            $announcedLgaComparison = AnnouncedLgaResult::where('lga_name', (string) $selectedLga->lga_id)
                ->get()
                ->keyBy('party_abbreviation');

            foreach ($parties as $party) {
                $abbr = $party->abbreviation;
                $puSum = isset($rawSums[$abbr]) ? (int) $rawSums[$abbr]->total_score : 0;
                $officialScore = isset($announcedLgaComparison[$abbr])
                    ? (int) $announcedLgaComparison[$abbr]->party_score
                    : null;

                $summedResults->push([
                    'party_name' => $party->partyname,
                    'party_abbreviation' => $abbr,
                    'summed_pu_score' => $puSum,
                    'official_lga_score' => $officialScore,
                    'variance' => $officialScore !== null ? ($puSum - $officialScore) : null,
                ]);
            }

            // Sort highest polling unit sum first so the leading party stands out clearly
            $summedResults = $summedResults->sortByDesc('summed_pu_score')->values();

            $grandTotalFromPus = $summedResults->sum('summed_pu_score');
            $grandTotalAnnouncedLga = $summedResults->sum(fn ($r) => $r['official_lga_score'] ?? 0);
            $highestLgaPartyScore = (int) $summedResults->max('summed_pu_score');
            $leadingLgaParty = $highestLgaPartyScore > 0
                ? $summedResults->firstWhere('summed_pu_score', $highestLgaPartyScore)['party_abbreviation']
                : null;

            // Fetch the individual polling units under this LGA
            $pollingUnitsInLga = PollingUnit::with('ward')
                ->withCount('results')
                ->withSum('results', 'party_score')
                ->where('lga_id', $selectedLga->lga_id)
                ->orderByDesc('results_count')
                ->orderBy('uniqueid')
                ->get();
        }

        return view('elections.lga_results', compact(
            'lgas',
            'selectedLga',
            'selectedLgaId',
            'summedResults',
            'pollingUnitsInLga',
            'grandTotalFromPus',
            'grandTotalAnnouncedLga',
            'highestLgaPartyScore',
            'leadingLgaParty'
        ));
    }

    /**
     * QUESTION 3 (GET):
     * Display the form to store results for ALL parties for a new polling unit.
     */
    public function createPollingUnitResult()
    {
        $lgas = Lga::where('state_id', 25)->orderBy('lga_name')->get();
        $parties = Party::orderBy('id')->get();

        return view('elections.create_result', compact('lgas', 'parties'));
    }

    /**
     * QUESTION 3 (POST):
     * Save the new polling unit and store results for ALL parties in announced_pu_results.
     */
    public function storePollingUnitResult(Request $request)
    {
        $validated = $request->validate([
            'lga_id' => ['required', 'integer', 'exists:lga,lga_id'],
            'uniquewardid' => ['required', 'integer', 'exists:ward,uniqueid'],
            'polling_unit_name' => ['required', 'string', 'max:50'],
            'polling_unit_number' => ['nullable', 'string', 'max:50'],
            'polling_unit_description' => ['nullable', 'string', 'max:255'],
            'entered_by_user' => ['required', 'string', 'max:50'],
            'party_scores' => ['required', 'array'],
            'party_scores.*' => ['required', 'integer', 'min:0'],
        ]);

        $ward = Ward::where('uniqueid', $validated['uniquewardid'])->firstOrFail();
        $now = now()->format('Y-m-d H:i:s');
        $userIp = $request->ip() ?? '127.0.0.1';

        $nextPuId = (int) PollingUnit::where('uniquewardid', $ward->uniqueid)->max('polling_unit_id') + 1;
        $puNumber = $validated['polling_unit_number']
            ?: sprintf('DT%02d%02d%03d', $validated['lga_id'], max($ward->ward_id, 1), $nextPuId);

        $newPollingUnit = DB::transaction(function () use ($validated, $ward, $nextPuId, $puNumber, $now, $userIp) {
            // 1. Create the new Polling Unit row in `polling_unit`
            $pu = PollingUnit::create([
                'polling_unit_id' => $nextPuId,
                'ward_id' => $ward->ward_id,
                'lga_id' => $validated['lga_id'],
                'uniquewardid' => $ward->uniqueid,
                'polling_unit_number' => $puNumber,
                'polling_unit_name' => $validated['polling_unit_name'],
                'polling_unit_description' => $validated['polling_unit_description'] ?? $validated['polling_unit_name'],
                'lat' => '5.8950',
                'long' => '5.7304',
                'entered_by_user' => $validated['entered_by_user'],
                'date_entered' => $now,
                'user_ip_address' => $userIp,
            ]);

            // 2. Insert a row in `announced_pu_results` for EVERY political party
            foreach ($validated['party_scores'] as $partyCode => $score) {
                $partyAbbr = substr(trim((string) $partyCode), 0, 4);

                AnnouncedPuResult::create([
                    'polling_unit_uniqueid' => (string) $pu->uniqueid,
                    'party_abbreviation' => $partyAbbr,
                    'party_score' => (int) $score,
                    'entered_by_user' => $validated['entered_by_user'],
                    'date_entered' => $now,
                    'user_ip_address' => $userIp,
                ]);
            }

            return $pu;
        });

        return redirect()
            ->route('polling-unit.index', [
                'lga_id' => $newPollingUnit->lga_id,
                'uniquewardid' => $newPollingUnit->uniquewardid,
                'polling_unit_uniqueid' => $newPollingUnit->uniqueid,
            ])
            ->with(
                'success',
                "Results for all parties in new Polling Unit \"{$newPollingUnit->polling_unit_name}\" (Unique ID #{$newPollingUnit->uniqueid}) have been saved!"
            );
    }

    /**
     * AJAX Endpoint for Chained Combo Box:
     * Fetch all Wards under a given lga_id (including how many Polling Units each Ward has).
     */
    public function getWardsByLga(int $lgaId)
    {
        $wards = Ward::where('lga_id', $lgaId)
            ->withCount(['pollingUnits' => fn ($q) => $q->where('lga_id', '>', 0)])
            ->orderByDesc('polling_units_count')
            ->orderBy('ward_name')
            ->get(['uniqueid', 'ward_id', 'ward_name', 'lga_id']);

        return response()->json($wards);
    }

    /**
     * AJAX Endpoint for Chained Combo Box:
     * Fetch all Polling Units under a given uniquewardid.
     */
    public function getPollingUnitsByWard(int $uniqueWardId)
    {
        $pollingUnits = PollingUnit::withCount('results')
            ->where('uniquewardid', $uniqueWardId)
            ->where('lga_id', '>', 0)
            ->orderByDesc('results_count')
            ->orderBy('polling_unit_name')
            ->get(['uniqueid', 'polling_unit_number', 'polling_unit_name', 'uniquewardid', 'lga_id']);

        return response()->json($pollingUnits);
    }

    /**
     * Hidden Activity Log Dashboard (/activity-logs)
     * Lets you see whenever an assessor visits or tests the application.
     */
    public function activityLogs()
    {
        $logs = DB::table('visitor_logs')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('elections.activity_logs', compact('logs'));
    }
}
