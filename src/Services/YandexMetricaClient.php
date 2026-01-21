<?php

namespace Botble\YandexMetrica\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class YandexMetricaClient
{
    protected string $baseUrl = 'https://api-metrika.yandex.com/management/v1';
    protected string $statUrl = 'https://api-metrika.yandex.com/stat/v1/data/bytime';

    // Credentials from the reference WordPress plugin
    public const CLIENT_ID = 'e1a0017805e24d7b9395f969b379b7bf';
    public const CLIENT_SECRET = '410e753d1ab9478eaa21aa2c3f9a7d88';

    public function getAuthorizationUrl(): string
    {
        // Yandex OAuth URL
        return 'https://oauth.yandex.com/authorize?response_type=code&client_id=' . self::CLIENT_ID;
    }

    public function getAccessToken(string $code)
    {
        $response = Http::withoutVerifying()->asForm()->post('https://oauth.yandex.com/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
        ]);

        if ($response->failed()) {
            throw new Exception('Could not retrieve token: ' . $response->body());
        }

        return $response->json('access_token');
    }

    public function getCounters(string $accessToken)
    {
        $response = Http::withoutVerifying()->withToken($accessToken)->get($this->baseUrl . '/counters');

        if ($response->failed()) {
            return [];
        }

        return $response->json('counters', []);
    }

    public function getTrafficSummary(string $accessToken, string $counterId, $date1 = 7, $date2 = null)
    {
        if (is_int($date1)) {
            $startDate = now()->subDays($date1)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        } else {
            $startDate = $date1;
            $endDate = $date2 ?: now()->format('Y-m-d');
        }

        $response = Http::withoutVerifying()->withToken($accessToken)->get($this->statUrl, [
            'ids' => $counterId,
            'metrics' => 'ym:s:visits,ym:s:users,ym:s:pageviews',
            'group' => 'day',
            'date1' => $startDate,
            'date2' => $endDate,
        ]);

        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }
}
