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
    private $cache = [];
    /** @var bool[] */
    private $cacheMe = [];

    private const CACHE_ME  = true;
    private const TRAVERSED = true;

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
        $this->resolvers[$key] = $resolver;
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
        // We use a stack to keep track of the path traversed in the graph so we can detect cycles
        // We utilise static as an easy and convenient way to keep state during recursion
        static $traversedPathStack = [];

        if (isset($traversedPathStack[$key])) {
            $cycle = array_merge(array_keys($traversedPathStack), [$key]);
            throw new CycleDetected(implode(' -> ', $cycle));
        }

        $traversedPathStack[$key] = self::TRAVERSED;
        $resolved = $this->resolvers[$key]($this, ...$resolverParams);
        array_pop($traversedPathStack);

        return $resolved;
    }
}
