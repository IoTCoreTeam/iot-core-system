<?php

namespace Modules\MapModule\Providers;

use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class MapModuleServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'MapModule';

    protected string $nameLower = 'mapmodule';

    public function boot(): void
    {
        // Boot methods
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
