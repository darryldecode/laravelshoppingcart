<?php

namespace Ozanmuyes\Cart;

use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . "/../config/cart.php" => config_path("cart.php")
        ], "config");
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/../config/cart.php", "cart");

        $this->app["cart"] = $this->app->share(function($app) {
            $storage = $app["session"];
            $events = $app["events"];
            $instanceName = "cart";
            $session_key = config("cart.session_key");

            return new Cart(
                $storage,
                $events,
                $instanceName,
                $session_key
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
