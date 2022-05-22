<?php

namespace Haringsrob\LivewireDatepicker;

use Haringsrob\LivewireDatepicker\Http\Livewire\DatePickerComponent;
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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'livewire-datepicker');

        Livewire::component('datepicker', DatePickerComponent::class);

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
        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/haringsrob'),
        ], 'livewire-datepicker.views');
    }
}
