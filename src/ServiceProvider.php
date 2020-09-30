<?php
namespace Leyden\Schoology;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @throws \Exception
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__.'/../config/schoology.php';
        $this->mergeConfigFrom($configPath, 'schoology');

        $this->app->bind('schoology', function() {
            return new Schoology();
        });

        $this->app->alias('schoology', Schoology::class);

    }

    /**
     * Check if package is running under Lumen app
     *
     * @return bool
     */
    protected function isLumen()
    {
        return Str::contains($this->app->version(), 'Lumen') === true;
    }

    public function boot()
    {
        if (!$this->isLumen()) {
            $configPath = __DIR__.'/../config/schoology.php';
            $this->publishes([$configPath => config_path('schoology.php')], 'config');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('schoology');
    }

}
