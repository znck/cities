<?php
namespace Znck\Cities;

use Illuminate\Support\ServiceProvider;

class CitiesServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__).'/config/cities.php' => config_path('cities.php'),
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/config/cities.php', 'cities');
        $this->app->singleton('command.cities.update', UpdateCitiesCommand::class);
        $this->app->singleton(
            'translator.cities',
            function () {
                $locale = $this->app['config']['app.locale'];

                $loader = new FileLoader($this->app['files'], dirname(__DIR__).'/data');

                $trans = new Translator($loader, $locale);

                return $trans;
            }
        );
        $this->commands('command.cities.update');
    }

    public function provides()
    {
        return ['translator.cities', 'command.cities.update'];
    }
}
