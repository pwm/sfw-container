<?php
declare(strict_types=1);

namespace SFW\Container;

use Closure;
use RuntimeException;

class Container
{
    /** @var Closure[] */
    private $resolvers = [];

    /** @var array */
    private $cache = [];

    public function add(string $key, Closure $resolver): void
    {
        if (array_key_exists($key, $this->resolvers)) {
            throw new RuntimeException(sprintf('Cannot override resolver for key: %s', $key));
        }
        $this->resolvers[$key] = $resolver->bindTo($this);
    }

    public function resolve(string $key)
    {
        if (! array_key_exists($key, $this->resolvers)) {
            throw new RuntimeException(sprintf('No resolver found for key: %s', $key));
        }

        return $this->_resolve($key);
    }

    //@todo: addCached would be better
    public function resolveFromCache(string $key)
    {
        return array_key_exists($key, $this->cache)
            ? $this->cache[$key]
            : $this->resolve($key);
    }

    private function _resolve(string $key)
    {
        static $traversedPathStack = [];

        if (isset($traversedPathStack[$key])) {
            $cycle = array_merge(array_keys($traversedPathStack), [$key]);
            throw new RuntimeException(sprintf('Circular dependency detected: %s', implode(' -> ', $cycle)));
        }

        $traversedPathStack[$key] = true;
        $resolved = $this->resolvers[$key]();
        if (! array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $resolved;
        }
        array_pop($traversedPathStack);

        return $resolved;
    }
}
