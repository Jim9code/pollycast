@extends('layouts.app')

@section('title', 'Question 2: Summed LGA Results | INEC Delta State')

@section('content')
<div class="space-y-6">
    <!-- Header & LGA Select Box -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-2">
                    Question 2
                </span>
                <h2 class="text-2xl font-bold text-slate-900">Summed Polling Unit Results by LGA</h2>
                <p class="text-sm text-slate-600 mt-1">
                    Calculates the summed total score for each party across all Polling Units (<code>announced_pu_results</code> joined with <code>polling_unit</code>) under the selected Local Government Area.
                </p>
            </div>
        </div>

        <!-- Select Box to Choose Local Government -->
        <form method="GET" action="{{ route('lga-results.index', [], false) }}" class="mt-6 pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <label for="lga_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                    Select Local Government Area (Delta State - ID 25)
                </label>
                <select id="lga_id" name="lga_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    @foreach($lgas as $lga)
                        <option value="{{ $lga->lga_id }}" {{ (int) $selectedLgaId === (int) $lga->lga_id ? 'selected' : '' }}>
                            {{ $lga->lga_name }} (LGA ID: {{ $lga->lga_id }}) — {{ $lga->active_pus_count > 0 ? '✓ '.$lga->active_pus_count.' PUs with results' : $lga->total_pus_count.' PUs (0 results)' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-medium py-2.5 px-6 text-sm transition shrink-0">
                Calculate Summed Result
            </button>
        </form>
    </div>

    @if($selectedLga)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-900 text-white rounded-xl p-5 shadow-sm">
                <span class="text-xs uppercase tracking-wider text-slate-400">Selected Local Government</span>
                <h3 class="text-xl font-bold mt-1">{{ $selectedLga->lga_name }}</h3>
                <p class="text-xs text-emerald-300 mt-1">LGA ID: {{ $selectedLga->lga_id }} &bull; Delta State (25)</p>
            </div>

            <div class="bg-emerald-900 text-white rounded-xl p-5 shadow-sm">
                <span class="text-xs uppercase tracking-wider text-emerald-200">Summed Total from PUs (Required)</span>
                <h3 class="text-2xl font-bold mt-1 text-white">{{ number_format($grandTotalFromPus) }} <span class="text-sm font-normal text-emerald-200">votes</span></h3>
                <p class="text-xs text-emerald-200 mt-1">
                    Across {{ $pollingUnitsInLga->where('results_count', '>', 0)->count() }} active PUs ({{ $pollingUnitsInLga->count() }} total)
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <span class="text-xs uppercase tracking-wider text-slate-500">Leading Party in PU Sum</span>
                <h3 class="text-2xl font-bold mt-1 text-emerald-700">
                    {{ $leadingLgaParty ?: 'N/A' }}
                    @if($highestLgaPartyScore > 0)
                        <span class="text-sm font-medium text-slate-500">({{ number_format($highestLgaPartyScore) }})</span>
                    @endif
                </h3>
                <p class="text-xs text-slate-500 mt-1">Highest summed score across polling units</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <span class="text-xs uppercase tracking-wider text-slate-500">Announced LGA Table (Comparison)</span>
                <h3 class="text-2xl font-bold mt-1 text-slate-800">{{ number_format($grandTotalAnnouncedLga) }} <span class="text-sm font-normal text-slate-500">votes</span></h3>
                <p class="text-xs text-slate-500 mt-1">
                    From <code>announced_lga_results</code> for cross-check
                </p>
            </div>
        </div>

        <!-- Summed Party Results Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        Summed Polling Unit Results for {{ $selectedLga->lga_name }} LGA
                    </h3>
                    <p class="text-xs text-slate-500">
                        Calculated via <code>SUM(announced_pu_results.party_score)</code> joined with <code>polling_unit.lga_id = {{ $selectedLga->lga_id }}</code>
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-6">Party</th>
                            <th class="py-3.5 px-6 text-right bg-emerald-50/70 text-emerald-900">
                                Summed Polling Units Total (Required)
                            </th>
                            <th class="py-3.5 px-6 w-1/3">Share of PU Sum</th>
                            <th class="py-3.5 px-6 text-right text-slate-500">
                                Announced LGA Result (Comparison)
                            </th>
                            <th class="py-3.5 px-6 text-right text-slate-500">
                                Variance
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-sm">
                        @foreach($summedResults as $row)
                            @php
                                $share = $grandTotalFromPus > 0
                                    ? round(($row['summed_pu_score'] / $grandTotalFromPus) * 100, 1)
                                    : 0;
                                $isLeader = $highestLgaPartyScore > 0 && $row['summed_pu_score'] === $highestLgaPartyScore;
                            @endphp
                            <tr class="{{ $isLeader ? 'bg-emerald-50/40' : 'hover:bg-slate-50/80' }} transition">
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold {{ $isLeader ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-800 border border-slate-300' }}">
                                            {{ $row['party_abbreviation'] }}
                                        </span>
                                        @if($row['party_name'] !== $row['party_abbreviation'])
                                            <span class="text-xs text-slate-500">({{ $row['party_name'] }})</span>
                                        @endif
                                        @if($isLeader)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                Leading
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-6 text-right font-bold text-base bg-emerald-50/40 text-emerald-900">
                                    {{ number_format($row['summed_pu_score']) }}
                                </td>
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                            <div class="{{ $isLeader ? 'bg-emerald-600' : 'bg-slate-600' }} h-2.5 rounded-full" style="width: {{ $share }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-slate-600 w-12 text-right">{{ $share }}%</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-6 text-right text-slate-600">
                                    {{ $row['official_lga_score'] !== null ? number_format($row['official_lga_score']) : 'N/A' }}
                                </td>
                                <td class="py-3.5 px-6 text-right text-xs font-medium {{ ($row['variance'] ?? 0) < 0 ? 'text-amber-700' : 'text-slate-600' }}">
                                    @if($row['variance'] !== null)
                                        {{ $row['variance'] > 0 ? '+' : '' }}{{ number_format($row['variance']) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 border-t-2 border-slate-200 font-bold text-slate-900">
                            <td class="py-4 px-6">Grand Total</td>
                            <td class="py-4 px-6 text-right text-emerald-800 text-base bg-emerald-100/60">
                                {{ number_format($grandTotalFromPus) }}
                            </td>
                            <td class="py-4 px-6 text-xs font-normal text-slate-500">100% of summed polling units</td>
                            <td class="py-4 px-6 text-right text-slate-700">
                                {{ number_format($grandTotalAnnouncedLga) }}
                            </td>
                            <td class="py-4 px-6 text-right text-xs text-slate-600">
                                {{ number_format($grandTotalFromPus - $grandTotalAnnouncedLga) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Breakdown of Individual Polling Units in this LGA -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h4 class="text-sm font-bold text-slate-900">
                    Polling Units Under {{ $selectedLga->lga_name }} Included in Calculation ({{ $pollingUnitsInLga->count() }} Polling Units)
                </h4>
            </div>
            @if($pollingUnitsInLga->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-6">PU Unique ID</th>
                                <th class="py-3 px-6">Polling Unit Name</th>
                                <th class="py-3 px-6">PU Number</th>
                                <th class="py-3 px-6">Ward</th>
                                <th class="py-3 px-6 text-right">Party Rows</th>
                                <th class="py-3 px-6 text-right">PU Total Votes</th>
                                <th class="py-3 px-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($pollingUnitsInLga as $pu)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-6 font-medium text-slate-700">#{{ $pu->uniqueid }}</td>
                                    <td class="py-3 px-6 font-medium text-slate-900">{{ $pu->polling_unit_name ?: 'Unnamed PU' }}</td>
                                    <td class="py-3 px-6 text-slate-500">{{ $pu->polling_unit_number ?: 'N/A' }}</td>
                                    <td class="py-3 px-6 text-slate-600">{{ $pu->ward?->ward_name ?? 'Ward #'.$pu->ward_id }}</td>
                                    <td class="py-3 px-6 text-right">{{ $pu->results_count }}</td>
                                    <td class="py-3 px-6 text-right font-semibold text-emerald-700">
                                        {{ number_format($pu->results_sum_party_score ?? 0) }}
                                    </td>
                                    <td class="py-3 px-6 text-right">
                                        <a href="{{ route('polling-unit.index', ['polling_unit_uniqueid' => $pu->uniqueid]) }}"
                                           class="text-xs font-semibold text-emerald-700 hover:underline">
                                            Inspect PU &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center text-sm text-slate-500">
                    No polling units are registered under this LGA in the database.
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
