<?php

namespace Roboroads\LighthouseSettings;

use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/lighthouse-settings.php', 'lighthouse-settings'
        );
    }
    
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->make('events')->listen(
            RegisterDirectiveNamespaces::class,
            function (): array {
                return [
                    'Roboroads\\LighthouseSettings\\Directives',
                ];
            }
        );
    
        $this->publishes([
            __DIR__.'/config/lighthouse-settings.php' => config_path('lighthouse-settings.php'),
        ]);
    }
}
