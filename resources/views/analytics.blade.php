@extends('core/base::layouts.master')

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <form method="GET" action="{{ route('yandex-metrica.analytics.view') }}" class="form-inline bg-white p-3 rounded shadow-sm border">
                <div class="d-flex align-items-center">
                    <label class="me-2 fw-bold">Date Range:</label>
                    <input type="date" name="date_from" value="{{ $startDate->format('Y-m-d') }}" class="form-control me-2">
                    <span class="me-2">-</span>
                    <input type="date" name="date_to" value="{{ $endDate->format('Y-m-d') }}" class="form-control me-2">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter"></i> Filter</button>
                    <a href="{{ route('yandex-metrica.analytics.view') }}" class="btn btn-link">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if($error)
                <div class="alert alert-danger mb-3">
                    {{ $error }}
                </div>
            @endif

            @if(empty($data['dates']))
                 <div class="alert alert-warning">
                     {{ trans('plugins/yandex-metrica::yandex-metrica.settings.no_data') ?? 'No data available.' }}
                 </div>
            @else
                
                @php
                    $maxVal = max(
                        empty($data['visits']) ? 0 : max($data['visits']),
                        empty($data['visitors']) ? 0 : max($data['visitors']),
                        empty($data['pageviews']) ? 0 : max($data['pageviews'])
                    );
                    if($maxVal == 0) $maxVal = 1; // prevent div by zero
                @endphp

                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title">Traffic Overview</h4>
                    </div>
                    <div class="card-body">
                        <!-- Custom CSS Bar Chart -->
                        <div class="d-flex justify-content-between align-items-end" style="height: 300px; padding-bottom: 20px; border-bottom: 1px solid #eee; overflow-x: auto;">
                            @foreach($data['dates'] as $index => $date)
                                @php
                                    $visits = $data['visits'][$index] ?? 0;
                                    $pageviews = $data['pageviews'][$index] ?? 0;
                                    
                                    $hVisits = ($visits / $maxVal) * 100;
                                    $hPageviews = ($pageviews / $maxVal) * 100;
                                @endphp
                                <div class="text-center mx-1" style="min-width: 40px; flex: 1;">
                                    <div class="d-flex justify-content-center align-items-end" style="height: 250px;">
                                         <!-- Pageviews Bar (Background/Lighter) -->
                                        <div style="width: 12px; height: {{ $hPageviews }}%; background-color: #e2e8f0; margin-right: 2px; border-radius: 3px 3px 0 0;" title="Pageviews: {{ $pageviews }}"></div>
                                        <!-- Visits Bar (Foreground/Darker) -->
                                        <div style="width: 12px; height: {{ $hVisits }}%; background-color: #206bc4; border-radius: 3px 3px 0 0;" title="Visits: {{ $visits }}"></div>
                                    </div>
                                    <div class="mt-2 small text-muted rotate-45" style="font-size: 10px; transform: rotate(-45deg); white-space: nowrap;">{{ $date }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <span class="badge bg-primary me-2">Visits</span>
                            <span class="badge bg-secondary text-dark" style="background-color: #e2e8f0 !important;">Pageviews</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Detailed Data</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>{{ trans('plugins/yandex-metrica::yandex-metrica.widget.visits') }}</th>
                                    <th>{{ trans('plugins/yandex-metrica::yandex-metrica.widget.visitors') }}</th>
                                    <th>{{ trans('plugins/yandex-metrica::yandex-metrica.widget.pageviews') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_reverse($data['dates'], true) as $index => $date)
                                <tr>
                                    <td>{{ $date }}</td>
                                    <td>{{ $data['visits'][$index] ?? 0 }}</td>
                                    <td>{{ $data['visitors'][$index] ?? 0 }}</td>
                                    <td>{{ $data['pageviews'][$index] ?? 0 }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
