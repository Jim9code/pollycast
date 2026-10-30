<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'INEC Delta State Election Portal - Bincom Test')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">

    <!-- Top Header & Navigation -->
    <header class="bg-emerald-900 text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-4 gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-700 flex items-center justify-center font-bold text-lg border border-emerald-500">
                        INEC
                    </div>
                    <div>
                        <h1 class="text-lg font-bold tracking-tight">Delta State (State ID: 25) Election Results Portal</h1>
                        <p class="text-xs text-emerald-200">2011 General Elections Collation &amp; Verification System</p>
                    </div>
                </div>

                <!-- Navigation Tabs for Question 1, Question 2, Question 3 -->
                <nav class="flex flex-wrap gap-2">
                    <a href="{{ route('polling-unit.index') }}"
                       class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('polling-unit.index') ? 'bg-white text-emerald-950 shadow-sm font-semibold' : 'bg-emerald-800/70 text-emerald-100 hover:bg-emerald-800' }}">
                        Q1: Polling Unit Results
                    </a>
                    <a href="{{ route('lga-results.index') }}"
                       class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('lga-results.index') ? 'bg-white text-emerald-950 shadow-sm font-semibold' : 'bg-emerald-800/70 text-emerald-100 hover:bg-emerald-800' }}">
                        Q2: Summed LGA Results
                    </a>
                    <a href="{{ route('polling-unit.create') }}"
                       class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('polling-unit.create') ? 'bg-white text-emerald-950 shadow-sm font-semibold' : 'bg-emerald-800/70 text-emerald-100 hover:bg-emerald-800' }}">
                        Q3: Add New PU Result
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Slide-in Toast Notifications (Top-Right) -->
    @if(session('success') || $errors->any())
        <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-md w-full px-4 sm:px-0 pointer-events-none">
            @if(session('success'))
                <div id="toast-success"
                     class="pointer-events-auto relative overflow-hidden rounded-2xl bg-white/95 backdrop-blur-md border border-emerald-200 shadow-2xl shadow-emerald-950/10 p-4 flex items-start gap-3.5 transform translate-x-full opacity-0 transition-all duration-500 ease-out"
                     role="alert">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="flex-1 pr-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Result Recorded</p>
                        <p class="text-sm font-medium text-slate-700 mt-0.5 leading-snug">
                            {{ session('success') }}
                        </p>
                    </div>
                    <button type="button" onclick="dismissToast('toast-success')"
                            class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg p-1.5 transition shrink-0"
                            aria-label="Close notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <!-- Auto-close progress bar -->
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-100">
                        <div id="toast-success-bar" class="h-full bg-emerald-600 transition-all ease-linear" style="width: 100%; transition-duration: 5000ms;"></div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div id="toast-error"
                     class="pointer-events-auto relative overflow-hidden rounded-2xl bg-white/95 backdrop-blur-md border border-red-200 shadow-2xl shadow-red-950/10 p-4 flex items-start gap-3.5 transform translate-x-full opacity-0 transition-all duration-500 ease-out"
                     role="alert">
                    <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 pr-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-red-700">Validation Error</p>
                        <ul class="list-disc list-inside text-xs font-medium text-slate-700 mt-1 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" onclick="dismissToast('toast-error')"
                            class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg p-1.5 transition shrink-0"
                            aria-label="Close notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-red-100">
                        <div id="toast-error-bar" class="h-full bg-red-600 transition-all ease-linear" style="width: 100%; transition-duration: 7000ms;"></div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
            <span>Bincom Preliminary Software Developer Test &bull; Built with Laravel MVC</span>
            <span>Database: <code>bincom_test.sql</code> (Delta State &bull; State ID: 25)</span>
        </div>
    </footer>

    <script>
        function dismissToast(id) {
            const toast = document.getElementById(id);
            if (!toast) return;
            toast.classList.remove('translate-x-0', 'opacity-100');
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 500);
        }

        document.addEventListener('DOMContentLoaded', () => {
            [
                { id: 'toast-success', barId: 'toast-success-bar', duration: 5000 },
                { id: 'toast-error', barId: 'toast-error-bar', duration: 7000 }
            ].forEach(({ id, barId, duration }) => {
                const toast = document.getElementById(id);
                const bar = document.getElementById(barId);
                if (!toast) return;

                // Slide in from the right
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-x-full', 'opacity-0');
                    toast.classList.add('translate-x-0', 'opacity-100');
                    if (bar) {
                        requestAnimationFrame(() => {
                            bar.style.width = '0%';
                        });
                    }
                });

                // Auto-close after duration (pause on hover)
                let timer = setTimeout(() => dismissToast(id), duration);

                toast.addEventListener('mouseenter', () => {
                    clearTimeout(timer);
                    if (bar) {
                        bar.style.transitionDuration = '200ms';
                        bar.style.width = '100%';
                    }
                });

                toast.addEventListener('mouseleave', () => {
                    if (bar) {
                        bar.style.transitionDuration = duration + 'ms';
                        bar.style.width = '0%';
                    }
                    timer = setTimeout(() => dismissToast(id), duration);
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>

