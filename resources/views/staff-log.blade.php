@extends('layouts.app')

@section('title', 'CRH Blood Bank — Staff Log')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/staff-accounts.css') }}">
    <style>
        .dashboard,
        .dashboard__content {
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
        }
        .dashboard__main {
            flex: 1;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 100%;
            padding: 0.6rem 0.85rem;
        }
        .module-page,
        .module-page__content,
        .staff-module,
        .tab-content-panel {
            flex: 1;
            min-height: 0;
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        .module-page__content {
            overflow: hidden;
            padding: 0.65rem 0.75rem;
        }
        .staff-module {
            gap: 0.45rem;
            overflow: hidden;
        }
        .module-controls-bar {
            margin: 0;
            padding-bottom: 0.35rem;
            flex-shrink: 0;
        }
        .module-controls-bar h2 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--color-red-primary);
        }
        .day-log-form {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .day-log-form label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #333;
        }
        .day-log-form input {
            height: 32px;
            border: 1.5px solid #e8c8c8;
            border-radius: 8px;
            padding: 0 8px;
            font-size: 0.75rem;
            font-family: inherit;
            color: #1e293b;
            background: #fff;
        }
        .staff-module .table-wrap {
            flex: 1;
            min-height: 0;
            width: 100%;
            max-height: none;
            overflow: auto;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
        }
        .staff-table th,
        .staff-table td {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        .staff-table th {
            font-weight: 600;
        }
        .staff-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #fafafa;
        }
    </style>
@endpush

@section('content')
    <div class="module-page">
        <div class="module-page__content">
            <div class="staff-module">
                <div class="module-controls-bar">
                    <h2>{{ $selectedStaff ? $selectedStaff->name.' — Login Records' : 'Staff Log' }}</h2>
                    @if ($selectedStaff)
                        <a class="btn-secondary" href="{{ route('staff-accounts') }}">Back to staff accounts</a>
                    @else
                        <form class="day-log-form" method="GET" action="{{ route('staff-log') }}">
                            <label for="log-date">Date</label>
                            <input id="log-date" type="date" name="date" value="{{ $logDate }}" onchange="this.form.submit()">
                        </form>
                    @endif
                </div>

                <section class="tab-content-panel">
                    <div class="table-wrap">
                        <table class="staff-table" id="day-log-table">
                            <thead>
                                <tr>
                                    @if ($selectedStaff)
                                        <th>Date</th>
                                    @else
                                        <th>Name</th>
                                    @endif
                                    <th>In</th>
                                    <th>Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dayLogs as $log)
                                    <tr>
                                        <td>{{ $selectedStaff ? $log->login_date?->format('M j, Y') : ($log->user?->name ?? 'Removed account') }}</td>
                                        <td>{{ $log->logged_in_at ? $log->logged_in_at->format('h:i A') : $log->login_time }}</td>
                                        <td>{{ $log->isOpen() ? 'Signed in' : ($log->logged_out_at ? $log->logged_out_at->format('h:i A') : $log->logout_time) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="empty-state">No staff signed in on this date.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
