<?php
declare(strict_types = 1);

namespace SFW\Container;

use Closure;
use RuntimeException;

class Container
{
    /** @var Closure[] */
    private $resolvers = [];

    /** @var array */
    private $cache = [];

    /** @var array */
    private $traversedPathStack = [];

    public function add(string $key, Closure $resolver): void
    {
        if (array_key_exists($key, $this->resolvers)) {
            throw new RuntimeException(sprintf('Cannot override resolver for key: %s', $key));
        }
        $this->resolvers[$key] = $resolver->bindTo($this);
    }

    public function resolve(string $key)
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        if (! array_key_exists($key, $this->resolvers)) {
            throw new RuntimeException(sprintf('No resolver found for key: %s', $key));
        }

        if (isset($this->traversedPathStack[$key])) {
            $cycle = array_merge(array_keys($this->traversedPathStack), [$key]);
            throw new RuntimeException(sprintf('Circular dependency detected: %s', implode(' -> ', $cycle)));
        }

        $this->traversedPathStack[$key] = true;
        $this->cache[$key] = $this->resolvers[$key]();
        array_pop($this->traversedPathStack);

        return $this->cache[$key];
    }
}
