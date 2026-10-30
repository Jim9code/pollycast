@extends('layouts.app')

@section('title', 'Question 3: Store New Polling Unit Results | INEC Delta State')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-2">
            Question 3
        </span>
        <h2 class="text-2xl font-bold text-slate-900">Record Results for a New Polling Unit</h2>
        <p class="text-sm text-slate-600 mt-1">
            Use the chained combo boxes to select the LGA and Ward in Delta State, enter the new Polling Unit details, and input the scores for <strong>ALL {{ $parties->count() }} registered political parties</strong>.
        </p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('polling-unit.store', [], false) }}" class="space-y-6">
        @csrf

        <!-- Step 1: Location & Polling Unit Details -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-5">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">
                1. Polling Unit Location &amp; Metadata (Delta State - ID 25)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Chained Select 1: LGA -->
                <div>
                    <label for="create_lga_select" class="block text-sm font-medium text-slate-700 mb-1">
                        Local Government Area (LGA) <span class="text-red-500">*</span>
                    </label>
                    <select id="create_lga_select" name="lga_id" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                        <option value="">-- Select LGA --</option>
                        @foreach($lgas as $lga)
                            <option value="{{ $lga->lga_id }}" {{ (int) old('lga_id') === (int) $lga->lga_id ? 'selected' : '' }}>
                                {{ $lga->lga_name }} (ID: {{ $lga->lga_id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Chained Select 2: Ward -->
                <div>
                    <label for="create_ward_select" class="block text-sm font-medium text-slate-700 mb-1">
                        Ward (Chained to LGA) <span class="text-red-500">*</span>
                    </label>
                    <select id="create_ward_select" name="uniquewardid" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                        <option value="">-- Select LGA First --</option>
                    </select>
                </div>

                <!-- Polling Unit Name -->
                <div>
                    <label for="polling_unit_name" class="block text-sm font-medium text-slate-700 mb-1">
                        New Polling Unit Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="polling_unit_name" name="polling_unit_name"
                           value="{{ old('polling_unit_name') }}"
                           placeholder="e.g. Central Primary School Hall II" required maxlength="50"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                </div>

                <!-- Polling Unit Number / Code -->
                <div>
                    <label for="polling_unit_number" class="block text-sm font-medium text-slate-700 mb-1">
                        Polling Unit Number / Code <span class="text-xs text-slate-400">(Optional - auto-generated if blank)</span>
                    </label>
                    <input type="text" id="polling_unit_number" name="polling_unit_number"
                           value="{{ old('polling_unit_number') }}"
                           placeholder="e.g. DT2201025" maxlength="50"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                </div>

                <!-- Entered By User -->
                <div>
                    <label for="entered_by_user" class="block text-sm font-medium text-slate-700 mb-1">
                        Entered By (Presiding Officer / Agent) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="entered_by_user" name="entered_by_user"
                           value="{{ old('entered_by_user') }}"
                           placeholder="e.g. Chinedu Okafor" required maxlength="50"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                </div>

                <!-- Description -->
                <div>
                    <label for="polling_unit_description" class="block text-sm font-medium text-slate-700 mb-1">
                        Location Description <span class="text-xs text-slate-400">(Optional)</span>
                    </label>
                    <input type="text" id="polling_unit_description" name="polling_unit_description"
                           value="{{ old('polling_unit_description') }}"
                           placeholder="e.g. Opposite Town Hall Junction" maxlength="255"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                </div>
            </div>
        </div>

        <!-- Step 2: Scores for ALL Political Parties -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-3 gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        2. Enter Votes for ALL Registered Political Parties ({{ $parties->count() }} Parties)
                    </h3>
                    <p class="text-xs text-slate-500">
                        Each party score will be saved as an individual row in <code>announced_pu_results</code>.
                    </p>
                </div>
                <button type="button" id="fillDemoBtn"
                        class="text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-lg transition self-start sm:self-auto">
                    Auto-Fill Sample Scores
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($parties as $party)
                    @php
                        $abbr = $party->abbreviation; // 4-char abbreviation (e.g. LABOUR -> LABO)
                    @endphp
                    <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/60 hover:border-emerald-400 transition">
                        <label for="party_{{ $abbr }}" class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-slate-900 text-white">
                                {{ $party->partyname }}
                            </span>
                            <span class="text-xs text-slate-400 font-mono">DB Code: {{ $abbr }}</span>
                        </label>
                        <input type="number"
                               id="party_{{ $abbr }}"
                               name="party_scores[{{ $abbr }}]"
                               value="{{ old('party_scores.'.$abbr, 0) }}"
                               min="0"
                               required
                               class="party-score-input w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-base font-bold text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                    </div>
                @endforeach
            </div>

            <!-- Live Total Votes Summary & Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-slate-600">Total Votes Across All Parties:</span>
                    <span id="liveTotalVotes" class="text-xl font-bold text-emerald-700">0</span>
                </div>

                <button type="submit" id="submitResultBtn"
                        class="rounded-xl bg-emerald-700 hover:bg-emerald-800 disabled:opacity-60 text-white font-semibold py-3 px-8 text-sm shadow-sm transition">
                    Save Polling Unit &amp; All Party Results
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Prevent accidental double submission
    document.querySelector('form').addEventListener('submit', function () {
        const btn = document.getElementById('submitResultBtn');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Saving Results...';
        }
    });
    // 1. Chained Combo Box: LGA -> Ward
    const createLgaSelect = document.getElementById('create_lga_select');
    const createWardSelect = document.getElementById('create_ward_select');

    async function loadWardsForLga(lgaId) {
        if (!lgaId) {
            createWardSelect.innerHTML = '<option value="">-- Select LGA First --</option>';
            return;
        }

        createWardSelect.innerHTML = '<option value="">Loading wards...</option>';
        try {
            const response = await fetch(`/api/lgas/${lgaId}/wards`);
            const wards = await response.json();

            createWardSelect.innerHTML = '<option value="">-- Select Ward --</option>';
            wards.forEach(ward => {
                const option = document.createElement('option');
                option.value = ward.uniqueid;
                option.textContent = `${ward.ward_name} (Ward ID: ${ward.ward_id})`;
                createWardSelect.appendChild(option);
            });
        } catch (error) {
            createWardSelect.innerHTML = '<option value="">Error loading wards</option>';
        }
    }

    createLgaSelect.addEventListener('change', function () {
        loadWardsForLga(this.value);
    });

    // If an LGA was already selected (e.g., validation redirect), load its wards on page load
    if (createLgaSelect.value) {
        loadWardsForLga(createLgaSelect.value);
    }

    // 2. Live Total Votes Calculator
    const scoreInputs = document.querySelectorAll('.party-score-input');
    const liveTotalEl = document.getElementById('liveTotalVotes');

    function updateLiveTotal() {
        let sum = 0;
        scoreInputs.forEach(input => {
            const val = parseInt(input.value, 10);
            if (!isNaN(val) && val > 0) {
                sum += val;
            }
        });
        liveTotalEl.textContent = sum.toLocaleString();
    }

    scoreInputs.forEach(input => {
        input.addEventListener('input', updateLiveTotal);
    });
    updateLiveTotal();

    // 3. Auto-Fill Sample Scores button for quick assessor testing
    document.getElementById('fillDemoBtn').addEventListener('click', function () {
        scoreInputs.forEach(input => {
            input.value = Math.floor(Math.random() * 650) + 120;
        });
        updateLiveTotal();
    });
</script>
@endpush

