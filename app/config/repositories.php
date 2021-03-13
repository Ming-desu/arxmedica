<?php
declare(strict_types=1);

use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\AnalyticsRepositoryInterface;
use App\Interfaces\AuthRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\QuotationRepositoryInterface;
use App\Interfaces\StoreProductRepositoryInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Interfaces\UnitRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\AccountRepository;
use App\Repositories\AnalyticsRepository;
use App\Repositories\AuthRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\StoreProductRepository;
use App\Repositories\StoreRepository;
use App\Repositories\UnitRepository;
use App\Repositories\UserRepository;
use DI\ContainerBuilder;
use function DI\autowire;

return function(ContainerBuilder $builder) {
  $builder->addDefinitions([
    UserRepositoryInterface::class => autowire(UserRepository::class),
    AuthRepositoryInterface::class => autowire(AuthRepository::class),
    CategoryRepositoryInterface::class => autowire(CategoryRepository::class),
    UnitRepositoryInterface::class => autowire(UnitRepository::class),
    ProductRepositoryInterface::class => autowire(ProductRepository::class),
    StoreRepositoryInterface::class => autowire(StoreRepository::class),
    StoreProductRepositoryInterface::class => autowire(StoreProductRepository::class),
    AnalyticsRepositoryInterface::class => autowire(AnalyticsRepository::class),
    QuotationRepositoryInterface::class => autowire(QuotationRepository::class),
    
  ]);
};