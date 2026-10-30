@extends('layouts.app')

@section('title', 'Visitor & Assessor Activity Logs | INEC Delta State')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-2">
                Live Audit Trail
            </span>
            <h2 class="text-2xl font-bold text-slate-900">Visitor &amp; Assessor Activity Logs</h2>
            <p class="text-sm text-slate-600 mt-1">
                Tracks every page view, chained dropdown selection, and new Polling Unit submission in real time.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-slate-100 rounded-xl px-4 py-2.5 text-right">
                <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Actions Logged</span>
                <span class="text-xl font-bold text-slate-900">{{ $logs->count() }}</span>
            </div>
            <a href="{{ route('activity.logs', [], false) }}"
               class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-medium transition">
                Refresh Logs
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        @if($logs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-5">Time (UTC)</th>
                            <th class="py-3.5 px-5">Action / Page Visited</th>
                            <th class="py-3.5 px-5">Method &amp; URL</th>
                            <th class="py-3.5 px-5">IP Address</th>
                            <th class="py-3.5 px-5">Device / Browser</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-5 text-xs font-mono text-slate-600 whitespace-nowrap">
                                    {{ $log->visited_at }}
                                </td>
                                <td class="py-3.5 px-5 font-semibold text-slate-900">
                                    {{ $log->action_label }}
                                </td>
                                <td class="py-3.5 px-5 text-xs font-mono text-slate-500">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $log->method === 'POST' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $log->method }}
                                    </span>
                                    <span class="ml-1">{{ $log->path }}</span>
                                </td>
                                <td class="py-3.5 px-5 text-xs font-mono text-emerald-700 whitespace-nowrap">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="py-3.5 px-5 text-xs text-slate-500 max-w-xs truncate" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-10 text-center text-sm text-slate-500">
                No visitor activity recorded yet. Visit any page on the portal and refresh!
            </div>
        @endif
    </div>
</div>
@endsection
