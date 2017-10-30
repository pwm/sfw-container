<?php
declare(strict_types=1);

namespace SFW\Container;

use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /** @var Container */
    private $container;

    public function setUp(): void
    {
        $this->container = new Container();
    }

    /**
     * @test
     */
    public function it_resolves_a_class_instance(): void
    {
        $this->container->add(Test::class, function () { return new Test(); });
        self::assertInstanceOf(Test::class, $this->container->resolve(Test::class));
    }

    /**
     * @test
     */
    public function it_can_resolve_from_cache_or_create_a_new_instance(): void
    {
        $this->container->add('cacheTest', function () {
            static $counter = 0;
            return new class(++$counter) {
                private $counter;
                public function __construct(int $counter) { $this->counter = $counter; }
                public function getCounter(): int { return $this->counter; }
            };
        });

        self::assertSame(1, $this->container->resolve('cacheTest')->getCounter());
        self::assertSame(2, $this->container->resolve('cacheTest')->getCounter());
        self::assertSame(3, $this->container->resolve('cacheTest')->getCounter());
        self::assertSame(1, $this->container->resolveFromCache('cacheTest')->getCounter());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp 'Cannot override resolver for key: .*'
     */
    public function it_throws_upon_overriding_a_resolver_key(): void
    {
        $this->container->add('key', function () { return new class {}; });
        $this->container->add('key', function () { return new class {}; });
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No resolver found for key: foobar
     */
    public function it_throws_when_no_resolver_key_found(): void
    {
        $this->container->resolve('foobar');
    }
}

class Test {}
