@extends('layouts.app')

@section('title', 'Question 1: Individual Polling Unit Results | INEC Delta State')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-2">
                    Question 1
                </span>
                <h2 class="text-2xl font-bold text-slate-900">Individual Polling Unit Result</h2>
                <p class="text-sm text-slate-600 mt-1">
                    Select any Polling Unit in Delta State (State ID: 25) using the chained combo boxes (LGA &rarr; Ward &rarr; Polling Unit) or quick selector below.
                </p>
            </div>
            <a href="{{ route('polling-unit.create') }}"
               class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-medium transition shrink-0">
                + Enter New Polling Unit Result
            </a>
        </div>

        <!-- Chained Combo Box Form (LGA -> Ward -> Polling Unit) -->
        <form method="GET" action="{{ route('polling-unit.index') }}" id="chainedForm" class="mt-6 pt-6 border-t border-slate-100">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">
                Filter by Chained Combo Box (LGA &rarr; Ward &rarr; Polling Unit)
            </p>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- Step 1: Select LGA -->
                <div>
                    <label for="lga_select" class="block text-xs font-medium text-slate-700 mb-1">1. Local Government Area (LGA)</label>
                    <select id="lga_select" name="lga_id"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                        <option value="">-- Select LGA --</option>
                        @foreach($lgas as $lga)
                            <option value="{{ $lga->lga_id }}" {{ (int) $selectedLgaId === (int) $lga->lga_id ? 'selected' : '' }}>
                                {{ $lga->lga_name }} ({{ $lga->active_pus_count }}/{{ $lga->total_pus_count }} PUs with results)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Step 2: Select Ward (Chained to LGA) -->
                <div>
                    <label for="ward_select" class="block text-xs font-medium text-slate-700 mb-1">2. Ward</label>
                    <select id="ward_select" name="uniquewardid"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                        <option value="">-- Select Ward --</option>
                        @foreach($wardsInSelectedLga as $ward)
                            <option value="{{ $ward->uniqueid }}" {{ (int) $selectedUniqueWardId === (int) $ward->uniqueid ? 'selected' : '' }}>
                                {{ $ward->ward_name }} ({{ $ward->polling_units_count }} PUs)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Step 3: Select Polling Unit (Chained to Ward) -->
                <div>
                    <label for="pu_select" class="block text-xs font-medium text-slate-700 mb-1">3. Polling Unit</label>
                    <select id="pu_select" name="polling_unit_uniqueid" required onchange="if(this.value) this.form.submit()"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                        <option value="">-- Select Polling Unit --</option>
                        @foreach($pusInSelectedWard as $pu)
                            <option value="{{ $pu->uniqueid }}" {{ $selectedPollingUnit && (int) $selectedPollingUnit->uniqueid === (int) $pu->uniqueid ? 'selected' : '' }}>
                                [ID: {{ $pu->uniqueid }}] {{ $pu->polling_unit_name }} {{ $pu->results_count > 0 ? '✓ ('.$pu->results_count.' scores)' : '(0 scores)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-4 text-sm transition">
                        View PU Result
                    </button>
                </div>
            </div>
        </form>

        <!-- Quick Direct Selector (for fast testing of any Polling Unit) -->
        <form method="GET" action="{{ route('polling-unit.index') }}" class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3">
            <label for="quick_pu_select" class="text-xs font-medium text-slate-600 shrink-0">
                Or Quick-Jump to any Polling Unit:
            </label>
            <select id="quick_pu_select" name="polling_unit_uniqueid" onchange="this.form.submit()"
                    class="flex-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-1.5 text-sm focus:border-emerald-600 focus:outline-none">
                @foreach($pollingUnits as $pu)
                    <option value="{{ $pu->uniqueid }}" {{ $selectedPollingUnit && (int) $selectedPollingUnit->uniqueid === (int) $pu->uniqueid ? 'selected' : '' }}>
                        UniqueID #{{ $pu->uniqueid }} — {{ $pu->polling_unit_name }} ({{ $pu->polling_unit_number ?: 'No Code' }}) | LGA: {{ $pu->lga?->lga_name ?? 'N/A' }} {{ $pu->results_count > 0 ? '✓ ['.$pu->results_count.' party scores]' : '[No announced scores]' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Selected Polling Unit Details & Results Table -->
    @if($selectedPollingUnit)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <!-- Polling Unit Metadata Banner -->
            <div class="bg-slate-900 text-white p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-emerald-300 font-medium mb-1">
                            <span>Unique ID: #{{ $selectedPollingUnit->uniqueid }}</span>
                            <span>&bull;</span>
                            <span>Code: {{ $selectedPollingUnit->polling_unit_number ?: 'N/A' }}</span>
                            <span>&bull;</span>
                            <span>State: Delta (25)</span>
                        </div>
                        <h3 class="text-xl font-bold">{{ $selectedPollingUnit->polling_unit_name ?: 'Unnamed Polling Unit' }}</h3>
                        <p class="text-xs text-slate-300 mt-1">
                            LGA: <strong class="text-white">{{ $selectedPollingUnit->lga?->lga_name ?? 'Unknown LGA' }}</strong>
                            &nbsp;|&nbsp;
                            Ward: <strong class="text-white">{{ $selectedPollingUnit->ward?->ward_name ?? 'Ward ID '.$selectedPollingUnit->ward_id }}</strong>
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @if($leadingParty)
                            <div class="bg-emerald-950/80 border border-emerald-700/70 rounded-xl px-4 py-3 text-right">
                                <span class="block text-xs text-emerald-300 uppercase tracking-wider">Leading Party</span>
                                <span class="text-xl font-bold text-white">{{ $leadingParty }} <span class="text-xs font-normal text-emerald-300">({{ number_format($highestVote) }})</span></span>
                            </div>
                        @endif
                        <div class="bg-slate-800 border border-slate-700 rounded-xl px-5 py-3 text-right">
                            <span class="block text-xs text-slate-400 uppercase tracking-wider">Total Votes Cast</span>
                            <span class="text-2xl font-bold text-emerald-400">{{ number_format($totalVotes) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Table -->
            @if($results->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-6">#</th>
                                <th class="py-3.5 px-6">Political Party</th>
                                <th class="py-3.5 px-6 text-right">Party Score (Votes)</th>
                                <th class="py-3.5 px-6 w-1/3">Vote Share</th>
                                <th class="py-3.5 px-6">Entered By</th>
                                <th class="py-3.5 px-6">Date Entered</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm">
                            @foreach($results as $index => $row)
                                @php
                                    $percentage = $totalVotes > 0 ? round(($row->party_score / $totalVotes) * 100, 1) : 0;
                                    $isWinner = $highestVote > 0 && (int) $row->party_score === $highestVote;
                                @endphp
                                <tr class="{{ $isWinner ? 'bg-emerald-50/50' : 'hover:bg-slate-50/80' }} transition">
                                    <td class="py-3.5 px-6 text-slate-400 font-medium">{{ $index + 1 }}</td>
                                    <td class="py-3.5 px-6">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold {{ $isWinner ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-800 border border-slate-300' }}">
                                                {{ $row->party_abbreviation }}
                                            </span>
                                            @if($isWinner)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                    Leading
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-6 text-right font-bold text-slate-900">
                                        {{ number_format($row->party_score) }}
                                    </td>
                                    <td class="py-3.5 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                                <div class="{{ $isWinner ? 'bg-emerald-600' : 'bg-slate-600' }} h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-slate-600 w-12 text-right">{{ $percentage }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-6 text-slate-600">
                                        {{ $row->entered_by_user ?: 'System / Unspecified' }}
                                    </td>
                                    <td class="py-3.5 px-6 text-slate-500 text-xs">
                                        {{ $row->date_entered }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-900">
                                <td colspan="2" class="py-4 px-6">Total Recorded Votes ({{ $results->count() }} entries)</td>
                                <td class="py-4 px-6 text-right text-emerald-700 text-base">{{ number_format($totalVotes) }}</td>
                                <td colspan="3" class="py-4 px-6 text-xs font-normal text-slate-500">
                                    Joined via <code>polling_unit.uniqueid = announced_pu_results.polling_unit_uniqueid ({{ $selectedPollingUnit->uniqueid }})</code>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="p-10 text-center">
                    <p class="text-slate-700 font-semibold text-base">No announced results found in <code>announced_pu_results</code> for Polling Unit #{{ $selectedPollingUnit->uniqueid }}.</p>
                    <p class="text-slate-500 text-sm mt-1">
                        In the provided <code>bincom_test.sql</code> dataset, Polling Units with Unique IDs <strong>#8 through #27</strong> contain announced party scores.
                    </p>
                    <div class="mt-4 flex justify-center gap-3">
                        <a href="{{ route('polling-unit.index', ['polling_unit_uniqueid' => 8]) }}"
                           class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800">
                            View Example PU #8 (Sapele Ward 8)
                        </a>
                        <a href="{{ route('polling-unit.create') }}"
                           class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50">
                            Record New Polling Unit Result
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Chained Combo Box Logic (LGA -> Ward -> Polling Unit) using Fetch API
    const lgaSelect = document.getElementById('lga_select');
    const wardSelect = document.getElementById('ward_select');
    const puSelect = document.getElementById('pu_select');

    lgaSelect.addEventListener('change', async function () {
        const lgaId = this.value;
        wardSelect.innerHTML = '<option value="">Loading wards...</option>';
        puSelect.innerHTML = '<option value="">-- Select Ward First --</option>';

        if (!lgaId) {
            wardSelect.innerHTML = '<option value="">-- Select LGA First --</option>';
            return;
        }

        try {
            const response = await fetch(`/api/lgas/${lgaId}/wards`);
            const wards = await response.json();

            wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';
            wards.forEach(ward => {
                const option = document.createElement('option');
                option.value = ward.uniqueid;
                option.textContent = `${ward.ward_name} (${ward.polling_units_count ?? 0} PUs)`;
                wardSelect.appendChild(option);
            });
        } catch (error) {
            wardSelect.innerHTML = '<option value="">Error loading wards</option>';
        }
    });

    wardSelect.addEventListener('change', async function () {
        const uniqueWardId = this.value;
        puSelect.innerHTML = '<option value="">Loading polling units...</option>';

        if (!uniqueWardId) {
            puSelect.innerHTML = '<option value="">-- Select Ward First --</option>';
            return;
        }

        try {
            const response = await fetch(`/api/wards/${uniqueWardId}/polling-units`);
            const pollingUnits = await response.json();

            if (pollingUnits.length === 0) {
                puSelect.innerHTML = '<option value="">No polling units found in this ward</option>';
                return;
            }

            puSelect.innerHTML = '<option value="">-- Select Polling Unit --</option>';
            pollingUnits.forEach(pu => {
                const option = document.createElement('option');
                option.value = pu.uniqueid;
                const badge = pu.results_count > 0 ? `✓ (${pu.results_count} scores)` : `(0 scores)`;
                option.textContent = `[ID: ${pu.uniqueid}] ${pu.polling_unit_name} ${badge}`;
                puSelect.appendChild(option);
            });
        } catch (error) {
            puSelect.innerHTML = '<option value="">Error loading polling units</option>';
        }
    });
</script>
@endpush
