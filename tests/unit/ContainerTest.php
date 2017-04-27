<?php
declare(strict_types = 1);

namespace SFW\Container;

use PHPUnit\Framework\TestCase;

/**
 * @group container
 */
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
    public function itResolves(): void
    {
        $this->container->add(Test::class, function () { return new Test(); });
        static::assertInstanceOf(Test::class, $this->container->resolve(Test::class));
    }

    /**
     * @test
     */
    public function itResolvesFromCache(): void
    {
        $this->container->add(CounterTest::class, function () {
            static $counter = 0;
            return new CounterTest(++$counter);
        });

        // object was resolved => counter increments
        static::assertSame(1, $this->container->resolve(CounterTest::class)->getCounter());

        // object was served from the container cache => counter does not increment
        static::assertSame(1, $this->container->resolve(CounterTest::class)->getCounter());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp 'Cannot override resolver for key: .*'
     */
    public function itThrowsWhenOverridingResolverKey(): void
    {
        $this->container->add(Foo1::class, function () { return new Foo1(); });
        $this->container->add(Foo1::class, function () { return new Foo2(); });
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No resolver found for key: foobar
     */
    public function itThrowsWhenNoResolverKeyFound(): void
    {
        $this->container->resolve('foobar');
    }
}

// test data for itResolves
class Test {}

// test data for itResolvesFromCache
class CounterTest {
    private $counter = 0;
    public function __construct(int $counter) { $this->counter += $counter; }
    public function getCounter(): int { return $this->counter; }
}

// test data for itThrowsWhenOverridingResolverKey
class Foo1 {}
class Foo2 {}
