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
        $configFile = __DIR__ . "/../config/cart.php";

        $this->mergeConfigFrom($configFile, "cart");

        $this->publishes([
            $configFile => config_path("cart.php")
        ], "config");

        $this->publishes([
            __DIR__ . "/Listeners/CartCreatedListener.php" => app_path("Listeners/CartCreatedListener.php"),
            __DIR__ . "/Listeners/ItemsAddingListener.php" => app_path("Listeners/ItemsAddingListener.php"),
            __DIR__ . "/Listeners/ItemsAddedListener.php" => app_path("Listeners/ItemsAddedListener.php"),
            __DIR__ . "/Listeners/ItemsUpdatingListener.php" => app_path("Listeners/ItemsUpdatingListener.php"),
            __DIR__ . "/Listeners/ItemsUpdatedListener.php" => app_path("Listeners/ItemsUpdatedListener.php"),
            __DIR__ . "/Listeners/ItemsRemovingListener.php" => app_path("Listeners/ItemsRemovingListener.php"),
            __DIR__ . "/Listeners/ItemsRemovedListener.php" => app_path("Listeners/ItemsRemovedListener.php"),
            __DIR__ . "/Listeners/CartClearingListener.php" => app_path("Listeners/CartClearingListener.php"),
            __DIR__ . "/Listeners/CartClearedListener.php" => app_path("Listeners/CartClearedListener.php"),
        ], "listeners");
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app["cart"] = $this->app->share(function($app) {
            $storage = $app["session"];
            $instanceName = "cart";
            $session_key = config("cart.session_key");

            return new Cart(
                $storage,
                $instanceName,
                $session_key
            );
        });
    }
}
