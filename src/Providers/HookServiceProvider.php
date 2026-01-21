<?php

namespace Botble\YandexMetrica\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Supports\ServiceProvider;
use Botble\Dashboard\Events\RenderingDashboardWidgets;
use Botble\Dashboard\Supports\DashboardWidgetInstance;
use Illuminate\Support\Facades\Auth;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter('base_filter_public_head', function (?string $html): string {
            if (! setting('yandex_metrica_enabled', false)) {
                return $html;
            }

            $counterId = setting('yandex_metrica_counter_id');

            if (! $counterId) {
                return $html;
            }

            // Check if logged in users tracking is enabled
            if (Auth::check()) {
                if (! setting('yandex_metrica_track_logged_in', false)) {
                    return $html;
                }
                
                $user = Auth::user();
                $ignoreRoles = json_decode(setting('yandex_metrica_ignore_roles', '[]'), true) ?: [];
                
                if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($ignoreRoles)) {
                     return $html;
                }
            }
            
            $jsUrl = setting('yandex_metrica_js_url', 'https://mc.yandex.ru/metrika/tag.js');

            $script = <<<HTML
<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "$jsUrl", "ym");

   ym($counterId, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/$counterId" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
HTML;

            return $html . $script;
        }, 120);

        // Dashboard widget removed by user request
        // $this->app['events']->listen(RenderingDashboardWidgets::class, function () {
        //     if (! setting('yandex_metrica_enabled', false) || ! setting('yandex_metrica_access_token')) {
        //        return;
        //     }

        //     add_filter(DASHBOARD_FILTER_ADMIN_LIST, [$this, 'registerDashboardWidget'], 21, 2);
        // });
    }

    public function registerDashboardWidget(array $widgets, \Illuminate\Support\Collection $widgetSettings): array
    {
        // Check dashboard roles
        if (Auth::check()) {
             $user = Auth::user();
             $dashboardRoles = json_decode(setting('yandex_metrica_dashboard_roles', '[]'), true) ?: [];
             
             if (! empty($dashboardRoles)) {
                 if (method_exists($user, 'hasAnyRole') && ! $user->hasAnyRole($dashboardRoles)) {
                     // super admin usually bypasses, but let's stick to the list logic.
                     if (! $user->isSuperUser()) {
                         return $widgets;
                     }
                 }
             }
        }

        return (new DashboardWidgetInstance)
            ->setPermission('yandex-metrica.settings')
            ->setKey('widget_yandex_metrica_v3')
            ->setTitle(trans('plugins/yandex-metrica::yandex-metrica.widget.title'))
            ->setIcon('ti ti-chart-dots')
            ->setColor('info')
            ->setRoute(route('yandex-metrica.widgets.general'))
            ->setColumn('col-md-12')
            ->init($widgets, $widgetSettings);
    }
}
