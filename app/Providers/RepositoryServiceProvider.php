<?php

namespace App\Providers;

use App\Repository\Eloquent\ProductRepository;
use App\Repository\ProductRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register()
  {
    $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
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
