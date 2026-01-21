@if(isset($error) && $error)
    <div class="alert alert-danger" style="margin-bottom: 10px;">
        {{ $error }}
    </div>
@endif

<div class="d-block mb-3">
    <strong>Yandex Metrica Report</strong> 
    <span class="text-muted small">({{ now()->format('H:i:s') }})</span>
</div>

<div class="row row-cards" id="ym-stats-container">
    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar">
                            <i class="ti ti-users"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            {{ trans('plugins/yandex-metrica::yandex-metrica.widget.visitors') }}
                        </div>
                        <div class="text-muted" id="ym-visitors">
                            {{ $totals['visitors'] ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                         <span class="bg-green text-white avatar">
                            <i class="ti ti-eye"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            {{ trans('plugins/yandex-metrica::yandex-metrica.widget.pageviews') }}
                        </div>
                        <div class="text-muted" id="ym-pageviews">
                            {{ $totals['pageviews'] ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-warning text-white avatar">
                            <i class="ti ti-click"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            {{ trans('plugins/yandex-metrica::yandex-metrica.widget.visits') }}
                        </div>
                        <div class="text-muted" id="ym-visits">
                            {{ $totals['visits'] ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
