<?php

namespace KamrulHaque\ActionCrudHelper;

use Illuminate\Support\ServiceProvider;
use KamrulHaque\ActionCrudHelper\Console\Commands\MakeAction;
use KamrulHaque\ActionCrudHelper\Console\Commands\MakeCrud;

class ActionCrudHelperServiceProvider extends ServiceProvider
{
    protected string $name = 'action-crud-helper';

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs'),
            ], "{$this->name}-stubs");

            $this->publishes([
                __DIR__.'/../stubs/tests/TestCase.php' => base_path('tests/TestCase.php'),
                __DIR__.'/../stubs/tests/Pest.php' => base_path('tests/Pest.php'),
                __DIR__.'/../stubs/tests/Feature' => base_path('tests/Feature'),
            ], "{$this->name}-tests");

            $this->commands([
                MakeAction::class,
                MakeCrud::class,
            ]);
        }
    }
}
