@extends('dasgboard.layouts.app')

@section('title', 'Visitor Tracking')

@push('styles')
<style>
.visitor-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;margin-bottom:24px}.visitor-card{display:flex;align-items:center;gap:16px;padding:21px;border:1px solid var(--line);border-radius:15px;background:#fff;box-shadow:0 7px 25px #2a180f0b}.visitor-icon{display:grid;flex:0 0 52px;width:52px;height:52px;place-items:center;border-radius:14px;background:#fff0e3;color:var(--primary);font-size:21px}.visitor-card:nth-child(2) .visitor-icon{background:#eaf2ff;color:#3678d4}.visitor-card:nth-child(3) .visitor-icon{background:#e8f8ee;color:#25834c}.visitor-card:nth-child(4) .visitor-icon{background:#f4edff;color:#7c4dcc}.visitor-card small{display:block;color:var(--muted)}.visitor-card strong{display:block;margin-top:3px;font-size:27px}.section-heading{margin:4px 0 14px}.section-heading h2{margin:0;font-size:21px}.section-heading p{margin:4px 0 0;color:var(--muted)}.event-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:24px}.event-card{position:relative;overflow:hidden;padding:19px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:0 6px 22px #2a180f09}.event-card:after{content:'';position:absolute;right:-22px;bottom:-24px;width:75px;height:75px;border-radius:50%;background:var(--event-soft,#fff3e9)}.event-icon{display:grid;width:42px;height:42px;margin-bottom:15px;place-items:center;border-radius:11px;background:var(--event-soft,#fff3e9);color:var(--event-color,var(--primary));font-size:18px}.event-card small{display:block;color:var(--muted)}.event-card strong{display:block;margin-top:3px;font-size:25px}.event-card.rate{--event-soft:#e8f8ee;--event-color:#25834c}.report-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:15px}.report-head h2{margin:0;font-size:21px}.report-head span{color:var(--muted);font-size:14px}.visitor-bar-wrap{display:flex;align-items:center;gap:12px}.visitor-bar{width:150px;height:8px;overflow:hidden;border-radius:99px;background:#f3e9e2}.visitor-bar span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--primary),#ff9a4f)}@media(max-width:1150px){.visitor-grid,.event-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.visitor-grid,.event-grid{grid-template-columns:1fr}.visitor-bar{width:90px}}
</style>
@endpush

@section('content')
<div class="page-heading"><div><h1>Visitor Tracking</h1><p>Monitor unique visitors to your landing page.</p></div><a href="{{ route('home') }}" target="_blank" class="site-link"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open Website</a></div>

<p id="reportRefreshStatus" role="status" aria-live="polite" data-report-url="{{ route('admin.tracking.edit', 'visitors') }}">Updates automatically every 10 seconds.</p>
<section class="visitor-grid" aria-label="Visitor summary">
    <article class="visitor-card"><span class="visitor-icon"><i class="fa-solid fa-users"></i></span><div><small>Total Visitors</small><strong data-visitor-metric="totalVisitors">{{ number_format($totalVisitors) }}</strong></div></article>
    <article class="visitor-card"><span class="visitor-icon"><i class="fa-solid fa-calendar-day"></i></span><div><small>Today</small><strong data-visitor-metric="todayVisitors">{{ number_format($todayVisitors) }}</strong></div></article>
    <article class="visitor-card"><span class="visitor-icon"><i class="fa-solid fa-calendar-week"></i></span><div><small>Last 7 Days</small><strong data-visitor-metric="sevenDayVisitors">{{ number_format($sevenDayVisitors) }}</strong></div></article>
    <article class="visitor-card"><span class="visitor-icon"><i class="fa-solid fa-calendar"></i></span><div><small>Last 30 Days</small><strong data-visitor-metric="thirtyDayVisitors">{{ number_format($thirtyDayVisitors) }}</strong></div></article>
</section>

@php
    $events = [
        'page_view' => ['Page Views', 'fa-regular fa-eye', '#3678d4', '#eaf2ff'],
        'checkout_view' => ['Checkout Views', 'fa-solid fa-cart-shopping', '#8b52d1', '#f4edff'],
        'order_button_click' => ['Order Button Clicks', 'fa-solid fa-arrow-pointer', '#d56a12', '#fff2e5'],
        'form_start' => ['Form Starts', 'fa-regular fa-pen-to-square', '#167d73', '#e7f7f5'],
        'order_completed' => ['Orders Completed', 'fa-solid fa-circle-check', '#25834c', '#e8f8ee'],
        'whatsapp_click' => ['WhatsApp Clicks', 'fa-brands fa-whatsapp', '#159447', '#e7f8ed'],
    ];
    $pageViews = (int) ($eventCounts['page_view'] ?? 0);
    $completedOrders = (int) ($eventCounts['order_completed'] ?? 0);
    $conversionRate = $pageViews > 0 ? ($completedOrders / $pageViews) * 100 : 0;
@endphp
<div class="section-heading"><h2>Customer Activity</h2><p>Order button and WhatsApp clicks on the website are counted here. Buttons on this admin page are not counted.</p></div>
<form id="activityReportForm" method="GET" action="{{ route('admin.tracking.edit', 'visitors') }}" style="display:flex;gap:12px;align-items:center;margin-bottom:18px">
    <label for="activityPeriod">Activity period</label><select id="activityPeriod" name="period" style="padding:8px;border:1px solid #ebded4;border-radius:8px">
        @foreach(['all' => 'All time', 'today' => 'Today', '7' => 'Last 7 days', '30' => 'Last 30 days'] as $value => $label)<option value="{{ $value }}" @selected((string) $period === (string) $value)>{{ $label }}</option>@endforeach
    </select><button type="submit" style="padding:8px 16px;border:0;border-radius:8px;background:#ff6b00;color:white">Refresh report</button>
</form>
<section class="event-grid" aria-label="Customer activity">
    @foreach($events as $eventKey => [$eventLabel, $eventIcon, $eventColor, $eventSoft])
        <article class="event-card" style="--event-color:{{ $eventColor }};--event-soft:{{ $eventSoft }}"><span class="event-icon"><i class="{{ $eventIcon }}"></i></span><small>{{ $eventLabel }}</small><strong data-event-metric="{{ $eventKey }}">{{ number_format($eventCounts[$eventKey] ?? 0) }}</strong></article>
    @endforeach
    <article class="event-card rate"><span class="event-icon"><i class="fa-solid fa-chart-line"></i></span><small>Order Conversion Rate</small><strong id="conversionRate">{{ number_format($conversionRate, 1) }}%</strong></article>
</section>

<section class="panel">
    <div class="report-head"><h2>Daily Visitor Report</h2><span>Latest 30 days with visits</span></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Date</th><th>Day</th><th>Unique Visitors</th><th>Activity</th></tr></thead>
        <tbody id="dailyVisitorRows">
        @php($maxVisitors = max(1, (int) $dailyVisitors->max('visitors')))
        @forelse($dailyVisitors as $day)
            <tr><td><strong>{{ $day->visited_on->format('d M, Y') }}</strong></td><td>{{ $day->visited_on->format('l') }}</td><td>{{ number_format($day->visitors) }}</td><td><div class="visitor-bar-wrap"><div class="visitor-bar"><span style="width:{{ max(4, ($day->visitors / $maxVisitors) * 100) }}%"></span></div></div></td></tr>
        @empty
            <tr><td colspan="4" class="empty"><i class="fa-regular fa-folder-open"></i> No visitor data recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('asset/js/visitor-report.js') }}?v={{ filemtime(public_path('asset/js/visitor-report.js')) }}"></script>
@endpush
