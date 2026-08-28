<?php

namespace Privateer\Moments;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Livewire\Livewire;
use Privateer\Moments\Console\Commands\DeleteOrphanImagesCommand;
use Privateer\Moments\Console\Commands\GenerateAltTextCommand;
use Privateer\Moments\Console\Commands\GlideKeyCommand;
use Privateer\Moments\Console\Commands\InstallCommand;
use Privateer\Moments\Livewire\CreateMoment;
use Privateer\Moments\Livewire\EditMoment;
use Privateer\Moments\Markdown\HashtagMarkdownRenderer;
use Privateer\Moments\Policies\MomentPolicy;
use Privateer\Moments\Support\Moments as MomentsSupport;

class MomentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/moments.php', 'moments');

        // Building the CommonMark environment is expensive, and every rendered
        // moment on a timeline or feed page resolves this renderer.
        $this->app->singleton(HashtagMarkdownRenderer::class);
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerLivewire();
        $this->registerPolicies();
        $this->registerMiddleware();
        $this->registerRoutes();
        $this->registerSchedule();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'moments');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/moments.php' => config_path('moments.php'),
        ], 'moments-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'moments-migrations');

        $this->publishes([
            __DIR__.'/../resources/js/moments.js' => resource_path('vendor/moments/moments.js'),
        ], 'moments-assets');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/moments'),
        ], 'moments-views');

        $this->publishes([
            __DIR__.'/../openapi.yaml' => base_path('openapi.yaml'),
        ], 'moments-openapi');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            DeleteOrphanImagesCommand::class,
            GenerateAltTextCommand::class,
            GlideKeyCommand::class,
            InstallCommand::class,
        ]);
    }

    protected function registerLivewire(): void
    {
        Livewire::component('create-moment', CreateMoment::class);
        Livewire::component('edit-moment', EditMoment::class);
    }

    protected function registerPolicies(): void
    {
        Gate::policy(MomentsSupport::momentModel(), MomentPolicy::class);
    }

    protected function registerMiddleware(): void
    {
        $this->app->make(Router::class)->aliasMiddleware('abilities', CheckAbilities::class);
    }

    protected function registerRoutes(): void
    {
        Route::model('moment', MomentsSupport::momentModel());

        if (config('moments.register_web_routes', true)) {
            Route::middleware(config('moments.web_middleware', ['web']))
                ->prefix(MomentsSupport::routePrefix())
                ->as(MomentsSupport::routeNamePrefix())
                ->group(__DIR__.'/../routes/web.php');
        }

        if (config('moments.register_api_routes', true)) {
            Route::middleware(config('moments.api_middleware', ['api', 'auth:sanctum']))
                ->prefix(MomentsSupport::apiPrefix())
                ->as(MomentsSupport::routeNamePrefix())
                ->group(__DIR__.'/../routes/api.php');
        }
    }

    protected function registerSchedule(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('moments:delete-orphan-images')->everyFiveMinutes();
        });
    }
}
