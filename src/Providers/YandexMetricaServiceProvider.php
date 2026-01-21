<?php

namespace Botble\YandexMetrica\Providers;

use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\PanelSections\SettingOthersPanelSection;

class YandexMetricaServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/yandex-metrica')
            ->loadAndPublishConfigurations(['general'])
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->publishAssets();

        PanelSectionManager::default()->beforeRendering(function (): void {
            PanelSectionManager::registerItem(
                SettingOthersPanelSection::class,
                fn () => PanelSectionItem::make('yandex_metrica')
                    ->setTitle(trans('plugins/yandex-metrica::yandex-metrica.settings.title'))
                    ->withIcon('ti ti-chart-dots')
                    ->withDescription(trans('plugins/yandex-metrica::yandex-metrica.settings.description'))
                    ->withPriority(170)
                    ->withRoute('yandex-metrica.settings')
            );
        });
        
        \Botble\Base\Facades\DashboardMenu::default()->beforeRetrieving(function () {
            \Botble\Base\Facades\DashboardMenu::registerItem([
                'id' => 'cms-plugins-yandex-metrica-analytics',
                'priority' => 5,
                'parent_id' => null,
                'name' => 'plugins/yandex-metrica::yandex-metrica.menu', 
                'icon' => 'ti ti-chart-line',
                'url' => route('yandex-metrica.analytics.view'),
                'permissions' => ['yandex-metrica.settings'],
            ]);
        });

        $this->app->booted(function (): void {
            $this->app->register(HookServiceProvider::class);
        });
    }
}
