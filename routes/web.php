<?php

use Botble\Base\Facades\AdminHelper;
use Botble\YandexMetrica\Http\Controllers\Settings\YandexMetricaSettingController;
use Botble\YandexMetrica\Http\Controllers\YandexMetricaWidgetController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::group([
        'prefix' => 'settings/yandex-metrica',
        'as' => 'yandex-metrica.settings',
        'permission' => 'yandex-metrica.settings',
    ], function (): void {
        Route::get('/', [
            'uses' => YandexMetricaSettingController::class . '@edit',
        ]);

        Route::put('/', [
            'as' => '.update',
            'uses' => YandexMetricaSettingController::class . '@update',
        ]);
    });

    Route::group([
        'prefix' => 'analytics/yandex-metrica',
        'as' => 'yandex-metrica.analytics.',
        'permission' => 'yandex-metrica.settings', 
    ], function () {
         Route::get('/', [
            'as' => 'view',
            'uses' => \Botble\YandexMetrica\Http\Controllers\YandexMetricaAnalyticsController::class . '@index',
        ]);
         // Helper routes for widget (legacy or if we bring it back)
         Route::get('general', [
            'as' => 'widgets.general',
            'uses' => YandexMetricaWidgetController::class . '@index',
        ]);

        Route::get('data', [
            'as' => 'widgets.data',
            'uses' => YandexMetricaWidgetController::class . '@getData',
        ]);
    });
});
