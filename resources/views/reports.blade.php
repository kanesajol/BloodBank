@extends('layouts.app')

@section('title', 'CRH Blood Bank — Reports')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/reports.css') }}">
    <style>
        .reports-header-bar {
            display: block;
            min-width: 0;
            overflow: visible;
            padding-top: 0;
        }
        .reports-header-controls {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: flex-start;
            gap: 0.75rem;
            width: 100%;
            min-width: 0;
            overflow: visible;
        }
        .header-year-slot {
            position: relative;
        }
        .header-year-slot .header-actions {
            position: absolute;
            right: 0;
            bottom: calc(100% + 8px);
            display: flex;
            align-items: center;
            margin: 0;
        }
        .reports-card-head h2 {
            margin: 0 0 0.45rem;
            font-size: 1.25rem;
            color: var(--color-red-primary);
        }
        .reports-header-controls .report-select-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            min-width: 0;
        }
        .report-location-search {
            position: relative;
        }
        .report-location-search i {
            position: absolute;
            left: 12px;
            color: #990000;
            font-size: 1rem;
            pointer-events: none;
        }
        .reports-header-controls .report-select-label {
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
        }
        .reports-header-controls .report-select-label i {
            font-size: 1.1rem;
        }
        .reports-header-controls .report-dropdown-select {
            min-width: 0 !important;
            height: 36px;
            padding: 7px 30px 7px 12px;
            font-size: 0.88rem;
            border-radius: 8px;
            box-sizing: border-box;
        }
        .header-location-input {
            flex: 1;
            width: auto;
            min-width: 0;
            height: 36px;
            box-sizing: border-box;
            padding: 0 12px 0 34px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e0;
            background: #fff;
            color: #1a202c;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: text;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .header-location-input::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }
        .header-location-input:hover {
            border-color: #a0aec0;
        }
        .header-location-input:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.15);
        }
        .reports-header-controls .header-period {
            width: 128px !important;
            min-width: 120px !important;
            max-width: 128px;
        }
        .reports-header-controls .header-year {
            width: 112px !important;
            min-width: 100px !important;
            max-width: 112px;
        }
        .reports-header-bar .btn-print {
            height: 36px;
            box-sizing: border-box;
            padding: 0 12px;
            font-size: 0.875rem;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .report-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin: 0;
            flex-shrink: 0;
        }
        .report-nav-pill {
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #4a5568;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 0.875rem;
            font-weight: 700;
            line-height: 1.2;
            cursor: pointer;
        }
        .report-nav-pill:hover {
            background: #f8fafc;
            color: #1a202c;
        }
        .report-nav-pill.is-active {
            background: #FDECEC;
            color: #990000;
            border-color: #E8C8C8;
        }
        .cursor-pointer { cursor: pointer; }
        .transition-colors { transition-property: background-color, color; }
        .duration-150 { transition-duration: 150ms; }
        .hover\:bg-slate-50:hover { background: #f8fafc; }
        .period-pills {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 4px;
            padding: 2px;
            background: #edf2f7;
            border-radius: 999px;
        }
        .period-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #4a5568;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            line-height: 1.2;
            cursor: pointer;
        }
        .period-pill.is-active {
            background: #fff;
            color: #990000;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }
        .period-pill:hover {
            color: #1a202c;
        }
        .period-pill.is-active:hover {
            color: #990000;
        }
        .reports-header-controls .header-actions {
            position: static;
            margin-left: auto;
        }
        .panel-filters {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            margin-left: auto;
        }
        .panel-filters .filter-select {
            height: 28px;
            min-width: 6.5rem;
            padding: 2px 8px;
            font-size: 0.75rem;
        }
        .dashboard,
        .dashboard__content {
            height: auto;
            max-height: none;
            overflow: visible;
        }
        .dashboard__main {
            flex: 1;
            overflow: visible;
            padding: 0.6rem 0.85rem;
            display: flex;
            flex-direction: column;
        }
        .module-page {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .module-page__header {
            margin-bottom: 0.35rem;
            flex-shrink: 0;
        }
        .module-page__header h2 {
            font-size: 1.25rem;
            margin-bottom: 0;
        }
        .module-page__content {
            flex: 1;
            overflow: visible;
            padding: 0.65rem 0.75rem;
            display: flex;
            flex-direction: column;
        }
        .reports-module {
            overflow: visible;
            gap: 0.45rem;
        }
        .reports-header-bar {
            padding-bottom: 0.35rem;
            flex-shrink: 0;
        }
        #report-results {
            overflow: visible;
            display: flex;
            flex-direction: column;
        }
        [data-report-panel]:not([hidden]) {
            overflow: visible;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .report-section {
            overflow: visible;
            gap: 0.4rem;
        }
        .section-title-wrap {
            gap: 0.4rem;
            flex-shrink: 0;
        }
        .filter-group {
            font-size: 0.75rem;
            gap: 4px;
        }
        .card-box {
            padding: 0.55rem 0.7rem;
        }
        .card-box:has(.table-wrap) {
            display: flex;
            flex-direction: column;
            overflow: visible;
        }
        .box-header {
            margin-bottom: 0.35rem;
            flex-shrink: 0;
            flex-wrap: wrap;
        }
        .box-title {
            font-size: 0.8rem;
        }
        .record-badge {
            font-size: 0.68rem;
            padding: 2px 6px;
        }
        .table-wrap {
            overflow-x: auto;
            max-height: none;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            -webkit-overflow-scrolling: touch;
        }
        [data-report-panel="demographics"]:not([hidden]) {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            align-items: stretch;
        }
        .reports-module:not(.print-all-reports) [data-report-panel="demographics"] > .report-section,
        .reports-module:not(.print-all-reports) [data-report-panel="demographics"] .demo-split > .card-box {
            height: 100%;
        }
        .reports-module:not(.print-all-reports) [data-report-panel="demographics"]:not([hidden]) {
            display: block;
        }
        .reports-module:not(.print-all-reports) [data-report-panel="demographics"] > .report-section {
            display: contents;
            height: auto;
        }
        .reports-module:not(.print-all-reports) [data-report-panel="demographics"] .card-box {
            display: none;
        }
        .demo-board {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            align-items: start;
        }
        .demo-pair {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 18px;
            align-items: start;
        }
        .demo-summary {
            padding: 13px;
            border: 1px solid #f1dede;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 3px 8px rgba(127, 29, 29, 0.04);
        }
        .demo-summary-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 11px;
            border-bottom: 1px solid #f1f5f9;
        }
        .demo-summary-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: #fff1f1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .demo-summary-head h3 {
            margin: 0;
            color: #1e293b;
            font-size: 15px;
            font-weight: 800;
        }
        .demo-summary-head p {
            margin: 2px 0 0;
            color: #64748b;
            font-size: 11px;
        }
        .demo-subhead {
            margin: 13px 0 8px;
            color: #1e293b;
            font-size: 12px;
            font-weight: 800;
        }
        .demo-age-list {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }
        .demo-age-row {
            display: flex;
            align-items: center;
            gap: 9px;
            width: 100%;
            padding: 9px;
            border: 0;
            border-radius: 12px;
            background: #fafafa;
            text-align: left;
            cursor: pointer;
            font: inherit;
        }
        .demo-age-row:hover {
            background: #fff1f1;
        }
        .demo-age-icon {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #990000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }
        .demo-age-body {
            flex: 1;
            min-width: 0;
        }
        .demo-age-top,
        .demo-age-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .demo-age-top strong {
            color: #1e293b;
            font-size: 12px;
        }
        .demo-age-top span,
        .demo-gender-count {
            color: #990000;
            font-size: 11px;
            font-weight: 800;
        }
        .demo-age-bottom {
            margin-top: 4px;
            justify-content: flex-start;
        }
        .demo-age-bottom > span:first-child {
            color: #64748b;
            font-size: 10px;
            min-width: 76px;
        }
        .demo-track {
            flex: 1;
            height: 5px;
            border-radius: 4px;
            background: #e2e8f0;
            overflow: hidden;
        }
        .demo-fill {
            display: block;
            height: 100%;
            border-radius: 4px;
            background: #990000;
        }
        .demo-gender-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .demo-gender-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            padding: 10px;
            border: 0;
            border-radius: 12px;
            background: #fafafa;
            cursor: pointer;
            font: inherit;
        }
        .demo-gender-card:hover {
            background: #fff1f1;
        }
        .demo-gender-icon {
            width: 34px;
            height: 34px;
            margin-bottom: 6px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            font-weight: 700;
        }
        .demo-gender-icon.female {
            background: #fff0f5;
            color: #db2777;
        }
        .demo-gender-icon.male {
            background: #eff6ff;
            color: #2563eb;
        }
        .demo-gender-label {
            color: #1e293b;
            font-size: 12px;
            font-weight: 800;
        }
        .demo-gender-meta {
            margin-top: 2px;
            color: #64748b;
            font-size: 10px;
            text-align: center;
        }
        @media (max-width: 860px) {
            .demo-board {
                grid-template-columns: 1fr;
            }
            .print-all-reports .print-sheet--people [data-report-panel="demographics"] {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                flex-direction: row !important;
            }
        }
        [data-report-panel="demographics"] > .print-report-section-title {
            grid-column: 1 / -1;
            margin: 0;
        }
        [data-report-panel="demographics"] > .demo-split {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            align-items: start;
        }
        @media (max-width: 860px) {
            .reports-header-controls {
                flex-wrap: wrap;
            }
            .reports-header-controls .report-select-group,
            .reports-header-controls .header-actions,
            .reports-header-bar .btn-print {
                flex: 1 1 100%;
                width: 100%;
            }
            .reports-header-controls .header-actions {
                margin-left: 0;
            }
            .reports-header-bar .btn-print {
                justify-content: center;
            }
            .report-nav-pill {
                flex: 1 1 calc(50% - 6px);
                text-align: center;
            }
            .box-header {
                align-items: flex-start;
            }
            [data-report-panel="demographics"]:not([hidden]),
            [data-report-panel="demographics"] > .demo-split {
                display: flex;
                flex-direction: column;
            }
            .print-all-reports [data-report-panel="demographics"]:not([hidden]) {
                display: grid;
                grid-template-columns: 1fr 1fr;
                flex-direction: row;
            }
        }
        .report-table {
            font-size: 0.8rem;
        }
        .report-table th,
        .report-table td,
        .report-table.compact th,
        .report-table.compact td {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        .report-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .consumption-report-total,
        .card-box:has(.consumption-report-total) .report-table {
            table-layout: fixed;
            width: 100%;
        }
        .consumption-report-total {
            flex-shrink: 0;
            border-collapse: collapse;
        }
        .consumption-report-total td {
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
        }
        .pill-group {
            gap: 6px;
        }
        .breakdown-pill {
            padding: 4px 8px;
        }
        [data-report-panel][aria-busy="true"],
        #report-results[aria-busy="true"] {
            opacity: 0.55;
            pointer-events: none;
        }
        #report-results[aria-busy="true"] {
            min-height: 10rem;
        }
        .print-only-header,
        .print-only-signatures,
        .print-page-mark {
            display: none;
        }
        body:has(.print-all-reports) {
            background: #d9dde3;
        }
        body:has(.print-all-reports) .sidebar,
        body:has(.print-all-reports) .header,
        body:has(.print-all-reports) .reports-card-head,
        body:has(.print-all-reports) .reports-header-bar,
        body:has(.print-all-reports) .report-nav,
        .print-all-reports .period-pills,
        .print-all-reports label.filter-group,
        .print-all-reports .province-chevron {
            display: none !important;
        }
        body:has(.print-all-reports) .dashboard,
        body:has(.print-all-reports) .dashboard__content,
        body:has(.print-all-reports) .dashboard__main,
        body:has(.print-all-reports) .module-page,
        body:has(.print-all-reports) .module-page__content,
        .print-all-reports,
        .print-all-reports #report-results,
        .print-all-reports [data-report-panel],
        .print-all-reports .report-section,
        .print-all-reports .card-box,
        .print-all-reports .card-box:has(.table-wrap),
        .print-all-reports .table-wrap,
        .print-all-reports .box-header,
        .print-all-reports .province-report-section {
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
            flex: none !important;
            overflow: visible !important;
        }
        body:has(.print-all-reports) .dashboard,
        body:has(.print-all-reports) .dashboard__content,
        body:has(.print-all-reports) .dashboard__main,
        body:has(.print-all-reports) .module-page,
        body:has(.print-all-reports) .module-page__content {
            display: block;
            width: auto;
            margin: 0;
            padding: 0;
            background: transparent;
            box-shadow: none;
        }
        .print-all-reports .print-only-header,
        .print-all-reports .print-only-signatures,
        .print-all-reports .print-page-mark,
        .print-all-reports .print-report-section-title {
            display: block;
        }
        .print-all-reports .modal-backdrop,
        .print-all-reports .history-table-wrap {
            display: none !important;
        }
        .print-book {
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            width: 210mm;
            max-width: calc(100vw - 24px);
            min-height: 297mm;
            margin: 16px auto 28px;
            padding: 8mm 8mm 10mm;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
        }
        .print-sheet {
            box-sizing: border-box;
            display: block;
            width: auto;
            max-width: 100%;
            min-height: 0;
            margin: 0;
            padding: 0;
            background: transparent;
            color: #111;
            box-shadow: none;
            overflow: visible;
            break-after: avoid;
            page-break-after: avoid;
            break-inside: auto;
            page-break-inside: auto;
        }
        .print-sheet + .print-sheet {
            margin-top: 12px;
        }
        .print-sheet--consumption {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
        }
        .print-sheet--consumption .print-only-signatures {
            margin-top: auto;
        }
        .print-page-mark {
            margin: 0 0 4px;
            text-align: right;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #64748b;
        }
        .print-lead {
            margin: 0 0 8px;
            color: #475569;
            font-size: 11px;
        }
        .print-mini-title {
            margin: 0;
            padding: 6px 10px;
            background: #800020;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .print-period-layout {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 10px 12px;
            align-items: stretch;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .print-period-side {
            display: grid;
            grid-template-rows: auto 1fr;
            height: 100%;
        }
        .print-period-side > .print-panel:last-child {
            align-self: end;
        }
        .print-total-row td {
            font-weight: 800;
            background: #f8f1f3;
        }
        .print-empty-line {
            margin: 0;
            padding: 8px 10px;
            color: #64748b;
            font-size: 12px;
        }
        .print-panel {
            border: 1px solid #eadfe2;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }
        .print-lines {
            display: flex;
            flex-direction: column;
        }
        .print-ledger-head,
        .print-line {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 4.4rem 4.4rem;
            gap: 8px;
            align-items: center;
            padding: 4px 10px;
        }
        .print-lines--quad .print-ledger-head,
        .print-lines--quad .print-line {
            grid-template-columns: minmax(0, 1.4fr) 6.2rem 4.6rem 4.4rem;
        }
        .print-ledger-head {
            color: #94a3b8;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-bottom: 1px solid #f3e8ea;
        }
        .print-ledger-head span:not(:first-child),
        .print-line strong {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }
        .print-line {
            border-bottom: 1px solid #f6f1f2;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.3;
        }
        .print-line:last-child {
            border-bottom: 0;
        }
        .print-line strong {
            color: #1e293b;
            font-weight: 700;
            white-space: nowrap;
        }
        .print-line--total {
            background: #fbf6f7;
            font-weight: 800;
        }
        .print-line--zero strong {
            color: #94a3b8;
            font-weight: 600;
        }
        .print-line--total span {
            color: #800020;
            font-weight: 800;
        }
        .print-all-reports .print-sheet--people .card-box {
            border: 1px solid #eadfe2;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }
        .print-all-reports .print-sheet--people .box-header {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin: 0;
            padding: 6px 10px;
            background: #800020;
        }
        .print-all-reports .print-sheet--people .box-title {
            flex: 1 1 auto;
            min-width: 0;
            color: #fff;
            font-size: 11px;
            line-height: 1.25;
        }
        .print-all-reports .print-sheet--people .record-badge {
            flex: 0 0 auto;
            margin-left: 8px;
            color: #fff;
            white-space: nowrap;
        }
        .print-consumption-summary {
            display: block;
            width: 100%;
            max-width: 100%;
            margin: 0 0 8px;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .print-consumption-summary .report-table {
            width: 100%;
            table-layout: fixed;
        }
        .print-consumption-summary .report-table th,
        .print-consumption-summary .report-table td {
            overflow-wrap: anywhere;
        }
        .print-sheet--collections [data-report-panel="periodic"],
        .print-sheet--consumption .pill-group,
        .print-sheet--consumption .province-report-section {
            display: none !important;
        }
        .print-sheet--people .print-lead,
        .print-sheet--people .print-report-section-title {
            grid-column: 1 / -1;
        }
        .print-all-reports .print-only-header {
            margin: 0 0 8px;
            padding: 0 0 8px;
            border-bottom: 2px solid #800020;
            background: #fff;
        }
        .print-all-reports .print-only-header h1 {
            margin: 0 0 2px;
            color: #800020;
            font-size: 18px;
            font-weight: 800;
        }
        .print-all-reports .print-only-header h2 {
            margin: 0 0 4px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
        }
        .print-all-reports .print-only-meta {
            margin: 0;
            color: #475569;
            font-size: 10px;
            line-height: 1.45;
        }
        .print-all-reports .print-report-section-title {
            margin: 0 0 8px;
            padding: 0 0 4px;
            border-bottom: 1px solid #e2e8f0;
            color: #800020;
            font-size: 14px;
            font-weight: 800;
        }
        .print-all-reports .card-box,
        .print-all-reports .table-wrap {
            display: block;
            width: auto;
            border: 0;
            border-radius: 0;
            padding: 0;
            margin: 0 0 8px;
            background: transparent;
            box-shadow: none;
        }
        .print-all-reports .box-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
            margin: 0 0 4px;
        }
        .print-sheet--collections .box-header {
            display: none !important;
        }
        .print-all-reports .box-title {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            color: #1e293b;
        }
        .print-all-reports .record-badge {
            padding: 0;
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
        }
        .print-all-reports .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .print-all-reports .report-table th,
        .print-all-reports .report-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #111;
            vertical-align: middle;
        }
        .print-all-reports .report-table thead th {
            position: static;
            background: #f8f1f3;
            color: #800020;
            font-size: 10px;
            font-weight: 800;
        }
        .print-all-reports .print-sheet--people [data-report-panel="demographics"] {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 12px;
            align-items: start;
        }
        .print-all-reports .print-sheet--people [data-report-panel="demographics"] > .print-report-section-title,
        .print-all-reports .print-sheet--people [data-report-panel="demographics"] > .print-lead {
            grid-column: 1 / -1;
        }
        .print-all-reports .print-sheet--people [data-report-panel="demographics"] > .report-section {
            display: block;
            min-width: 0;
        }
        .print-all-reports .print-sheet--people [data-report-panel="demographics"] > .report-section > .card-box {
            width: 100%;
            margin: 0;
        }
        .print-line small {
            display: block;
            margin-top: 1px;
            color: #64748b;
            font-size: 9px;
            font-weight: 600;
        }
        .print-sheet--people .print-report-section-title {
            grid-column: 1 / -1;
        }
        .print-sheet--consumption .print-sheet-main {
            flex: 0 0 auto;
        }
        .print-sheet--consumption .demo-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 14px;
            align-items: start;
            margin: 0 0 8px;
        }
        .print-all-reports .pill-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin: 0 0 8px;
        }
        .print-all-reports .breakdown-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            background: #fff;
            box-shadow: none;
            font-size: 11px;
        }
        .print-all-reports .consumption-province-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 10px;
            padding: 0;
        }
        .print-all-reports .province-card,
        .print-all-reports .province-card--trigger {
            min-height: 0;
            height: auto;
            padding: 4px 8px;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
            gap: 2px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            background: #fff;
            box-shadow: none;
            transform: none;
        }
        .print-all-reports .province-summary-name {
            flex: 1 1 100%;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .print-all-reports .province-summary-badges,
        .print-all-reports .province-summary-total {
            font-size: 10px;
        }
        .print-all-reports .consumption-report-total {
            width: auto !important;
            margin-top: 6px;
            border: 0 !important;
            table-layout: auto !important;
        }
        .print-all-reports .consumption-report-total col {
            width: auto !important;
        }
        .print-all-reports .consumption-report-total td {
            width: auto !important;
            border: 0 !important;
            background: transparent;
            padding: 4px 10px 0 0;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }
        .print-all-reports .consumption-report-total td:nth-child(n+3) {
            display: none !important;
        }
        .print-all-reports .report-table th {
            white-space: nowrap;
        }
        .print-all-reports .print-only-signatures {
            position: static;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            width: min(100%, 170mm);
            margin: 16mm auto 0;
            padding-top: 8mm;
            border: 0;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            color: #111;
            background: #fff;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .print-all-reports .print-only-signatures p {
            margin: 0 0 1.1rem;
            font-size: 12px;
            color: #111;
        }
        .print-all-reports .print-only-signatures span {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            color: #334155;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }
            .print-only-header {
                display: block !important;
                margin: 0 0 18px;
                padding: 0 0 12px;
                border-bottom: 2px solid #800020;
                background: #fff;
                color: #111;
            }
            .print-only-header h1 {
                margin: 0 0 4px;
                color: #800020 !important;
                font-size: 20px;
                font-weight: 800;
                letter-spacing: 0.02em;
            }
            .print-only-header h2 {
                margin: 0 0 8px;
                color: #334155 !important;
                font-size: 12px;
                font-weight: 700;
            }
            .print-only-meta {
                margin: 0;
                color: #475569 !important;
                font-size: 10px;
                line-height: 1.6;
            }
            .sidebar,
            .header,
            .module-page__header,
            .reports-card-head,
            .reports-header-bar,
            .report-nav,
            .period-pills,
            .panel-filters,
            .header-actions,
            .btn-print,
            .filters-inline,
            .pagination,
            .modal-backdrop,
            .modal-overlay,
            .history-table-wrap {
                display: none !important;
            }
            .reports-module,
            #report-results,
            .reports-module.print-all-reports [data-report-panel],
            .reports-module.print-all-reports .report-section,
            .reports-module.print-all-reports .card-box,
            .reports-module.print-all-reports .card-box:has(.table-wrap),
            .reports-module.print-all-reports .box-header,
            .reports-module.print-all-reports .province-report-section {
                display: block !important;
                width: auto !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                margin: 0 0 10px !important;
                float: none !important;
                position: static !important;
                overflow: visible !important;
                break-inside: auto !important;
                page-break-inside: auto !important;
            }
            .reports-module.print-all-reports .print-sheet--collections [data-report-panel="periodic"],
            .reports-module.print-all-reports .print-sheet--consumption .pill-group,
            .reports-module.print-all-reports .print-sheet--consumption .province-report-section {
                display: none !important;
            }
            body,
            .dashboard,
            .dashboard__content,
            .dashboard__main,
            .module-page,
            .module-page__content {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #111 !important;
                box-shadow: none !important;
                overflow: visible !important;
            }
            .reports-module.print-all-reports [data-report-panel] + [data-report-panel] {
                break-before: auto;
                page-break-before: auto;
                margin-top: 8px !important;
            }
            .reports-module.print-all-reports [data-report-panel="demographics"] {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 8px 12px !important;
                align-items: start !important;
                width: 100% !important;
            }
            .reports-module.print-all-reports [data-report-panel="demographics"] > .print-report-section-title,
            .reports-module.print-all-reports [data-report-panel="demographics"] > .demo-split {
                grid-column: 1 / -1 !important;
            }
            .reports-module.print-all-reports [data-report-panel="demographics"] > .demo-split {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 8px 12px !important;
                align-items: start !important;
                margin: 0 !important;
            }
            .reports-module.print-all-reports [data-report-panel="demographics"] .card-box {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                margin: 0 !important;
            }
            .print-report-section-title {
                display: block !important;
                margin: 0 0 8px !important;
                padding: 0 0 6px !important;
                border-bottom: 1px solid #cbd5e1 !important;
                color: #800020 !important;
                font-size: 16px !important;
                font-weight: 800 !important;
                break-after: avoid;
                page-break-after: avoid;
            }
            .card-box,
            .table-wrap,
            .report-table,
            .kpi-card {
                width: 100% !important;
                margin: 0 0 10px !important;
                padding: 8px !important;
                background: #fff !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                overflow: visible !important;
                color: #111 !important;
            }
            .box-header,
            .print-report-section-title {
                break-after: avoid;
                page-break-after: avoid;
            }
            .report-table {
                border-collapse: collapse !important;
                font-size: 9px !important;
            }
            .report-table th,
            .report-table td,
            .section-heading,
            .box-title {
                color: #111 !important;
                background: #fff !important;
                box-shadow: none !important;
            }
            .report-table th,
            .report-table td {
                padding: 7px 9px !important;
                border: 1px solid #cbd5e1 !important;
                font-size: 11px !important;
                vertical-align: middle !important;
            }
            .report-table thead {
                display: table-header-group;
            }
            .report-table tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .report-table thead th {
                position: static !important;
                color: #334155 !important;
                background: #f1f5f9 !important;
                font-weight: 800 !important;
            }
            .province-card,
            .breakdown-pill,
            .record-badge {
                color: #111 !important;
                background: #fff !important;
                border-color: #cbd5e1 !important;
                box-shadow: none !important;
            }
            .province-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .print-report-section-title {
                display: block !important;
            }
            .period-pills,
            .panel-filters,
            .filters-inline {
                display: none !important;
            }
            .consumption-report-total {
                border: 0 !important;
            }
            .print-only-signatures {
                position: static !important;
                right: auto !important;
                bottom: auto !important;
                left: auto !important;
                z-index: auto !important;
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                justify-content: center !important;
                gap: 2rem !important;
                width: min(100%, 170mm) !important;
                margin: 18mm auto 0 !important;
                padding: 8mm 0 0 !important;
                border: 0 !important;
                border-top: 1px solid #cbd5e1 !important;
                text-align: center !important;
                color: #111;
                background: #fff !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
            .print-only-signatures p {
                margin: 0 0 1.75rem;
                font-size: 12px;
                color: #111;
            }
            .print-only-signatures span {
                display: block;
                margin-top: 4px;
                font-size: 11px;
                color: #333;
            }
            .print-book {
                display: flex !important;
                flex-direction: column !important;
                box-sizing: border-box !important;
                width: auto !important;
                max-width: none !important;
                min-height: 281mm !important;
                margin: 0 !important;
                gap: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                box-shadow: none !important;
            }
            .print-sheet,
            .print-sheet--collections,
            .print-sheet--people,
            .print-sheet--consumption {
                width: auto !important;
                max-width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
                overflow: visible !important;
                break-after: avoid !important;
                page-break-after: avoid !important;
                break-inside: auto !important;
                page-break-inside: auto !important;
            }
            .print-sheet + .print-sheet {
                margin-top: 6px !important;
            }
            .reports-module.print-all-reports .print-sheet--people [data-report-panel="demographics"],
            .reports-module.print-all-reports .demo-split {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 8px 14px !important;
                align-items: start !important;
                width: auto !important;
                margin: 0 0 8px !important;
                break-inside: auto !important;
                page-break-inside: auto !important;
            }
            .reports-module.print-all-reports .print-sheet--people .print-report-section-title {
                grid-column: 1 / -1 !important;
            }
            .reports-module.print-all-reports .card-box,
            .reports-module.print-all-reports .table-wrap,
            .reports-module.print-all-reports .report-section,
            .reports-module.print-all-reports .province-card {
                break-inside: auto !important;
                page-break-inside: auto !important;
                height: auto !important;
                min-height: 0 !important;
            }
            .reports-module.print-all-reports .card-box,
            .reports-module.print-all-reports .table-wrap {
                border: 0 !important;
                padding: 0 !important;
                margin: 0 0 8px !important;
                background: transparent !important;
            }
            .print-period-layout,
            .print-consumption-summary,
            .reports-module.print-all-reports .print-sheet--people [data-report-panel="demographics"] {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
            .reports-module.print-all-reports .print-only-signatures {
                position: static !important;
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                width: min(100%, 170mm) !important;
                margin: 18mm auto 0 !important;
                break-before: avoid;
                page-break-before: avoid;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
            .print-sheet--collections [data-report-panel="periodic"],
            .print-sheet--consumption .pill-group,
            .print-sheet--consumption .province-report-section {
                display: none !important;
            }
            .reports-module.print-all-reports .print-period-layout {
                display: grid !important;
                align-items: stretch !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .reports-module.print-all-reports .print-consumption-summary {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .reports-module.print-all-reports .print-consumption-summary .report-table {
                width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed !important;
            }
            .reports-module.print-all-reports .print-sheet--people .print-lead,
            .reports-module.print-all-reports .print-sheet--people [data-report-panel="demographics"] > .demo-split {
                grid-column: 1 / -1 !important;
            }
            .reports-module.print-all-reports .consumption-report-total,
            .reports-module.print-all-reports .consumption-report-total td {
                width: auto !important;
                border: 0 !important;
                background: transparent !important;
                table-layout: auto !important;
            }
            .reports-module.print-all-reports .consumption-report-total td:nth-child(n+3) {
                display: none !important;
            }
            .reports-module.print-all-reports [data-report-panel] + [data-report-panel] {
                break-before: auto !important;
                page-break-before: auto !important;
                margin-top: 0 !important;
            }
            .reports-module.print-all-reports .print-sheet--people [data-report-panel="demographics"] {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 10px 12px !important;
                align-items: start !important;
            }
            .reports-module.print-all-reports .print-sheet--people [data-report-panel="demographics"] > .print-report-section-title,
            .reports-module.print-all-reports .print-sheet--people [data-report-panel="demographics"] > .print-lead,
            .reports-module.print-all-reports .print-sheet--people [data-report-panel="demographics"] > .report-section {
                min-width: 0 !important;
            }
            .reports-module.print-all-reports .print-sheet--people [data-report-panel="demographics"] > .print-report-section-title,
            .reports-module.print-all-reports .print-sheet--people [data-report-panel="demographics"] > .print-lead {
                grid-column: 1 / -1 !important;
            }
            .reports-module.print-all-reports .print-sheet--people [data-report-panel="demographics"] > .report-section {
                display: block !important;
                width: auto !important;
            }
            .reports-module.print-all-reports .print-sheet--people .card-box {
                border: 1px solid #eadfe2 !important;
                border-radius: 8px !important;
                overflow: hidden !important;
                padding: 0 !important;
                background: #fff !important;
            }
            .reports-module.print-all-reports .print-sheet--people .box-header {
                display: flex !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
                margin: 0 !important;
                padding: 6px 10px !important;
                background: #800020 !important;
            }
            .reports-module.print-all-reports .print-sheet--people .box-title,
            .reports-module.print-all-reports .print-sheet--people .record-badge {
                color: #fff !important;
                background: transparent !important;
            }
            .reports-module.print-all-reports .print-sheet--people .box-title {
                flex: 1 1 auto !important;
                font-size: 11px !important;
                line-height: 1.25 !important;
            }
            .reports-module.print-all-reports .print-sheet--people .record-badge {
                flex: 0 0 auto !important;
                white-space: nowrap !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="module-page">
        <div class="module-page__content">
            <div class="reports-module {{ $printAll ? 'print-all-reports' : '' }}" data-print-all="{{ $printAll ? '1' : '0' }}">
                @php
                    $printReportName = match ($view) {
                        'demographics' => 'Age & Gender Demographics',
                        'consumption' => 'Blood Consumption',
                        default => 'Periodic Collections — '.ucfirst($period),
                    };
                    $printLocation = $reportLocation !== '' ? $reportLocation : 'All Locations';
                    if ($view === 'consumption') {
                        $printCoverage = 'All Records';
                    } elseif ($view === 'demographics') {
                        $printCoverage = $monthLabel.' '.$year;
                    } elseif ($period === 'yearly') {
                        $printCoverage = 'Yearly 2025-2026';
                    } else {
                        $printCoverage = ucfirst($period).' '.$periodicYear;
                    }
                    if ($printAll) {
                        $printCoverage = ucfirst($period).' collections '.$periodicYear
                            .' · Demographics '.$monthLabel.' '.$year
                            .' · Consumption all records';
                    }
                @endphp
                @unless ($printAll)
                    <div class="print-only-header">
                        <h1>CRH BLOOD BANK - OFFICIAL ANALYTICS REPORT</h1>
                        <h2 id="printReportName">{{ $printReportName }}</h2>
                        <p class="print-only-meta">
                            Location: <span id="printLocation">{{ $printLocation }}</span>
                            · Coverage: <span id="printCoverage">{{ $printCoverage }}</span>
                            · Generated: {{ now()->format('F j, Y') }}
                        </p>
                    </div>
                @endunless
                <div class="reports-card-head">
                    <h2>System Reports &amp; Analytics</h2>
                        </div>
                <div class="reports-header-bar">
                    <form method="GET" action="{{ route('reports') }}" class="reports-header-controls" id="report-toolbar">
                        <div class="report-select-group report-location-search">
                            <i class="ti ti-map-pin" aria-hidden="true"></i>
                            <input id="reportLocationSearch" name="location" value="{{ $reportLocation }}" type="text" class="header-location-input" placeholder="Search location" autocomplete="off">
                        </div>
                        <input type="hidden" name="view" id="reportView" value="{{ $view }}">
                        <input type="hidden" name="period" id="toolbarPeriod" value="{{ $period }}">
                        <input type="hidden" name="blood_type" id="toolbarBloodType" value="{{ $consumptionBlood }}">
                        <input type="hidden" name="month" id="toolbarMonth" value="{{ $month }}">
                        <input type="hidden" name="year" id="toolbarYear" value="{{ $year }}">
                    <div class="header-actions">
                        <button type="button" class="btn-print" onclick="printAllReports()">
                            <i class="ti ti-printer"></i> Print / Export
                        </button>
                    </div>
                    </form>
                </div>
                <div class="report-nav" id="report-nav">
                    <button type="button" class="report-nav-pill {{ $view === 'periodic' ? 'is-active' : '' }}" data-report-tab="periodic">📊 Periodic Collections</button>
                    <button type="button" class="report-nav-pill {{ $view === 'demographics' ? 'is-active' : '' }}" data-report-tab="demographics">👥 Age &amp; Gender Demographics</button>
                    <button type="button" class="report-nav-pill {{ $view === 'consumption' ? 'is-active' : '' }}" data-report-tab="consumption">🩸 Blood Consumption</button>
                </div>
                <script>
                    function printAllReports() {
                        const url = new URL(@json(route('reports')), window.location.origin);
                        const periodicYear = document.getElementById('periodicYear');
                        const period = document.getElementById('toolbarPeriod');
                        const location = document.getElementById('reportLocationSearch');
                        url.searchParams.set('view', document.getElementById('reportView')?.value || 'periodic');
                        url.searchParams.set('print_all', '1');
                        url.searchParams.set('period', period?.value || 'monthly');
                        url.searchParams.set('year', periodicYear?.value || document.getElementById('toolbarYear')?.value || '');
                        url.searchParams.set('month', document.getElementById('toolbarMonth')?.value || 'all');
                        url.searchParams.set('location', location?.value.trim() || '');

                        let frame = document.getElementById('report-print-frame');
                        if (!frame) {
                            frame = document.createElement('iframe');
                            frame.id = 'report-print-frame';
                            frame.title = 'Report print';
                            frame.setAttribute('aria-hidden', 'true');
                            frame.style.cssText = 'position:fixed;width:0;height:0;border:0;visibility:hidden';
                            document.body.appendChild(frame);
                        }
                        frame.src = url.toString();
                    }
                    function openReportModal(id) {
                        const modal = document.getElementById(id);
                        if (modal) modal.removeAttribute('hidden');
                    }
                    let reportDetailRequest;
                    let consumptionDetailRequest;
                    let consumptionDemographicRequest;
                    function loadDemographicDetails(row) {
                        const modalId = row.dataset.demographicModal;
                        const modal = document.getElementById(modalId);
                        const body = document.getElementById(modalId + '-body');
                        if (!modal || !body) return;

                        const type = row.dataset.demographicType;
                        const group = row.dataset.demographicGroup;
                        const params = new URLSearchParams({
                            type: type,
                            group: group,
                            month: document.getElementById('toolbarMonth')?.value || 'all',
                            year: document.getElementById('toolbarYear')?.value || '',
                            location: document.getElementById('reportLocationSearch')?.value.trim() || '',
                        });
                        const endpoint = new URL(@json(route('reports.demographic-details')), window.location.origin);
                        endpoint.search = params.toString();
                        reportDetailRequest?.abort();
                        reportDetailRequest = new AbortController();
                        const currentRequest = reportDetailRequest;
                        modal.removeAttribute('hidden');
                        body.replaceChildren();
                        const loadingRow = document.createElement('tr');
                        const loadingCell = document.createElement('td');
                        loadingCell.colSpan = type === 'age' ? 5 : 6;
                        loadingCell.className = 'empty-state';
                        loadingCell.textContent = 'Loading donor records…';
                        loadingRow.appendChild(loadingCell);
                        body.appendChild(loadingRow);

                        fetch(endpoint.toString(), {
                            headers: { 'Accept': 'application/json' },
                            signal: currentRequest.signal,
                        })
                            .then(async function (response) {
                                const result = await response.json();
                                if (!response.ok) {
                                    throw new Error(result.message || 'Unable to load donor records.');
                                }

                                return result;
                            })
                            .then(function (result) {
                                if (reportDetailRequest !== currentRequest) return;
                                body.replaceChildren();
                                if (result.donors.length === 0) {
                                    const emptyRow = document.createElement('tr');
                                    const emptyCell = document.createElement('td');
                                    emptyCell.colSpan = type === 'age' ? 5 : 6;
                                    emptyCell.className = 'empty-state';
                                    emptyCell.textContent = 'No donor records found for this period.';
                                    emptyRow.appendChild(emptyCell);
                                    body.appendChild(emptyRow);
                                    return;
                                }

                                result.donors.forEach(function (donor) {
                                    const donorRow = document.createElement('tr');
                                    donorRow.dataset.location = (donor.address || '').toLowerCase();
                                    const values = type === 'age'
                                        ? [donor.name, donor.age, donor.blood_type, donor.contact, donor.address]
                                        : [donor.name, donor.blood_type, donor.age, donor.contact, donor.last_donation ? donor.last_donation_display : '—', donor.address];
                                    values.forEach(function (value, index) {
                                        const cell = document.createElement('td');
                                        cell.textContent = value === null || value === '' ? '—' : String(value);
                                        if (index === 0) cell.className = 'font-medium text-dark';
                                        if ((type === 'age' && index === 2) || (type === 'gender' && index === 1)) {
                                            const badge = document.createElement('span');
                                            badge.className = 'blood-type-badge';
                                            badge.textContent = cell.textContent;
                                            cell.replaceChildren(badge);
                                        }
                                        donorRow.appendChild(cell);
                                    });
                                    body.appendChild(donorRow);
                                });
                            })
                            .catch(function (error) {
                                if (error.name === 'AbortError' || reportDetailRequest !== currentRequest) return;
                                body.replaceChildren();
                                const errorRow = document.createElement('tr');
                                const errorCell = document.createElement('td');
                                errorCell.colSpan = type === 'age' ? 5 : 6;
                                errorCell.className = 'empty-state';
                                errorCell.textContent = error.message || 'Unable to load donor records. Please try again.';
                                errorRow.appendChild(errorCell);
                                body.appendChild(errorRow);
                            });
                    }
                    function loadConsumptionDemographicDetails(row) {
                        const type = row.dataset.consumptionDemographicType;
                        const group = row.dataset.consumptionDemographicGroup;
                        const modalId = type === 'age' ? 'consumption-age-patients' : 'consumption-gender-patients';
                        const modal = document.getElementById(modalId);
                        const body = document.getElementById(modalId + '-body');
                        const title = document.getElementById(modalId + '-title');
                        const summary = document.getElementById(modalId + '-summary');
                        if (!modal || !body) return;

                        if (title) title.textContent = type === 'age' ? 'Age ' + group + ' Consumption Patients' : group + ' Consumption Patients';
                        if (summary) summary.textContent = 'Loading patient records…';
                        modal.removeAttribute('hidden');
                        body.replaceChildren();
                        const loadingRow = document.createElement('tr');
                        const loadingCell = document.createElement('td');
                        loadingCell.colSpan = 4;
                        loadingCell.className = 'empty-state';
                        loadingCell.textContent = 'Loading patient records…';
                        loadingRow.appendChild(loadingCell);
                        body.appendChild(loadingRow);

                        const endpoint = new URL(@json(route('reports.consumption-demographic-details')), window.location.origin);
                        endpoint.search = new URLSearchParams({
                            type: type,
                            group: group,
                            month: document.getElementById('toolbarMonth')?.value || 'all',
                            year: document.getElementById('toolbarYear')?.value || '',
                            location: document.getElementById('reportLocationSearch')?.value.trim() || '',
                        }).toString();
                        consumptionDemographicRequest?.abort();
                        consumptionDemographicRequest = new AbortController();
                        const currentRequest = consumptionDemographicRequest;

                        fetch(endpoint.toString(), {
                            headers: { 'Accept': 'application/json' },
                            signal: currentRequest.signal,
                        })
                            .then(async function (response) {
                                const result = await response.json();
                                if (!response.ok) {
                                    throw new Error(result.message || 'Unable to load consumption patients.');
                                }

                                return result;
                            })
                            .then(function (result) {
                                if (consumptionDemographicRequest !== currentRequest) return;
                                const patients = result.patients || [];
                                body.replaceChildren();
                                if (summary) summary.textContent = patients.length.toLocaleString() + ' patient' + (patients.length === 1 ? '' : 's');
                                if (patients.length === 0) {
                                    const emptyRow = document.createElement('tr');
                                    const emptyCell = document.createElement('td');
                                    emptyCell.colSpan = 4;
                                    emptyCell.className = 'empty-state';
                                    emptyCell.textContent = 'No consumption patients found for this group.';
                                    emptyRow.appendChild(emptyCell);
                                    body.appendChild(emptyRow);
                                    return;
                                }

                                patients.forEach(function (patient) {
                                    const patientRow = document.createElement('tr');
                                    [
                                        patient.patient_name,
                                        patient.age ?? '—',
                                        patient.sex || '—',
                                        Number(patient.bags || 0).toLocaleString(),
                                    ].forEach(function (value, index) {
                                        const cell = document.createElement('td');
                                        cell.textContent = String(value);
                                        if (index === 0) cell.className = 'font-medium text-dark';
                                        patientRow.appendChild(cell);
                                    });
                                    body.appendChild(patientRow);
                                });
                            })
                            .catch(function (error) {
                                if (error.name === 'AbortError' || consumptionDemographicRequest !== currentRequest) return;
                                body.replaceChildren();
                                const errorRow = document.createElement('tr');
                                const errorCell = document.createElement('td');
                                errorCell.colSpan = 4;
                                errorCell.className = 'empty-state';
                                errorCell.textContent = error.message || 'Unable to load consumption patients. Please try again.';
                                errorRow.appendChild(errorCell);
                                body.appendChild(errorRow);
                                if (summary) summary.textContent = group;
                            });
                    }
                    function loadConsumptionMunicipalityDetails(row) {
                        const modal = document.getElementById('consumption-municipality-details');
                        const body = document.getElementById('consumption-municipality-records');
                        const title = document.getElementById('consumption-municipality-title');
                        const summary = document.getElementById('consumption-municipality-summary');
                        if (!modal || !body) return;

                        const province = row.dataset.consumptionProvince;
                        const municipality = row.dataset.consumptionMunicipality;
                        if (title) title.textContent = municipality;
                        if (summary) summary.textContent = province + ' · Loading records…';
                        modal.removeAttribute('hidden');
                        body.replaceChildren();
                        const loadingRow = document.createElement('tr');
                        const loadingCell = document.createElement('td');
                        loadingCell.colSpan = 6;
                        loadingCell.className = 'empty-state';
                        loadingCell.textContent = 'Loading consumption records…';
                        loadingRow.appendChild(loadingCell);
                        body.appendChild(loadingRow);

                        const endpoint = new URL(@json(route('reports.consumption-details')), window.location.origin);
                        endpoint.search = new URLSearchParams({
                            province: province,
                            municipality: municipality,
                            location: document.getElementById('reportLocationSearch')?.value.trim() || '',
                            blood_type: document.getElementById('toolbarBloodType')?.value || '',
                        }).toString();
                        consumptionDetailRequest?.abort();
                        consumptionDetailRequest = new AbortController();
                        const currentRequest = consumptionDetailRequest;

                        fetch(endpoint.toString(), {
                            headers: { 'Accept': 'application/json' },
                            signal: currentRequest.signal,
                        })
                            .then(async function (response) {
                                const result = await response.json();
                                if (!response.ok) {
                                    throw new Error(result.message || 'Unable to load consumption records.');
                                }

                                return result;
                            })
                            .then(function (result) {
                                if (consumptionDetailRequest !== currentRequest) return;
                                body.replaceChildren();
                                const records = result.records || [];
                                const totalUnits = records.reduce(function (total, record) {
                                    return total + Number(record.units || 0);
                                }, 0);
                                if (summary) {
                                    summary.textContent = province + ' · ' + records.length.toLocaleString() +
                                        ' records · ' + totalUnits.toLocaleString() + ' units';
                                }
                                if (records.length === 0) {
                                    const emptyRow = document.createElement('tr');
                                    const emptyCell = document.createElement('td');
                                    emptyCell.colSpan = 6;
                                    emptyCell.className = 'empty-state';
                                    emptyCell.textContent = 'No consumption records found for this municipality.';
                                    emptyRow.appendChild(emptyCell);
                                    body.appendChild(emptyRow);
                                    return;
                                }

                                records.forEach(function (record) {
                                    const recordRow = document.createElement('tr');
                                    [
                                        record.date,
                                        record.patient_name || '—',
                                        record.age ?? '—',
                                        record.sex || '—',
                                        record.component || '—',
                                        Number(record.units || 0).toLocaleString(),
                                    ].forEach(function (value) {
                                        const cell = document.createElement('td');
                                        cell.textContent = String(value);
                                        recordRow.appendChild(cell);
                                    });
                                    body.appendChild(recordRow);
                                });
                            })
                            .catch(function (error) {
                                if (error.name === 'AbortError' || consumptionDetailRequest !== currentRequest) return;
                                body.replaceChildren();
                                const errorRow = document.createElement('tr');
                                const errorCell = document.createElement('td');
                                errorCell.colSpan = 6;
                                errorCell.className = 'empty-state';
                                errorCell.textContent = error.message || 'Unable to load consumption records. Please try again.';
                                errorRow.appendChild(errorCell);
                                body.appendChild(errorRow);
                                if (summary) summary.textContent = province;
                            });
                    }
                    function syncPrintHeader() {
                        if (document.querySelector('.reports-module')?.dataset.printAll === '1') {
                            return;
                        }
                        const view = document.getElementById('reportView')?.value || 'periodic';
                        const locationEl = document.getElementById('printLocation');
                        const coverageEl = document.getElementById('printCoverage');
                        if (locationEl) {
                            const typed = document.getElementById('reportLocationSearch')?.value.trim() || '';
                            locationEl.textContent = typed !== '' ? typed : 'All Locations';
                        }
                        if (!coverageEl) return;
                        if (view === 'demographics') {
                            const month = document.getElementById('toolbarMonth')?.value || 'all';
                            const year = document.getElementById('toolbarYear')?.value || '';
                            const monthName = month === 'all'
                                ? 'All Months'
                                : (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'][Number(month) - 1] || 'All Months');
                            coverageEl.textContent = monthName + ' ' + year;
                        } else if (view === 'consumption') {
                            coverageEl.textContent = 'All Records';
                        } else {
                            const period = document.querySelector('#report-toolbar [name="period"]')?.value || 'monthly';
                            const year = document.getElementById('periodicYear')?.value || '';
                            coverageEl.textContent = period === 'yearly'
                                ? 'Yearly 2025-2026'
                                : period.charAt(0).toUpperCase() + period.slice(1) + ' ' + year;
                        }
                    }
                    window.addEventListener('beforeprint', syncPrintHeader);
                    function syncToolbarFields() {
                        const bloodType = document.getElementById('consumptionBloodType');
                        const toolbarMonth = document.getElementById('toolbarMonth');
                        const toolbarYear = document.getElementById('toolbarYear');
                        const toolbarBloodType = document.getElementById('toolbarBloodType');
                        const view = document.getElementById('reportView')?.value || 'periodic';
                        const periodicYear = document.getElementById('periodicYear');
                        if (view === 'periodic' && periodicYear && toolbarYear) toolbarYear.value = periodicYear.value;
                        if (bloodType && toolbarBloodType) toolbarBloodType.value = bloodType.value;
                    }
                    function bindReportInteractions(root) {
                        root.querySelectorAll('[data-modal-close]').forEach(function (button) {
                            button.addEventListener('click', function () {
                                const modal = document.getElementById(button.dataset.modalClose);
                                if (modal) modal.setAttribute('hidden', '');
                            });
                        });
                        root.querySelectorAll('[data-demographic-type]').forEach(function (row) {
                            row.addEventListener('click', function () {
                                loadDemographicDetails(row);
                            });
                        });
                        root.querySelectorAll('[data-consumption-demographic-type]').forEach(function (row) {
                            row.addEventListener('click', function () {
                                loadConsumptionDemographicDetails(row);
                            });
                        });
                        root.querySelectorAll('[data-consumption-province]').forEach(function (row) {
                            row.addEventListener('click', function () {
                                loadConsumptionMunicipalityDetails(row);
                            });
                        });
                        root.querySelectorAll('.modal-backdrop').forEach(function (overlay) {
                            overlay.addEventListener('click', function (event) {
                                if (event.target === overlay) overlay.setAttribute('hidden', '');
                            });
                        });
                        const deleteModal = document.getElementById('delete-confirm-modal');
                        const deleteForm = document.getElementById('delete-confirm-form');
                        const deleteName = document.getElementById('delete-confirm-name');
                        root.querySelectorAll('[data-delete-url]').forEach(function (button) {
                            button.addEventListener('click', function (event) {
                                event.preventDefault();
                                event.stopPropagation();
                                if (!deleteModal || !deleteForm) return;
                                deleteForm.action = button.dataset.deleteUrl;
                                if (deleteName) deleteName.textContent = button.dataset.deleteName || '';
                                deleteModal.removeAttribute('hidden');
                            });
                        });
                    }
                    let reportPanelRequest = 0;
                    function toolbarFieldValue(id) {
                        const field = document.getElementById(id);
                        return field ? field.value : '';
                    }
                    function activeReportView() {
                        return document.getElementById('reportView')?.value || 'periodic';
                    }
                    function syncReportPanelsVisibility(view) {
                        document.querySelectorAll('[data-report-panel]').forEach(function (panel) {
                            panel.toggleAttribute('hidden', panel.dataset.reportPanel !== view);
                        });
                    }
                    function refreshReportPanel(panelName) {
                        syncToolbarFields();
                        const requestId = ++reportPanelRequest;
                        const results = document.getElementById('report-results');
                        const current = document.querySelector('[data-report-panel="' + panelName + '"]');
                        const url = new URL(@json(route('reports')), window.location.origin);
                        const locationInput = document.getElementById('reportLocationSearch');
                        const periodInput = document.getElementById('toolbarPeriod');
                        url.searchParams.set('view', panelName);
                        url.searchParams.set('period', periodInput ? periodInput.value : 'monthly');
                        url.searchParams.set('location', locationInput ? locationInput.value : '');
                        url.searchParams.set('month', toolbarFieldValue('toolbarMonth'));
                        url.searchParams.set('year', toolbarFieldValue('toolbarYear'));
                        url.searchParams.set('blood_type', toolbarFieldValue('toolbarBloodType'));
                        if (current) {
                            current.setAttribute('aria-busy', 'true');
                            current.replaceChildren();
                            const loading = document.createElement('div');
                            loading.className = 'empty-state';
                            loading.textContent = 'Loading report…';
                            current.appendChild(loading);
                        }
                        if (results) {
                            results.setAttribute('aria-busy', 'true');
                        }
                        return fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                            .then(function (response) {
                                if (!response.ok) {
                                    throw new Error('Report request failed');
                                }
                                return response.text();
                            })
                            .then(function (html) {
                                if (requestId !== reportPanelRequest) {
                                    return;
                                }
                                const doc = new DOMParser().parseFromString(html, 'text/html');
                                const next = doc.querySelector('[data-report-panel="' + panelName + '"]');
                                const live = document.querySelector('[data-report-panel="' + panelName + '"]');
                                if (!next || !live) {
                                    return;
                                }
                                const activeView = activeReportView();
                                next.toggleAttribute('hidden', panelName !== activeView);
                                next.dataset.loaded = '1';
                                live.replaceWith(next);
                                bindReportInteractions(next);
                                syncToolbarFields();
                                syncPrintHeader();
                                syncReportPanelsVisibility(activeView);
                                const shown = new URL(url.toString());
                                const viewInput = document.getElementById('reportView');
                                if (viewInput) {
                                    shown.searchParams.set('view', viewInput.value);
                                }
                                history.replaceState(null, '', shown);
                            })
                            .catch(function (error) {
                                if (requestId !== reportPanelRequest) {
                                    return;
                                }
                                const live = document.querySelector('[data-report-panel="' + panelName + '"]');
                                if (live) {
                                    live.replaceChildren();
                                    const message = document.createElement('div');
                                    message.className = 'empty-state';
                                    message.textContent = error.message || 'Unable to load this report. Please try again.';
                                    live.appendChild(message);
                                }
                            })
                            .finally(function () {
                                if (requestId !== reportPanelRequest) {
                                    return;
                                }
                                const live = document.querySelector('[data-report-panel="' + panelName + '"]');
                                if (live) {
                                    live.removeAttribute('aria-busy');
                                }
                                if (results) {
                                    results.removeAttribute('aria-busy');
                                }
                            });
                    }
                    function switchReportView(view) {
                        const viewInput = document.getElementById('reportView');
                        document.querySelectorAll('[data-report-tab]').forEach(function (item) {
                            item.classList.toggle('is-active', item.dataset.reportTab === view);
                        });
                        if (viewInput) {
                            viewInput.value = view;
                        }
                        const url = new URL(window.location.href);
                        url.searchParams.set('view', view);
                        history.replaceState(null, '', url);
                        syncPrintHeader();
                        const panel = document.querySelector('[data-report-panel="' + view + '"]');
                        const needsLoad = panel && panel.dataset.loaded !== '1';
                        if (needsLoad) {
                            syncReportPanelsVisibility(view);
                            refreshReportPanel(view);
                            return;
                        }
                        syncReportPanelsVisibility(view);
                    }
                    document.addEventListener('change', function (event) {
                        const select = event.target.closest('[data-panel-filter]');
                        const results = document.getElementById('report-results');
                        if (!select || !results || !results.contains(select)) return;
                        refreshReportPanel(select.dataset.panelFilter);
                    });
                    document.addEventListener('DOMContentLoaded', function () {
                        if (document.querySelector('.reports-module')?.dataset.printAll === '1') {
                            window.addEventListener('load', function () {
                                window.print();
                            }, { once: true });
                        }
                        bindReportInteractions(document);
                        const input = document.getElementById('reportLocationSearch');
                        const form = document.getElementById('report-toolbar');
                        const viewInput = document.getElementById('reportView');
                        document.querySelectorAll('[data-report-tab]').forEach(function (pill) {
                            pill.addEventListener('click', function () {
                                switchReportView(pill.dataset.reportTab);
                            });
                        });
                        const results = document.getElementById('report-results');
                        if (results) {
                            results.addEventListener('click', function (event) {
                                const pill = event.target.closest('.period-pill[data-period]');
                                if (!pill || !results.contains(pill)) {
                                    return;
                                }
                                event.preventDefault();
                                const period = pill.dataset.period;
                                const periodInput = document.getElementById('toolbarPeriod');
                                if (periodInput) {
                                    periodInput.value = period;
                                }
                                pill.closest('.period-pills')?.querySelectorAll('.period-pill').forEach(function (item) {
                                    item.classList.toggle('is-active', item === pill);
                                });
                                if (viewInput) {
                                    viewInput.value = 'periodic';
                                }
                                document.querySelectorAll('[data-report-tab]').forEach(function (item) {
                                    item.classList.toggle('is-active', item.dataset.reportTab === 'periodic');
                                });
                                const url = new URL(window.location.href);
                                url.searchParams.set('view', 'periodic');
                                url.searchParams.set('period', period);
                                history.replaceState(null, '', url);
                                syncPrintHeader();
                                syncReportPanelsVisibility('periodic');
                                refreshReportPanel('periodic');
                            });
                        }
                        if (!input || !form) return;

                        const params = new URLSearchParams(window.location.search);
                        if ((params.get('location') || '') !== '') {
                            input.focus();
                            const end = input.value.length;
                            input.setSelectionRange(end, end);
                        }

                        let timer = null;
                        input.addEventListener('input', function () {
                            const view = (viewInput && viewInput.value) || 'periodic';
                            window.clearTimeout(timer);
                            timer = window.setTimeout(function () {
                                syncToolbarFields();
                                document.querySelectorAll('[data-report-panel]').forEach(function (panel) {
                                    if (panel.dataset.reportPanel !== view) {
                                        panel.dataset.loaded = '0';
                                    }
                                });
                                const url = new URL(window.location.href);
                                url.searchParams.set('location', input.value.trim());
                                url.searchParams.set('view', view);
                                history.replaceState(null, '', url);
                                refreshReportPanel(view);
                            }, 250);
                        });
                    });
                </script>

                <div id="report-results" @class(['print-book' => $printAll])>
                @php
                    $printHasActivity = static function (array $row, string $countKey, string $amountKey): bool {
                        return (int) ($row[$countKey] ?? 0) > 0 || (int) ($row[$amountKey] ?? 0) > 0;
                    };
                    $collectionAgeRows = $printAll
                        ? array_values(array_filter($ageReport['groups'], fn (array $item): bool => $printHasActivity($item, 'count', 'bags')))
                        : $ageReport['groups'];
                    $collectionGenderRows = $printAll
                        ? array_values(array_filter($gender['rows'], fn (array $item): bool => $printHasActivity($item, 'count', 'bags')))
                        : $gender['rows'];
                    $consumptionAgeRows = $printAll
                        ? array_values(array_filter($consumptionDemographics['age']['groups'], fn (array $item): bool => $printHasActivity($item, 'count', 'bags')))
                        : $consumptionDemographics['age']['groups'];
                    $consumptionGenderRows = $printAll
                        ? array_values(array_filter($consumptionDemographics['gender']['rows'], fn (array $item): bool => $printHasActivity($item, 'count', 'bags')))
                        : $consumptionDemographics['gender']['rows'];
                @endphp
                @if ($printAll)
                    <section class="print-sheet print-sheet--collections">
                        <p class="print-page-mark">1 / 1</p>
                        <div class="print-only-header">
                            <h1>CRH BLOOD BANK - OFFICIAL ANALYTICS REPORT</h1>
                            <h2 id="printReportName">Periodic Collections, Age &amp; Gender Demographics, and Blood Consumption</h2>
                            <p class="print-only-meta">
                                Location: <span id="printLocation">{{ $printLocation }}</span>
                                · Coverage: <span id="printCoverage">{{ $printCoverage }}</span>
                                · Generated: {{ now()->format('F j, Y') }}
                            </p>
                        </div>
                        <h2 class="print-report-section-title">Periodic Collections — {{ $periodicYear }}</h2>
                        <p class="print-lead">Monthly, quarterly, and yearly totals for {{ $periodicYear }}.</p>
                        @php
                            $monthlyRows = $printPeriods['monthly']['rows'];
                            $quarterlyRows = $printPeriods['quarterly']['rows'];
                            $yearlyRows = $printPeriods['yearly']['rows'];
                            $yearlyVisible = array_values(array_filter($yearlyRows, fn (array $row): bool => $printHasActivity($row, 'donors', 'bags')));
                            $monthlyDonorTotal = (int) array_sum(array_column($monthlyRows, 'donors'));
                            $monthlyBagTotal = (int) array_sum(array_column($monthlyRows, 'bags'));
                            $quarterlyDonorTotal = (int) array_sum(array_column($quarterlyRows, 'donors'));
                            $quarterlyBagTotal = (int) array_sum(array_column($quarterlyRows, 'bags'));
                        @endphp
                        <div class="print-period-layout">
                            <section class="print-panel">
                                <h3 class="print-mini-title">Monthly</h3>
                                @if ($monthlyRows === [])
                                    <p class="print-empty-line">No collections recorded for this period</p>
                                @else
                                    <div class="print-lines">
                                        <div class="print-ledger-head"><span>Month</span><span>Donors</span><span>Bags</span></div>
                                        @foreach ($monthlyRows as $row)
                                            <div @class(['print-line', 'print-line--zero' => (int) $row['donors'] === 0 && (int) $row['bags'] === 0])>
                                                <span>{{ $row['label'] }}</span>
                                                <strong>{{ number_format($row['donors']) }}</strong>
                                                <strong>{{ number_format($row['bags']) }}</strong>
                                            </div>
                                        @endforeach
                                        <div class="print-line print-line--total">
                                            <span>Total</span>
                                            <strong>{{ number_format($monthlyDonorTotal) }}</strong>
                                            <strong>{{ number_format($monthlyBagTotal) }}</strong>
                                        </div>
                                    </div>
                                @endif
                            </section>
                            <div class="print-period-side">
                                <section class="print-panel">
                                    <h3 class="print-mini-title">Quarterly</h3>
                                    @if ($quarterlyRows === [])
                                        <p class="print-empty-line">No collections recorded for this period</p>
                                    @else
                                        <div class="print-lines">
                                            <div class="print-ledger-head"><span>Quarter</span><span>Donors</span><span>Bags</span></div>
                                            @foreach ($quarterlyRows as $row)
                                                <div @class(['print-line', 'print-line--zero' => (int) $row['donors'] === 0 && (int) $row['bags'] === 0])>
                                                    <span>{{ $row['label'] }}<small>{{ $row['detail'] }}</small></span>
                                                    <strong>{{ number_format($row['donors']) }}</strong>
                                                    <strong>{{ number_format($row['bags']) }}</strong>
                                                </div>
                                            @endforeach
                                            <div class="print-line print-line--total">
                                                <span>Total</span>
                                                <strong>{{ number_format($quarterlyDonorTotal) }}</strong>
                                                <strong>{{ number_format($quarterlyBagTotal) }}</strong>
                                            </div>
                                        </div>
                                    @endif
                                </section>
                                <section class="print-panel">
                                    <h3 class="print-mini-title">Yearly</h3>
                                    @if ($yearlyVisible === [])
                                        <p class="print-empty-line">No collections recorded for this period</p>
                                    @else
                                        <div class="print-lines">
                                            <div class="print-ledger-head"><span>Year</span><span>Donors</span><span>Bags</span></div>
                                            @foreach ($yearlyVisible as $row)
                                                <div class="print-line print-line--total">
                                                    <span>{{ $row['label'] }}</span>
                                                    <strong>{{ number_format($row['donors']) }}</strong>
                                                    <strong>{{ number_format($row['bags']) }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </section>
                            </div>
                        </div>
                @endif
                <div data-report-panel="periodic" @if($view === 'periodic' || $printAll) data-loaded="1" @endif @unless($view === 'periodic' || $printAll) hidden @endunless>
                    <section class="report-section">
                        <h2 class="print-report-section-title" @unless($printAll) style="display:none" @endunless>Periodic Collections</h2>
                        <div class="card-box">
                            <div class="box-header">
                            <div>
                                    <div class="period-pills">
                                        @foreach (['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly'] as $periodId => $periodLabel)
                                            <button type="button" class="period-pill {{ $period === $periodId ? 'is-active' : '' }}" data-period="{{ $periodId }}">{{ $periodLabel }}</button>
                                        @endforeach
                            </div>
                                </div>
                                <label class="filter-group" for="periodicYear">Year:
                                    <select id="periodicYear" class="filter-select" data-panel-filter="periodic">
                                        @foreach ($periodicYears as $option)
                                            <option value="{{ $option }}" @selected((int) $periodicYear === (int) $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                </div>
                            <div class="table-wrap">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>{{ $period === 'quarterly' ? 'Quarter' : ($period === 'yearly' ? 'Year' : 'Month') }}</th>
                                            @if ($period === 'quarterly')
                                                <th>Period</th>
                                            @endif
                                            <th>Total Donors</th>
                                            <th>Total Collections / Bags</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($periodic['rows'] as $row)
                                            <tr class="clickable-row cursor-pointer hover:bg-slate-50 transition-colors duration-150 {{ $period === 'yearly' && (string) $row['label'] === (string) $periodicYear ? 'row-highlight' : '' }}" title="Click to view detailed donor list" onclick="openReportModal('periodic-{{ $row['id'] }}')" data-modal-open="periodic-{{ $row['id'] }}">
                                                <td><strong>{{ $row['label'] }}</strong></td>
                                                @if ($period === 'quarterly')
                                                    <td>{{ $row['detail'] }}</td>
                                                @endif
                                                <td><strong class="text-red">{{ number_format($row['donors']) }}</strong></td>
                                                <td>{{ number_format($row['bags']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $period === 'quarterly' ? 4 : 3 }}" class="empty-state">No collection records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @foreach ($periodic['rows'] as $row)
                            <div id="periodic-{{ $row['id'] }}" class="modal-backdrop" hidden>
                                <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="periodic-title-{{ $row['id'] }}" style="max-width: 920px;">
                                    <button type="button" class="modal-close" data-modal-close="periodic-{{ $row['id'] }}" aria-label="Close">
                                        <i class="ti ti-x"></i>
                                    </button>
                                    <div class="modal-header">
                                        <div>
                                            <h3 class="modal-name" id="periodic-title-{{ $row['id'] }}">{{ $row['detail'] }}</h3>
                                            <span class="modal-sub">{{ number_format($row['donors']) }} donor{{ $row['donors'] === 1 ? '' : 's' }} · {{ number_format($row['bags']) }} bag{{ $row['bags'] === 1 ? '' : 's' }}</span>
                                        </div>
                                    </div>
                                    <div class="history-table-wrap">
                                        <table class="history-table">
                                            <thead>
                                                <tr>
                                                    <th>Full Name</th>
                                                    <th>Blood Type</th>
                                                    <th>Age</th>
                                                    <th>Contact Number</th>
                                                    <th>Last Collection Date</th>
                                                    <th>Total Bags</th>
                                                    <th>Complete Address</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($row['records'] as $record)
                                                    <tr>
                                                        <td class="font-medium text-dark">{{ $record['name'] }}</td>
                                                        <td><span class="blood-type-badge">{{ $record['blood_type'] }}</span></td>
                                                        <td>{{ $record['age'] }}</td>
                                                        <td>{{ $record['contact'] }}</td>
                                                        <td>{{ \App\Support\BloodBank::formatDate($record['date']) }}</td>
                                                        <td>{{ $record['bags'] }} bag{{ (int) $record['bags'] === 1 ? '' : 's' }}</td>
                                                        <td>{{ $record['address'] }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="empty-state">No collection records for this period.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                                        @endforeach
                    </section>
                                </div>
                @if ($printAll)
                    </section>
                    <section class="print-sheet print-sheet--people">
                @endif
                @if (false)
                    <section class="report-section">
                        <div class="section-title-wrap">
                            <h3 class="section-heading">Quarterly New Donor Report</h3>
                            </div>
                            <div class="quarter-grid">
                                @foreach ($quarterly as $q)
                                    @php
                                        $count = $q['newDonors']->count();
                                        $percent = $totalNewDonors ? (int) round(($count / $totalNewDonors) * 100) : 0;
                                    @endphp
                                    <div class="quarter-card {{ $quarter === $q['quarter'] ? 'quarter-card--active' : '' }}" onclick="window.location='{{ route('reports', ['tab' => 'quarterly', 'year' => $year, 'month' => $month, 'quarter' => $q['quarter'], 'location' => $reportLocation]) }}'">
                                        <div class="quarter-top">
                                            <span class="quarter-tag">{{ $q['quarter'] }} {{ $year }}</span>
                                            <span class="quarter-months">{{ $q['periodLabel'] }}</span>
                                        </div>
                                        <div class="quarter-main">
                                            <span class="quarter-count-label">New Donors:</span>
                                            <strong class="quarter-count">{{ $count }}</strong>
                                        </div>
                                        <div class="quarter-progress-bar">
                                            <div class="quarter-progress-fill" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <span class="quarter-percent-meta">{{ $percent }}% of annual new donors</span>
                                    </div>
                                @endforeach
                            </div>
                        <div class="card-box">
                            <div class="box-header">
                                <h4 class="box-title">New Donors in {{ $quarter }} {{ $year }} ({{ $activeQuarter['periodLabel'] }})</h4>
                                <span class="record-badge">{{ $activeQuarter['newDonors']->count() }} Registered</span>
                            </div>
                            <div class="table-wrap">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Donor Name</th>
                                            <th>Blood Type</th>
                                            <th>Age</th>
                                            <th>Gender</th>
                                            <th>Location</th>
                                            <th>Contact</th>
                                            <th>First Donation / Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($activeQuarter['newDonors'] as $donor)
                                            <tr data-location="{{ mb_strtolower($donor->formattedAddress()) }}">
                                                <td class="font-medium text-dark">{{ $donor->full_name }}</td>
                                                <td><span class="blood-type-badge">{{ $donor->blood_type }}</span></td>
                                                <td>{{ $donor->age }}</td>
                                                <td><span class="gender-tag {{ strtolower($donor->gender) }}">{{ $donor->gender }}</span></td>
                                                <td>{{ $donor->formattedAddress() }}</td>
                                                <td>{{ $donor->contact }}</td>
                                                <td>{{ \App\Support\BloodBank::formatDate($donor->registered_date) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="empty-state">No new donors registered in {{ $quarter }} {{ $year }}.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                @elseif (false)
                    <section class="report-section">
                        <div class="section-title-wrap">
                                <h3 class="section-heading">Yearly Donor Report</h3>
                        </div>
                        <div class="kpi-grid">
                            <div class="kpi-card" style="grid-column: 1 / -1; flex-direction: row; align-items: center; justify-content: space-between; gap: 1rem;">
                                <div>
                                <span class="kpi-label">Total Annual Donors</span>
                                <strong class="kpi-value text-red">{{ $yearly['totalDonors'] }}</strong>
                            </div>
                                <div>
                                <span class="kpi-label">Total Donations</span>
                                <strong class="kpi-value text-blue">{{ $yearly['totalDonations'] }}</strong>
                            </div>
                                <div>
                                <span class="kpi-label">New Donors</span>
                                <strong class="kpi-value text-green">{{ $yearly['newDonorsCount'] }}</strong>
                            </div>
                                <div>
                                <span class="kpi-label">Repeat Donors</span>
                                <strong class="kpi-value text-purple">{{ $yearly['repeatDonorsCount'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="breakdown-grid">
                            <div class="card-box">
                                <h4 class="box-title"><i class="ti ti-map-pin"></i> Donors by Location</h4>
                                <div class="table-wrap">
                                    <table class="report-table compact">
                                        <thead>
                                            <tr>
                                                <th>Location</th>
                                                <th>Donors</th>
                                                <th>Donations</th>
                                                <th>Share</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($yearly['byLocation'] as $loc)
                                                <tr data-location="{{ mb_strtolower($loc['name']) }}">
                                                    <td class="font-medium">{{ $loc['name'] }}</td>
                                                    <td>{{ $loc['donorCount'] }}</td>
                                                    <td>{{ $loc['donationCount'] }} bags</td>
                                                    <td>
                                                        <div class="share-cell">
                                                            <div class="mini-bar"><div class="mini-bar-fill" style="width: {{ $loc['percentage'] }}%"></div></div>
                                                            <span class="share-pct">{{ $loc['percentage'] }}%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="empty-state">No location data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <h4 class="box-title" style="margin-top: 1.25rem;"><i class="ti ti-users"></i> Demographics</h4>
                                <div class="table-wrap">
                                    <table class="report-table compact">
                                        <thead>
                                            <tr>
                                                <th>Age Group</th>
                                                <th>Donors</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $visibleAges = collect($yearly['byAgeGroup'])->filter(fn ($age) => (int) $age['count'] > 0); @endphp
                                            @forelse ($visibleAges as $age)
                                                <tr>
                                                    <td class="font-medium">{{ $age['group'] }}</td>
                                                    <td><strong>{{ $age['count'] }}</strong></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="empty-state">No age data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="gender-yearly-box">
                                    <div class="gender-yearly-metric">
                                        <div class="gender-icon male"><i class="ti ti-gender-male"></i></div>
                                        <div>
                                            <span class="gender-label">Male</span>
                                            <strong class="gender-val">{{ $yearly['byGender']['male'] }}</strong>
                                        </div>
                                    </div>
                                    <div class="gender-yearly-metric">
                                        <div class="gender-icon female"><i class="ti ti-gender-female"></i></div>
                                        <div>
                                            <span class="gender-label">Female</span>
                                            <strong class="gender-val">{{ $yearly['byGender']['female'] }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-box">
                                <h4 class="box-title"><i class="ti ti-droplet"></i> Blood Type Inventory</h4>
                                <div class="bt-summary-grid" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                                    @foreach ($yearly['byBloodType'] as $bt)
                                        <div class="bt-stat-card" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                                            <div class="bt-badge-large">{{ $bt['type'] }}</div>
                                            <div class="bt-stat-details">
                                                <strong>{{ $bt['bags'] }} bags</strong>
                                                <span>{{ $bt['percentage'] }}%</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
                <div data-report-panel="demographics" @if($view === 'demographics' || $printAll) data-loaded="1" @endif @unless($view === 'demographics' || $printAll) hidden @endunless>
                    <h2 class="print-report-section-title" @unless($printAll) style="display:none" @endunless>Age &amp; Gender Demographics</h2>
                    @if ($printAll)
                        <p class="print-lead">Collection and consumption, by age and by gender, for {{ $periodicYear }}.</p>
                    @else
                        <div class="demo-board">
                            <article class="demo-summary">
                                <header class="demo-summary-head">
                                    <span class="demo-summary-icon" aria-hidden="true">🩸</span>
                                    <div>
                                        <h3>Blood Collection</h3>
                                        <p>{{ number_format($ageReport['totalDonors']) }} unique donors</p>
                                    </div>
                                </header>
                                <div class="demo-pair">
                                    <div>
                                        <h4 class="demo-subhead">Age group distribution</h4>
                                        <div class="demo-age-list">
                                    @foreach ($ageReport['groups'] as $item)
                                        @php
                                            $donorTotal = (int) $ageReport['totalDonors'];
                                            $donorShare = $item['percentage'] ?? ($donorTotal > 0 ? (int) round($item['count'] / $donorTotal * 100) : 0);
                                        @endphp
                                        <button type="button" class="demo-age-row" data-demographic-type="age" data-demographic-group="{{ $item['group'] }}" data-demographic-modal="{{ $item['modalId'] }}">
                                            <span class="demo-age-icon" aria-hidden="true">◷</span>
                                            <span class="demo-age-body">
                                                <span class="demo-age-top">
                                                    <strong>{{ $item['group'] }} years</strong>
                                                    <span>{{ number_format($item['count']) }} donors</span>
                                                </span>
                                                <span class="demo-age-bottom">
                                                    <span>{{ number_format($item['bags']) }} bags · {{ $donorShare }}%</span>
                                                    <span class="demo-track"><span class="demo-fill" style="width: {{ $donorShare }}%"></span></span>
                                                </span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                                    </div>
                                    <div>
                                <h4 class="demo-subhead">Gender comparison</h4>
                                <div class="demo-gender-grid">
                                    @foreach ($gender['rows'] as $row)
                                        @php
                                            $genderTotal = (int) $gender['total'];
                                            $genderShare = $row['percentage'] ?? ($genderTotal > 0 ? (int) round($row['count'] / $genderTotal * 100) : 0);
                                        @endphp
                                        <button type="button" class="demo-gender-card" data-demographic-type="gender" data-demographic-group="{{ $row['gender'] }}" data-demographic-modal="gender-donors-{{ strtolower($row['gender']) }}">
                                            <span class="demo-gender-icon {{ strtolower($row['gender']) }}" aria-hidden="true">{{ strtolower($row['gender']) === 'female' ? '♀' : '♂' }}</span>
                                            <span class="demo-gender-label">{{ $row['gender'] }}</span>
                                            <span class="demo-gender-count">{{ number_format($row['count']) }} donors</span>
                                            <span class="demo-gender-meta">{{ $genderShare }}% · {{ number_format($row['bags']) }} bags{{ isset($row['topBloodType']) && $row['topBloodType'] !== '—' ? ' · '.$row['topBloodType'] : '' }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                    </div>
                                </div>
                            </article>
                            <article class="demo-summary">
                                <header class="demo-summary-head">
                                    <span class="demo-summary-icon" aria-hidden="true">♥</span>
                                    <div>
                                        <h3>Blood Consumption</h3>
                                        <p>{{ number_format($consumptionDemographics['age']['totalPatients']) }} unique patients</p>
                                    </div>
                                </header>
                                <div class="demo-pair">
                                    <div>
                                        <h4 class="demo-subhead">Age group distribution</h4>
                                        <div class="demo-age-list">
                                    @foreach ($consumptionDemographics['age']['groups'] as $item)
                                        @php
                                            $patientTotal = (int) $consumptionDemographics['age']['totalPatients'];
                                            $patientShare = $patientTotal > 0 ? (int) round($item['count'] / $patientTotal * 100) : 0;
                                        @endphp
                                        <button type="button" class="demo-age-row" data-consumption-demographic-type="age" data-consumption-demographic-group="{{ $item['group'] }}">
                                            <span class="demo-age-icon" aria-hidden="true">◷</span>
                                            <span class="demo-age-body">
                                                <span class="demo-age-top">
                                                    <strong>{{ $item['group'] }} years</strong>
                                                    <span>{{ number_format($item['count']) }} patients</span>
                                                </span>
                                                <span class="demo-age-bottom">
                                                    <span>{{ number_format($item['bags']) }} bags · {{ $patientShare }}%</span>
                                                    <span class="demo-track"><span class="demo-fill" style="width: {{ $patientShare }}%"></span></span>
                                                </span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                                    </div>
                                    <div>
                                <h4 class="demo-subhead">Gender comparison</h4>
                                <div class="demo-gender-grid">
                                    @foreach ($consumptionDemographics['gender']['rows'] as $row)
                                        @php
                                            $patientGenderTotal = (int) ($consumptionDemographics['gender']['totalPatients'] ?? 0);
                                            $patientGenderShare = $patientGenderTotal > 0 ? (int) round($row['count'] / $patientGenderTotal * 100) : 0;
                                        @endphp
                                        <button type="button" class="demo-gender-card" data-consumption-demographic-type="gender" data-consumption-demographic-group="{{ $row['gender'] }}">
                                            <span class="demo-gender-icon {{ strtolower($row['gender']) }}" aria-hidden="true">{{ strtolower($row['gender']) === 'female' ? '♀' : '♂' }}</span>
                                            <span class="demo-gender-label">{{ $row['gender'] }}</span>
                                            <span class="demo-gender-count">{{ number_format($row['count']) }} patients</span>
                                            <span class="demo-gender-meta">{{ $patientGenderShare }}% · {{ number_format($row['bags']) }} bags</span>
                                        </button>
                                    @endforeach
                                </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endif
                    <section class="report-section">
                        <div class="card-box">
                            <div class="box-header">
                                <h4 class="box-title">Blood Collection — Age Group Distribution</h4>
                                <span class="record-badge">{{ number_format($ageReport['totalDonors']) }} Donors</span>
                            </div>
                            @if ($printAll)
                                @if ($collectionAgeRows === [])
                                    <p class="print-empty-line">No collections recorded for this period</p>
                                @else
                                    <div class="print-lines">
                                        <div class="print-ledger-head"><span>Age</span><span>Donors</span><span>Bags</span></div>
                                        @foreach ($collectionAgeRows as $item)
                                            <div class="print-line">
                                                <span>{{ $item['group'] }} years</span>
                                                <strong>{{ number_format($item['count']) }}</strong>
                                                <strong>{{ number_format($item['bags']) }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                            <div class="table-wrap">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Age Range</th>
                                            <th>Total Donors</th>
                                            <th>Total Collections / Bags</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($collectionAgeRows as $item)
                                            <tr class="clickable-row cursor-pointer hover:bg-slate-50 transition-colors duration-150" title="Click to view detailed donor list" data-demographic-type="age" data-demographic-group="{{ $item['group'] }}" data-demographic-modal="{{ $item['modalId'] }}">
                                                <td><strong>{{ $item['group'] }}</strong></td>
                                                <td><strong class="text-red">{{ number_format($item['count']) }}</strong></td>
                                                <td>{{ number_format($item['bags']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="empty-state">No donors found for this period.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                        @foreach ($ageReport['groups'] as $item)
                            <div id="{{ $item['modalId'] }}" class="modal-backdrop" hidden>
                                <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="{{ $item['modalId'] }}-title" style="max-width: 860px;">
                                    <button type="button" class="modal-close" data-modal-close="{{ $item['modalId'] }}" aria-label="Close">
                                        <i class="ti ti-x"></i>
                                    </button>
                                    <div class="modal-header">
                                        <div>
                                            <h3 class="modal-name" id="{{ $item['modalId'] }}-title">Age {{ $item['group'] }}</h3>
                                            <span class="modal-sub">{{ number_format($item['count']) }} donor{{ $item['count'] === 1 ? '' : 's' }}</span>
                                </div>
                                    </div>
                                    <div class="history-table-wrap">
                                        <table class="history-table">
                                        <thead>
                                            <tr>
                                                    <th>Full Name</th>
                                                <th>Exact Age</th>
                                                <th>Blood Type</th>
                                                    <th>Contact Number</th>
                                                    <th>Full Address</th>
                                            </tr>
                                        </thead>
                                        <tbody id="{{ $item['modalId'] }}-body">
                                            @if ($printAll)
                                                @foreach ($item['donors'] as $donor)
                                                    <tr data-location="{{ mb_strtolower($donor['address']) }}">
                                                        <td class="font-medium text-dark">{{ $donor['name'] }}</td>
                                                        <td>{{ $donor['age'] }}</td>
                                                        <td><span class="blood-type-badge">{{ $donor['blood_type'] }}</span></td>
                                                        <td>{{ $donor['contact'] }}</td>
                                                        <td>{{ $donor['address'] }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="5" class="empty-state">Select this age group to load donor records.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </div>
                        @endforeach
                    </section>
                    <section class="report-section">
                        <div class="card-box" id="gender-report-results">
                            <div class="box-header">
                                <h4 class="box-title">Blood Collection — Gender Comparison</h4>
                                <span class="record-badge">{{ number_format($gender['total']) }} Donors</span>
                            </div>
                            @if ($printAll)
                                @if ($collectionGenderRows === [])
                                    <p class="print-empty-line">No collections recorded for this period</p>
                                @else
                                    <div class="print-lines">
                                        <div class="print-ledger-head"><span>Gender</span><span>Donors</span><span>Bags</span></div>
                                        @foreach ($collectionGenderRows as $row)
                                            <div class="print-line">
                                                <span>{{ $row['gender'] }}</span>
                                                <strong>{{ number_format($row['count']) }}</strong>
                                                <strong>{{ number_format($row['bags']) }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                            <div class="table-wrap">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Gender</th>
                                            <th>Total Donors</th>
                                            <th>Total Blood Bags Collected</th>
                                            <th>Top Blood Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($collectionGenderRows as $row)
                                            <tr class="clickable-row cursor-pointer hover:bg-slate-50 transition-colors duration-150" title="Click to view detailed donor list" data-demographic-type="gender" data-demographic-group="{{ $row['gender'] }}" data-demographic-modal="gender-donors-{{ strtolower($row['gender']) }}">
                                                <td><span class="gender-tag {{ strtolower($row['gender']) }}">{{ $row['gender'] }}</span></td>
                                                <td><strong>{{ number_format($row['count']) }}</strong></td>
                                                <td>{{ number_format($row['bags']) }}</td>
                                                <td>
                                                    @if ($row['topBloodType'] === '—')
                                                        —
                                                    @else
                                                        <span class="blood-type-badge">{{ $row['topBloodType'] }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                </div>
                            @endif
                                </div>
                        @foreach ($gender['rows'] as $row)
                            <div id="gender-donors-{{ strtolower($row['gender']) }}" class="modal-backdrop" hidden>
                                <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="gender-donors-title-{{ strtolower($row['gender']) }}" style="max-width: 860px;">
                                    <button type="button" class="modal-close" data-modal-close="gender-donors-{{ strtolower($row['gender']) }}" aria-label="Close">
                                        <i class="ti ti-x"></i>
                                    </button>
                                    <div class="modal-header">
                                        <div>
                                            <h3 class="modal-name" id="gender-donors-title-{{ strtolower($row['gender']) }}">{{ $row['gender'] }} Donors</h3>
                                            <span class="modal-sub">{{ number_format($row['count']) }} donor{{ $row['count'] === 1 ? '' : 's' }}</span>
                        </div>
                            </div>
                                    <div class="history-table-wrap">
                                        <table class="history-table">
                                    <thead>
                                        <tr>
                                                    <th>Full Name</th>
                                            <th>Blood Type</th>
                                            <th>Age</th>
                                                    <th>Contact Number</th>
                                                    <th>Last Donation Date</th>
                                                    <th>Complete Address</th>
                                        </tr>
                                    </thead>
                                    <tbody id="gender-donors-{{ strtolower($row['gender']) }}-body">
                                        @if ($printAll)
                                            @forelse ($row['donors'] as $donor)
                                                <tr data-location="{{ mb_strtolower($donor['address']) }}">
                                                    <td class="font-medium text-dark">{{ $donor['name'] }}</td>
                                                    <td><span class="blood-type-badge">{{ $donor['blood_type'] }}</span></td>
                                                    <td>{{ $donor['age'] }}</td>
                                                    <td>{{ $donor['contact'] }}</td>
                                                    <td>{{ $donor['last_donation'] ? \App\Support\BloodBank::formatDate($donor['last_donation']) : '—' }}</td>
                                                    <td>{{ $donor['address'] }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="empty-state">No {{ strtolower($row['gender']) }} donors found for this period.</td>
                                                </tr>
                                            @endforelse
                                        @else
                                            <tr>
                                                <td colspan="6" class="empty-state">Select this gender to load donor records.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                            </div>
                                        @endforeach
                    </section>
                    <section class="report-section demo-split">
                        <div class="card-box">
                            <div class="box-header">
                                <h4 class="box-title">Blood Consumption — Age Group Distribution</h4>
                                <span class="record-badge">{{ number_format($consumptionDemographics['age']['totalPatients']) }} Patients</span>
                            </div>
                            @if ($printAll)
                                @if ($consumptionAgeRows === [])
                                    <p class="print-empty-line">No consumption recorded for this period</p>
                                @else
                                    <div class="print-lines">
                                        <div class="print-ledger-head"><span>Age</span><span>Patients</span><span>Bags</span></div>
                                        @foreach ($consumptionAgeRows as $item)
                                            <div class="print-line">
                                                <span>{{ $item['group'] }} years</span>
                                                <strong>{{ number_format($item['count']) }}</strong>
                                                <strong>{{ number_format($item['bags']) }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                            <div class="table-wrap">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Age Range</th>
                                            <th>Total Patients</th>
                                            <th>Blood Bags Consumed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($consumptionAgeRows as $item)
                                            <tr class="clickable-row cursor-pointer hover:bg-slate-50 transition-colors duration-150" title="Click to view consumption patients" data-consumption-demographic-type="age" data-consumption-demographic-group="{{ $item['group'] }}">
                                                <td><strong>{{ $item['group'] }}</strong></td>
                                                <td><strong class="text-red">{{ number_format($item['count']) }}</strong></td>
                                                <td>{{ number_format($item['bags']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="empty-state">No blood consumption patients found for this period.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                        <div class="card-box" id="consumption-gender-report-results">
                            <div class="box-header">
                                <h4 class="box-title">Blood Consumption — Gender Comparison</h4>
                                <span class="record-badge">{{ number_format($consumptionDemographics['gender']['totalPatients']) }} Patients</span>
                            </div>
                            @if ($printAll)
                                @if ($consumptionGenderRows === [])
                                    <p class="print-empty-line">No consumption recorded for this period</p>
                                @else
                                    <div class="print-lines">
                                        <div class="print-ledger-head"><span>Gender</span><span>Patients</span><span>Bags</span></div>
                                        @foreach ($consumptionGenderRows as $row)
                                            <div class="print-line">
                                                <span>{{ $row['gender'] }}</span>
                                                <strong>{{ number_format($row['count']) }}</strong>
                                                <strong>{{ number_format($row['bags']) }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                            <div class="table-wrap">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Gender</th>
                                            <th>Total Patients</th>
                                            <th>Blood Bags Consumed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($consumptionGenderRows as $row)
                                            <tr class="clickable-row cursor-pointer hover:bg-slate-50 transition-colors duration-150" title="Click to view consumption patients" data-consumption-demographic-type="gender" data-consumption-demographic-group="{{ $row['gender'] }}">
                                                <td><span class="gender-tag {{ strtolower($row['gender']) }}">{{ $row['gender'] }}</span></td>
                                                <td><strong>{{ number_format($row['count']) }}</strong></td>
                                                <td>{{ number_format($row['bags']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                        <div id="consumption-age-patients" class="modal-backdrop" hidden>
                            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="consumption-age-patients-title" style="max-width: 860px;">
                                <button type="button" class="modal-close" data-modal-close="consumption-age-patients" aria-label="Close">
                                    <i class="ti ti-x"></i>
                                </button>
                                <div class="modal-header">
                                    <div>
                                        <h3 class="modal-name" id="consumption-age-patients-title">Consumption Patients</h3>
                                        <span class="modal-sub" id="consumption-age-patients-summary"></span>
                                    </div>
                                </div>
                                <div class="history-table-wrap">
                                    <table class="history-table">
                                        <thead>
                                            <tr>
                                                <th>Patient Name</th>
                                                <th>Age</th>
                                                <th>Gender</th>
                                                <th>Blood Bags Consumed</th>
                                            </tr>
                                        </thead>
                                        <tbody id="consumption-age-patients-body">
                                            <tr><td colspan="4" class="empty-state">Select an age group to load patient records.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div id="consumption-gender-patients" class="modal-backdrop" hidden>
                            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="consumption-gender-patients-title" style="max-width: 860px;">
                                <button type="button" class="modal-close" data-modal-close="consumption-gender-patients" aria-label="Close">
                                    <i class="ti ti-x"></i>
                                </button>
                                <div class="modal-header">
                                    <div>
                                        <h3 class="modal-name" id="consumption-gender-patients-title">Consumption Patients</h3>
                                        <span class="modal-sub" id="consumption-gender-patients-summary"></span>
                                    </div>
                                </div>
                                <div class="history-table-wrap">
                                    <table class="history-table">
                                        <thead>
                                            <tr>
                                                <th>Patient Name</th>
                                                <th>Age</th>
                                                <th>Gender</th>
                                                <th>Blood Bags Consumed</th>
                                            </tr>
                                        </thead>
                                        <tbody id="consumption-gender-patients-body">
                                            <tr><td colspan="4" class="empty-state">Select a gender to load patient records.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                @if ($printAll)
                    </div>
                    </section>
                    <section class="print-sheet print-sheet--consumption">
                        <div class="print-sheet-main">
                            <h2 class="print-report-section-title">Blood Consumption — {{ $periodicYear }}</h2>
                            <p class="print-lead">Units issued to patients in {{ $periodicYear }}.</p>
                @else
                </div>
                @endif
                <div data-report-panel="consumption" @if($view === 'consumption' || $printAll) data-loaded="1" @endif @unless($view === 'consumption' || $printAll) hidden @endunless>
                    <section class="report-section">
                        @if ($printAll)
                            <div class="print-consumption-summary">
                                <h3 class="print-mini-title">By province</h3>
                                @php
                                    $provinceRows = collect($consumption['provinces'])
                                        ->filter(fn (array $province): bool => $printHasActivity($province, 'patient_count', 'total_units'))
                                        ->values()
                                        ->all();
                                @endphp
                                @if ($provinceRows === [])
                                    <p class="print-empty-line">No consumption recorded for this period</p>
                                @else
                                    <div class="print-lines print-lines--quad">
                                        <div class="print-ledger-head"><span>Province</span><span>Municipalities</span><span>Records</span><span>Units</span></div>
                                        @foreach ($provinceRows as $province)
                                            <div class="print-line">
                                                <span>{{ $province['name'] }}</span>
                                                <strong>{{ number_format($province['municipality_count']) }}</strong>
                                                <strong>{{ number_format($province['patient_count']) }}</strong>
                                                <strong>{{ number_format($province['total_units']) }}</strong>
                                            </div>
                                        @endforeach
                                        <div class="print-line print-line--total">
                                            <span>Total Consumed Units:</span>
                                            <strong></strong>
                                            <strong></strong>
                                            <strong>{{ number_format($consumption['totalUnits']) }}</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="card-box">
                            <div class="pill-group">
                                @foreach ($consumption['byBloodType'] as $item)
                                    <div class="breakdown-pill">
                                        <span class="badge-bt">{{ $item['type'] }}</span>
                                        <span class="pill-count"><strong>{{ number_format($item['total']) }}</strong> units</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <section class="province-report-section">
                            <div class="box-header">
                                <h4 class="box-title">Consumption by Province</h4>
                                <span class="record-badge">{{ number_format($consumption['provinceCount']) }} Provinces</span>
                            </div>
                            <div class="consumption-province-list">
                                @forelse ($consumption['provinces'] as $provinceIndex => $province)
                                    @php
                                        $provinceModalId = 'consumption-province-'.$provinceIndex;
                                    @endphp
                                    <button
                                        type="button"
                                        class="province-card province-card--trigger"
                                        title="Click to view municipalities and patient records"
                                        onclick="openReportModal('{{ $provinceModalId }}')"
                                        data-modal-open="{{ $provinceModalId }}"
                                    >
                                        <span class="province-summary-name">{{ $province['name'] }}</span>
                                        <span class="province-summary-badges">
                                            <span>{{ number_format($province['municipality_count']) }} Municipalities</span>
                                            <span>{{ number_format($province['patient_count']) }} Records</span>
                                        </span>
                                        <span class="province-summary-total">
                                            <span>Total Units</span>
                                            <strong>{{ number_format($province['total_units']) }}</strong>
                                        </span>
                                        <span class="province-chevron" aria-hidden="true">
                                            <svg viewBox="0 0 20 20" focusable="false"><path d="M7 5l6 5-6 5" /></svg>
                                        </span>
                                    </button>
                                @empty
                                    <div class="empty-state">No consumption records match your filters.</div>
                                @endforelse
                            </div>
                            @foreach ($consumption['provinces'] as $provinceIndex => $province)
                                @php
                                    $provinceModalId = 'consumption-province-'.$provinceIndex;
                                @endphp
                                <div id="{{ $provinceModalId }}" class="modal-backdrop" hidden>
                                    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="{{ $provinceModalId }}-title" style="max-width: 920px;">
                                        <button type="button" class="modal-close" data-modal-close="{{ $provinceModalId }}" aria-label="Close">
                                            <i class="ti ti-x"></i>
                                        </button>
                                        <div class="modal-header">
                                            <div>
                                                <h3 class="modal-name" id="{{ $provinceModalId }}-title">{{ $province['name'] }}</h3>
                                                <span class="modal-sub">
                                                    {{ number_format($province['municipality_count']) }} municipalit{{ $province['municipality_count'] === 1 ? 'y' : 'ies' }}
                                                    · {{ number_format($province['patient_count']) }} record{{ $province['patient_count'] === 1 ? '' : 's' }}
                                                    · {{ number_format($province['total_units']) }} units
                                                </span>
                                            </div>
                                        </div>
                                        <p class="modal-history-title">Municipalities — click a row to view patient records</p>
                                        <div class="history-table-wrap">
                                            <table class="history-table">
                                                <thead>
                                                    <tr>
                                                        <th>Municipality</th>
                                                        <th>Records</th>
                                                        <th>Total Units</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($province['municipalities'] as $municipality)
                                                        <tr
                                                            class="clickable-row cursor-pointer hover:bg-slate-50 transition-colors duration-150"
                                                            title="Click to view patient records"
                                                            data-consumption-province="{{ $province['name'] }}"
                                                            data-consumption-municipality="{{ $municipality['name'] }}"
                                                        >
                                                            <td class="font-medium text-dark">{{ $municipality['name'] }}</td>
                                                            <td>{{ number_format($municipality['patient_count']) }}</td>
                                                            <td><strong>{{ number_format($municipality['total_units']) }}</strong></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div id="consumption-municipality-details" class="modal-backdrop" hidden>
                                <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="consumption-municipality-title" style="max-width: 960px;">
                                    <button type="button" class="modal-close" data-modal-close="consumption-municipality-details" aria-label="Close">
                                        <i class="ti ti-x"></i>
                                    </button>
                                    <div class="modal-header">
                                        <div>
                                            <h3 class="modal-name" id="consumption-municipality-title">Municipality Consumption</h3>
                                            <span class="modal-sub" id="consumption-municipality-summary"></span>
                                        </div>
                                    </div>
                                    <div class="history-table-wrap">
                                        <table class="history-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Patient Name</th>
                                                    <th>Age</th>
                                                    <th>Sex</th>
                                                    <th>Component</th>
                                                    <th>Units</th>
                                                </tr>
                                            </thead>
                                            <tbody id="consumption-municipality-records">
                                                <tr>
                                                    <td colspan="6" class="empty-state">Select a municipality to load patient records.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @if ($consumption['provinceCount'] > 0)
                                <table class="report-table consumption-report-total">
                                    <colgroup>
                                        <col style="width: 34%">
                                        <col style="width: 22%">
                                        <col style="width: 28%">
                                        <col style="width: 16%">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <td class="total-label">Total Consumed Units:</td>
                                            <td class="total-val">{{ number_format($consumption['totalUnits']) }}</td>
                                            <td></td>
                                                <td></td>
                                            </tr>
                                    </tbody>
                                </table>
                            @endif
                        </section>
                    </section>
                            </div>
                @if ($printAll)
                        </div>
                        <div class="print-only-signatures">
                            <div>
                                <p>Prepared by: ____________________</p>
                                <span>Blood Bank Staff</span>
                            </div>
                            <div>
                                <p>Approved by: ____________________</p>
                                <span>Medical Officer / Administrator</span>
                            </div>
                        </div>
                    </section>
                @endif
                        </div>
                @unless ($printAll)
                <div class="print-only-signatures">
                    <div>
                        <p>Prepared by: ____________________</p>
                        <span>Blood Bank Staff</span>
                    </div>
                    <div>
                        <p>Approved by: ____________________</p>
                        <span>Medical Officer / Administrator</span>
                    </div>
                </div>
                @endunless
            </div>
        </div>
    </div>
@endsection
