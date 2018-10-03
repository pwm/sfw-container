<?php
declare(strict_types=1);

namespace SFW\Container;

use Closure;
use SFW\Container\Exception\CycleDetected;
use SFW\Container\Exception\DuplicateKey;
use SFW\Container\Exception\MissingResolver;
use function array_keys;
use function array_merge;
use function array_pop;
use function implode;
use function sprintf;

class Container
{
    /** @var Closure[] */
    private $resolvers = [];
    /** @var array */
    private $cacheMe = [];
    /** @var array */
    private $cache = [];

    private const TRAVERSED = true;
    private const CACHE_ME  = true;

    public function add(string $key, Closure $resolver): void
    {
        $this->factory($key, $resolver);
        $this->cacheMe[$key] = self::CACHE_ME;
    }

    public function factory(string $key, Closure $resolver): void
    {
        if (isset($this->resolvers[$key])) {
            throw new DuplicateKey(sprintf('Cannot override resolver for key: %s', $key));
        }
        // Bind the resolver to the container instance so we can use $this->resolve() from within it
        $this->resolvers[$key] = $resolver->bindTo($this);
    }

    public function resolve(string $key, ...$resolverParams)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        if (! isset($this->resolvers[$key])) {
            throw new MissingResolver(sprintf('No resolver found for key: %s', $key));
        }

        $resolved = $this->_resolve($key, ...$resolverParams);
        if (isset($this->cacheMe[$key])) {
            $this->cache[$key] = $resolved;
        }
        return $resolved;
    }

    private function _resolve(string $key, ...$resolverParams)
    {
        // Keeping track of our path in the dependency graph during recursive calls, so we can detect cycles
        static $traversedPathStack = [];

        if (isset($traversedPathStack[$key])) {
            $cycle = array_merge(array_keys($traversedPathStack), [$key]);
            throw new CycleDetected(sprintf('Circular dependency detected: %s', implode(' -> ', $cycle)));
        }

        $traversedPathStack[$key] = self::TRAVERSED;
        $resolved = $this->resolvers[$key](...$resolverParams);
        array_pop($traversedPathStack);

        return $resolved;
    }
}
