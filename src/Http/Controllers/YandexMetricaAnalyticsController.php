<?php

namespace Botble\YandexMetrica\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Facades\Assets;
use Botble\YandexMetrica\Services\YandexMetricaClient;
use Carbon\Carbon;

class YandexMetricaAnalyticsController extends BaseController
{
    public function index(YandexMetricaClient $client, \Illuminate\Http\Request $request)
    {
        $this->pageTitle(trans('plugins/yandex-metrica::yandex-metrica.settings.title'));
        
        $token = setting('yandex_metrica_access_token');
        $counterId = setting('yandex_metrica_counter_id');
        
        $data = [];
        $error = null;
        
        $startDate = $request->input('date_from') ? Carbon::parse($request->input('date_from')) : Carbon::now()->subDays(29);
        $endDate = $request->input('date_to') ? Carbon::parse($request->input('date_to')) : Carbon::now();
        
        // Ensure we pass Y-m-d to API client if we were to modify it, but for now client takes "days" integer.
        // We need to support date ranges in the client or calculate days diff.
        // Let's check client implementation. The getTrafficSummary takes $days.
        // We need to modify getTrafficSummary or use a new method for range.
        // For quick fix without breaking client: calculate diff in days.
        
        $days = $endDate->diffInDays($startDate) + 1; // +1 to include today
        if ($days < 1) $days = 30; // safety

        if ($token && $counterId) {
            try {
                // Fetch dynamic dates
                $response = $client->getTrafficSummary($token, $counterId, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
                
                if (isset($response['data'][0]['metrics']) && isset($response['time_intervals'])) {
                    $visits = $response['data'][0]['metrics'][0] ?? [];
                    $visitors = $response['data'][0]['metrics'][1] ?? [];
                    $pageviews = $response['data'][0]['metrics'][2] ?? [];
                    $intervals = $response['time_intervals'] ?? [];
                    
                    $chartData = [
                        'dates' => [],
                        'visits' => [],
                        'visitors' => [],
                        'pageviews' => []
                    ];

                    foreach ($intervals as $index => $interval) {
                        $chartData['dates'][] = Carbon::parse($interval[0])->format('d M');
                        $chartData['visits'][] = $visits[$index] ?? 0;
                        $chartData['visitors'][] = $visitors[$index] ?? 0;
                        $chartData['pageviews'][] = $pageviews[$index] ?? 0;
                    }
                    
                    $data = $chartData;
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        } else {
             $error = trans('plugins/yandex-metrica::yandex-metrica.widget.setup_required');
        }

        return view('plugins/yandex-metrica::analytics', compact('data', 'error', 'startDate', 'endDate'));
    }
}
