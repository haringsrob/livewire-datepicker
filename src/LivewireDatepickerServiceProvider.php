<?php

namespace Haringsrob\LivewireDatepicker;

use Haringsrob\LivewireDatepicker\Http\Livewire\DatePickerComponent;
use Haringsrob\LivewireDatepicker\Views\Calendar;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireDatepickerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'haringsrob');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'livewire-datepicker');

        Livewire::component('datepicker', DatePickerComponent::class);
        Blade::component('livewire-datepicker::calendar', Calendar::class);
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/livewire-datepicker.php', 'livewire-datepicker');

        // Register the service the package provides.
        $this->app->singleton('livewire-datepicker', function ($app) {
            return new LivewireDatepicker;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['livewire-datepicker'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/livewire-datepicker.php' => config_path('livewire-datepicker.php'),
        ], 'livewire-datepicker.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/haringsrob'),
        ], 'livewire-datepicker.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/haringsrob'),
        ], 'livewire-datepicker.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/haringsrob'),
        ], 'livewire-datepicker.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
