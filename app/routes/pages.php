<?php

declare(strict_types=1);

use App\Controllers\CategoriesController;
use App\Controllers\AnalyticsController;
use App\Controllers\AuthController;
use App\Controllers\ExpensesController;
use App\Controllers\ProductsController;
use App\Controllers\QuotationsController;
use App\Controllers\SettingsController;
use App\Controllers\StoreProductsController;
use App\Controllers\StoresController;
use App\Controllers\UnitsController;
use App\Controllers\UserController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\RouteNameMiddleware;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
  $app->group('', function (Group $app) {
    $app->get('/register', AuthController::class . '::signUp');
    $app->post('/register', AuthController::class . '::store');
    $app->get('/login', AuthController::class . '::signIn');
  })->add(function ($request, $handler) {
    $response = $handler->handle($request);
    if (isset($_COOKIE['token']) && isset($_COOKIE['refresh_token']))
      $response = $response
        ->withHeader('Location', $_ENV['BASE_URL'] . '/analytics');

    return $response;
  });

  $app->group('', function (Group $app) {
    $app->get('/request/user', UserController::class . '::request');
    /**
     * Analytics Route
     */
    $app->group('/analytics', function (Group $analytics) {
      $analytics->get('', AnalyticsController::class . '::index')->setName('analytics.index');
      $analytics->get('/print', AnalyticsController::class . '::print');
    });

    /**
     * Stores Route
     */
    $app->group('/stores', function (Group $stores) {
      $stores->get('', StoresController::class . '::index')->setName('stores.index');
      $stores->get('/new', StoresController::class . '::create')->setName('stores.create');
      $stores->get('/{id}/edit', StoresController::class . '::edit')->setName('stores.edit');
      $stores->get('/{id}/view', StoresController::class . '::view')->setName('stores.view');
      $stores->post('/new', StoresController::class . '::store');
      $stores->patch('/update', StoresController::class . '::update');
      $stores->delete('/delete', StoresController::class . '::delete');

      $stores->group('/{id}/products', function (Group $storeProduct) {
        $storeProduct->get('', StoreProductsController::class . '::index');
        $storeProduct->post('/new', StoreProductsController::class . '::store');
        $storeProduct->patch('/update', StoreProductsController::class . '::update');
        $storeProduct->delete('/delete', StoreProductsController::class . '::delete');
      });
    });

    /**
     * Products Route
     */
    $app->group('/products', function (Group $products) {
      $products->get('', ProductsController::class . '::index')->setName('products.index');
      $products->get('/new', ProductsController::class . '::create')->setName('products.create');
      $products->get('/{id}/edit', ProductsController::class . '::edit')->setName('products.edit');
      $products->post('/new', ProductsController::class . '::store');
      $products->patch('/update', ProductsController::class . '::update');
      $products->delete('/delete', ProductsController::class . '::delete');

      /**
       * Categories Route
       */
      $products->group('/categories', function (Group $categories) {
        $categories->get('', CategoriesController::class . '::index')->setName('products.categories.index');
        $categories->post('/new', CategoriesController::class . '::store');
        $categories->patch('/update', CategoriesController::class . '::update');
        $categories->delete('/delete', CategoriesController::class . '::delete');
      });

      /**
       * Units Route
       */
      $products->group('/units', function (Group $units) {
        $units->get('', UnitsController::class . '::index')->setName('products.units.index');
        $units->post('/new', UnitsController::class . '::store');
        $units->patch('/update', UnitsController::class . '::update');
        $units->delete('/delete', UnitsController::class . '::delete');
      });
    });

    /**
     * Quotations Route
     */
    $app->group('/quotations', function (Group $quotations) {
      $quotations->get('', QuotationsController::class . '::index')->setName('quotations.index');
      $quotations->get('/new', QuotationsController::class . '::create')->setName('quotations.create');
      $quotations->get('/{id}/edit', QuotationsController::class . '::edit')->setName('quotations.edit');
      $quotations->get('/{id}/print', QuotationsController::class . '::print');
      $quotations->post('/new', QuotationsController::class . '::store');
      $quotations->patch('/update', QuotationsController::class . '::update');
      $quotations->delete('/delete', QuotationsController::class . '::delete');
    });

    /**
     * Expenses Route
     */
    $app->group('/expenses', function (Group $expenses) {
      $expenses->get('', ExpensesController::class . '::index')->setName('expenses.index');
    });

    /**
     * Accounts Route
     */
    $app->group('/accounts', function (Group $accounts) {
      $accounts->get('', UserController::class . '::index')->setName('accounts.index');
      $accounts->get('/new', UserController::class . '::create')->setName('accounts.create');
      $accounts->get('/{id}/edit', UserController::class . '::edit')->setName('accounts.edit');
      $accounts->post('/new', UserController::class . '::store');
      $accounts->patch('/update', UserController::class . '::update');
      $accounts->delete('/delete', UserController::class . '::delete');
    });

    /**
     * Settings Route
     */
    $app->group('/settings', function (Group $settings) {
      $settings->get('', SettingsController::class . '::index')->setName('settings.index');
    });

    /**
     * My Account Route
     */
    $app->group('/profile', function (Group $profile) {
      $profile->get('', UserController::class . '::profile')->setName('settings.my-account');
      $profile->patch('/general', UserController::class . '::updateGeneral');
      $profile->patch('/credentials', UserController::class . '::updateCredentials');
    });
  })->add(AuthMiddleware::class)->add(RouteNameMiddleware::class);
};
