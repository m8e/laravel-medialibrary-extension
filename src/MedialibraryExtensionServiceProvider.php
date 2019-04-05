<?php

namespace Okipa\MediaLibraryExtension;

use Illuminate\Support\ServiceProvider;

class MediaLibraryExtensionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'medialibrary');
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang'),
        ], 'translations');
        $this->app->register(\Spatie\MediaLibrary\MediaLibraryServiceProvider::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
