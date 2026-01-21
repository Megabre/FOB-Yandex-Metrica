<?php

namespace Botble\YandexMetrica\Forms;

use Botble\Base\Forms\FieldOptions\MultiChecklistFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;
use Botble\YandexMetrica\Http\Requests\Settings\YandexMetricaSettingRequest;
use Botble\YandexMetrica\Services\YandexMetricaClient;
use Botble\ACL\Models\Role;

class YandexMetricaSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $isAuthenticated = (bool) setting('yandex_metrica_access_token');
        $counters = [];

        if ($isAuthenticated) {
            try {
                $client = new YandexMetricaClient();
                $countersRaw = $client->getCounters(setting('yandex_metrica_access_token'));
                foreach ($countersRaw as $counter) {
                    $counters[$counter['id']] = $counter['site'] . ' (' . $counter['id'] . ')';
                }
            } catch (\Exception $e) {
                // Token issues
            }
        }

        $roles = Role::query()->pluck('name', 'id')->all();

        $this
            ->setSectionTitle(trans('plugins/yandex-metrica::yandex-metrica.settings.title'))
            ->setSectionDescription(trans('plugins/yandex-metrica::yandex-metrica.settings.description'))
            ->setValidatorClass(YandexMetricaSettingRequest::class)
            ->add(
                'yandex_metrica_enabled',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/yandex-metrica::yandex-metrica.settings.enabled'))
                    ->value(setting('yandex_metrica_enabled', false))
            );

        if ($isAuthenticated && $counterId = setting('yandex_metrica_counter_id')) {
            $statsHtml = '';
            try {
                $client = new YandexMetricaClient();
                $data = $client->getTrafficSummary(setting('yandex_metrica_access_token'), $counterId);
                
                $totals = [
                    'visits' => 0,
                    'visitors' => 0,
                    'pageviews' => 0,
                ];

                if (isset($data['totals'])) {
                     $totals['visits'] = array_sum($data['totals'][0] ?? []);
                     $totals['visitors'] = array_sum($data['totals'][1] ?? []);
                     $totals['pageviews'] = array_sum($data['totals'][2] ?? []);
                } elseif (isset($data['data'][0]['metrics'])) {
                     $totals['visits'] = array_sum($data['data'][0]['metrics'][0] ?? []);
                     $totals['visitors'] = array_sum($data['data'][0]['metrics'][1] ?? []);
                     $totals['pageviews'] = array_sum($data['data'][0]['metrics'][2] ?? []);
                }
                
                $statsHtml = view('plugins/yandex-metrica::widgets.general', ['totals' => $totals, 'error' => null])->render();
            } catch (\Exception $e) {
                $statsHtml = '<div class="alert alert-danger">Stats Error: ' . $e->getMessage() . '</div>';
            }

            $this->add('yandex_stats_view', 'html', [
                'html' => '<div class="mb-4"><h3>' . trans('plugins/yandex-metrica::yandex-metrica.widget.title') . '</h3>' . $statsHtml . '</div>'
            ]);
        }

        if (! $isAuthenticated) {
            $clientId = YandexMetricaClient::CLIENT_ID;
            $authUrl = "https://oauth.yandex.com/authorize?response_type=code&client_id={$clientId}&display=popup";

            $this->add(
                'connect_btn',
                'html',
                [
                    'html' => sprintf(
                        '<div class="mb-3">
                            <a href="%s" target="_blank" onclick="window.open(this.href, \'yandex_auth\', \'width=600,height=500\'); return false;" class="btn btn-info">
                                <i class="ti ti-brand-yandex"></i> %s
                            </a>
                            <p class="text-muted small mt-1">%s</p>
                        </div>',
                        $authUrl,
                        trans('plugins/yandex-metrica::yandex-metrica.settings.connect'),
                        trans('plugins/yandex-metrica::yandex-metrica.settings.click_to_get_code')
                    )
                ]
            );

            $this->add(
                'yandex_metrica_auth_code',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/yandex-metrica::yandex-metrica.settings.auth_code'))
                    ->placeholder(trans('plugins/yandex-metrica::yandex-metrica.settings.enter_auth_code'))
                    ->helperText(trans('plugins/yandex-metrica::yandex-metrica.settings.auth_code_helper'))
            );
        } else {
             $this->add(
                'yandex_metrica_counter_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/yandex-metrica::yandex-metrica.settings.select_counter'))
                    ->choices($counters)
                    ->selected(setting('yandex_metrica_counter_id'))
                    ->searchable()
            )
            ->add(
                'yandex_metrica_track_logged_in',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/yandex-metrica::yandex-metrica.settings.track_logged_in'))
                    ->value(setting('yandex_metrica_track_logged_in', false))
                    ->helperText(trans('plugins/yandex-metrica::yandex-metrica.settings.track_logged_in_helper'))
            )
            ->add(
                'yandex_metrica_ignore_roles',
                MultiCheckListField::class,
                MultiChecklistFieldOption::make()
                    ->label(trans('plugins/yandex-metrica::yandex-metrica.settings.ignore_roles'))
                    ->choices($roles)
                    ->selected(json_decode(setting('yandex_metrica_ignore_roles', '[]'), true) ?: [])
                    ->helperText(trans('plugins/yandex-metrica::yandex-metrica.settings.ignore_roles_helper'))
            )
             ->add(
                'yandex_metrica_dashboard_roles',
                MultiCheckListField::class,
                MultiChecklistFieldOption::make()
                    ->label(trans('plugins/yandex-metrica::yandex-metrica.settings.dashboard_roles'))
                    ->choices($roles)
                    ->selected(json_decode(setting('yandex_metrica_dashboard_roles', '[]'), true) ?: [])
                    ->helperText(trans('plugins/yandex-metrica::yandex-metrica.settings.dashboard_roles_helper'))
            )
             ->add(
                'yandex_metrica_js_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/yandex-metrica::yandex-metrica.settings.js_url'))
                    ->value(setting('yandex_metrica_js_url', 'https://mc.yandex.ru/metrika/tag.js'))
                    ->placeholder('https://mc.yandex.ru/metrika/tag.js')
            );
        }
    }
}
