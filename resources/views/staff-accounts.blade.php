@extends('layouts.app')

@section('title', 'CRH Blood Bank — Staff Accounts')

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
        .module-page {
            flex: 1;
            min-height: 0;
            width: 100%;
            max-width: 100%;
            display: flex;
            flex-direction: column;
        }
        .module-page__content {
            flex: 1;
            min-height: 0;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 0.65rem 0.75rem;
        }
        .flash-banner {
            margin: 0 0 0.45rem;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
        }
        .staff-module {
            flex: 1;
            min-height: 0;
            width: 100%;
            display: flex;
            flex-direction: column;
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
        .filters-bar,
        .staff-count-badge {
            flex-shrink: 0;
        }
        .search-input,
        .filter-select,
        .btn-primary {
            font-size: 0.75rem;
        }
        .search-input,
        .filter-select {
            height: 32px;
            box-sizing: border-box;
        }
        .tab-content-panel {
            flex: 1;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }
        .staff-module .table-wrap {
            flex: 1;
            min-height: 0;
            width: 100%;
            max-height: 420px;
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
        .staff-login-cell {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .staff-session {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
            min-width: 0;
        }
        .staff-delete-btn {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border: 1px solid #e8c8c8;
            border-radius: 6px;
            background: #fff;
            color: var(--color-red-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
        }
        .staff-delete-btn i {
            font-size: 15px;
        }
        .staff-log-btn {
            border: 0;
            background: transparent;
            color: var(--color-red-primary);
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0;
            cursor: pointer;
        }
        #staff-log-modal .modal-card {
            width: min(560px, 100%);
        }
        .staff-log-wrap {
            max-height: 320px;
            overflow: auto;
        }
        .staff-log-table {
            width: 100%;
            border-collapse: collapse;
        }
        .staff-log-table th,
        .staff-log-table td {
            padding: 6px 12px;
            font-size: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }
    </style>
@endpush

@section('content')
    <div class="module-page">
        <div class="module-page__content">
            <div class="staff-module">
                <div class="module-controls-bar">
                    <h2>Staff Accounts Management</h2>
                </div>

                <section class="tab-content-panel">
                    <p class="results-count staff-count-badge">
                        <i class="ti ti-users"></i> Staff Accounts ({{ $staff->count() }})
                    </p>
                    <div class="table-wrap">
                        <table class="staff-table" id="staff-table">
                            <thead>
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Role / Position</th>
                                    <th>Contact</th>
                                    <th>Login / Logout</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($staff as $member)
                                    <tr data-search="{{ mb_strtolower($member->name.' '.$member->username.' '.$member->role) }}">
                                        <td>
                                            <div class="staff-profile-cell">
                                                <span class="staff-avatar" style="background-color: {{ $member->avatar_color }}">{{ $member->avatar_initials }}</span>
                                                <div>
                                                    <strong class="staff-name">{{ $member->name }}</strong>
                                                    <span class="staff-subtext">{{ '@'.$member->username }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $member->role }}</td>
                                        <td>{{ $member->contact }}</td>
                                        <td>
                                            @php $latest = $member->shiftRecords->first(); @endphp
                                            <div class="staff-login-cell">
                                                <div class="staff-session">
                                                    @if ($latest)
                                                        <span class="last-login-text"><i class="ti ti-login"></i> In {{ $latest->loginLabel() }}</span>
                                                        @if ($latest->isOpen())
                                                            <span class="staff-subtext">Signed in</span>
                                                        @else
                                                            <span class="staff-subtext">Out {{ $latest->logoutLabel() }}</span>
                                                        @endif
                                                        <a class="staff-log-btn" href="{{ route('staff-log', ['staff' => $member->id]) }}">All logins</a>
                                                    @else
                                                        <span class="last-login-text"><i class="ti ti-clock"></i> Never signed in</span>
                                                    @endif
                                                </div>
                                                @unless ($member->is(auth()->user()))
                                                    <button type="button" class="staff-delete-btn" aria-label="Delete" title="Delete" data-delete-url="{{ route('staff.destroy', $member) }}" data-delete-name="{{ $member->name }} ({{ '@'.$member->username }})">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                @endunless
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr data-empty-row hidden>
                                    <td colspan="4" class="empty-state">No staff accounts match your filter.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </div>
    </div>
@endsection
