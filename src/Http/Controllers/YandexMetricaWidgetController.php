<?php

namespace Botble\YandexMetrica\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\YandexMetrica\Services\YandexMetricaClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class YandexMetricaWidgetController extends BaseController
{
    public function index(BaseHttpResponse $response, YandexMetricaClient $client)
    {
        Log::info('YandexMetricaWidgetController::index calling.');

        $accessToken = setting('yandex_metrica_access_token');
        $counterId = setting('yandex_metrica_counter_id');
        $totals = [
            'visits' => '-',
            'visitors' => '-',
            'pageviews' => '-',
        ];
        $error = null;

        if (! $accessToken || ! $counterId) {
             $error = trans('plugins/yandex-metrica::yandex-metrica.widget.setup_required');
        } else {
            try {
                $data = $client->getTrafficSummary($accessToken, $counterId);
                
                if (isset($data['totals'])) {
                     $totals['visits'] = array_sum($data['totals'][0] ?? []);
                     $totals['visitors'] = array_sum($data['totals'][1] ?? []);
                     $totals['pageviews'] = array_sum($data['totals'][2] ?? []);
                } else {
                    if (isset($data['data'][0]['metrics'])) {
                       $totals['visits'] = array_sum($data['data'][0]['metrics'][0] ?? []);
                       $totals['visitors'] = array_sum($data['data'][0]['metrics'][1] ?? []);
                       $totals['pageviews'] = array_sum($data['data'][0]['metrics'][2] ?? []);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Yandex Metrica API Error in index: ' . $e->getMessage());
                $error = $e->getMessage();
            }
        }

        return $response
            ->setData(view('plugins/yandex-metrica::widgets.general', compact('totals', 'error'))->render());
    }

    public function getData(BaseHttpResponse $response, YandexMetricaClient $client)
    {
        // ... (keeping this as legacy or for future ajax chart implementation)
        return $response->setData([]);
    }
}
