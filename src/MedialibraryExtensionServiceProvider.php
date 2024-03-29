<?php

namespace Okipa\MediaLibraryExtension;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

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
        $this->app->register(MediaLibraryServiceProvider::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
