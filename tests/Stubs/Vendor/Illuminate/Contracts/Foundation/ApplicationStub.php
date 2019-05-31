<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Illuminate\Contracts\Foundation;

use ArrayAccess;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) This class is implemented from a Laravel interface
 * @SuppressWarnings(PHPMD.ExcessivePublicCount) This class is implemented from a Laravel interface
 * @SuppressWarnings(PHPMD.TooManyMethods) This class is implemented from a Laravel interface
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) This class is implemented from a Laravel interface
 */
class ApplicationStub implements Application, ArrayAccess
{
    /**
     * Container bindings
     *
     * @var \Illuminate\Container\Container
     */
    private $container;

    /**
     * Tagged services.
     *
     * @var mixed[]
     */
    private $tags;

    /**
     * Create container
     */
    public function __construct()
    {
        $this->container = new Container();
    }

    /**
     * {@inheritdoc}
     */
    public function addContextualBinding($concrete, $abstract, $implementation): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function afterResolving($abstract, ?Closure $callback = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function alias($abstract, $alias): void
    {
        $this->container->alias($abstract, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function basePath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function bind($abstract, $concrete = null, $shared = null): void
    {
        $this->container->bind($abstract, $concrete, $shared ?? false);
    }

    /**
     * {@inheritdoc}
     */
    public function bindIf($abstract, $concrete = null, $shared = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function booted($callback): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function booting($callback): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapPath($path = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $bootstrappers): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function bound($abstract)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function call($callback, ?array $parameters = null, $defaultMethod = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configPath($path = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configurationIsCached()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function databasePath($path = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function detectEnvironment(Closure $callback)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function environment(...$environments)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function environmentFile()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function environmentFilePath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function environmentPath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function extend($abstract, Closure $closure): void
    {
        $this->container->extend($abstract, $closure);
    }

    /**
     * {@inheritdoc}
     */
    public function factory($abstract)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
    }

    /**
     * @param mixed $containerId
     *
     * @return mixed
     */
    public function get($containerId)
    {
        return $this->container->get($containerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedConfigPath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedPackagesPath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedRoutesPath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedServicesPath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders($provider)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ShortVariable) Parameter is inherited from interface
     */
    public function has($id): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasBeenBootstrapped()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function instance($abstract, $instance)
    {
        return $this->container->instance($abstract, $instance);
    }

    /**
     * {@inheritdoc}
     */
    public function isDownForMaintenance()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function loadDeferredProviders(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function loadEnvironmentFrom($file)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function make($abstract, ?array $parameters = null)
    {
        return $this->container->make($abstract, $parameters ?? []);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return true;
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->container->offsetGet($offset);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function register($provider, $force = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerConfiguredProviders(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerDeferredProvider($provider, $service = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resolveProvider($provider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resolved($abstract)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resolving($abstract, ?Closure $callback = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resourcePath($path = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function routesAreCached()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function runningInConsole()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function runningUnitTests()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipMiddleware()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function singleton($abstract, $concrete = null): void
    {
        $this->container->singleton($abstract, $concrete);
    }

    /**
     * {@inheritdoc}
     */
    public function storagePath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function tag($abstracts, $tags): void
    {
        $this->container->tag($abstracts, $tags);
    }

    /**
     * {@inheritdoc}
     */
    public function tagged($tag)
    {
        return $this->container->tagged($tag);
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function version()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function when($concrete)
    {
    }
}
