<?php

namespace Botble\YandexMetrica\Http\Controllers\Settings;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Setting\Http\Controllers\SettingController;
use Botble\YandexMetrica\Forms\YandexMetricaSettingForm;
use Botble\YandexMetrica\Http\Requests\Settings\YandexMetricaSettingRequest;
use Botble\YandexMetrica\Services\YandexMetricaClient;
use Illuminate\Http\Request;
use Botble\Setting\Facades\Setting;

class YandexMetricaSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/yandex-metrica::yandex-metrica.settings.title'));

        return YandexMetricaSettingForm::create()->renderForm();
    }

    public function update(YandexMetricaSettingRequest $request, YandexMetricaClient $client): BaseHttpResponse
    {
        // Check if Auth Code is provided
        if ($code = $request->input('yandex_metrica_auth_code')) {
            try {
                $accessToken = $client->getAccessToken($code);
                Setting::set(['yandex_metrica_access_token' => $accessToken])->save();
            } catch (\Exception $e) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage(trans('plugins/yandex-metrica::yandex-metrica.settings.error_msg', ['msg' => $e->getMessage()]));
            }
        }

        $validated = $request->validated();

        // Convert array fields to JSON for storage
        $validated['yandex_metrica_ignore_roles'] = json_encode($request->input('yandex_metrica_ignore_roles', []));
        $validated['yandex_metrica_dashboard_roles'] = json_encode($request->input('yandex_metrica_dashboard_roles', []));

        return $this->performUpdate($validated);
    }
}
