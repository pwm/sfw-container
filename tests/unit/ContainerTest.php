<?php
declare(strict_types=1);

namespace SFW\Container;

use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates(): void
    {
        self::assertInstanceOf(Container::class, new Container());
    }

    /**
     * @test
     */
    public function it_resolves_a_member(): void
    {
        $c = new Container();

        $c->add(Test::class, function () { return new Test(); });

        self::assertInstanceOf(Test::class, $c->resolve(Test::class));
    }

    /**
     * @test
     */
    public function it_resolves_a_member_from_cache(): void
    {
        $c = new Container();

        $instanceCounter = function () {
            static $count = 0;
            return new class(++$count)
            {
                private $count;
                public function __construct(int $count) { $this->count = $count; }
                public function getCount(): int { return $this->count; }
            };
        };

        $c->add('newEveryTime', $instanceCounter);
        $c->addCached('cached', $instanceCounter);

        self::assertSame(1, $c->resolve('newEveryTime')->getCount());
        self::assertSame(2, $c->resolve('newEveryTime')->getCount());
        self::assertSame(3, $c->resolve('newEveryTime')->getCount());

        self::assertSame(1, $c->resolve('cached')->getCount());
        self::assertSame(1, $c->resolve('cached')->getCount());
        self::assertSame(1, $c->resolve('cached')->getCount());
    }

    /**
     * @test
     * @expectedException \SFW\Container\Exception\DuplicateKey
     */
    public function it_throws_on_duplicate_resolver_keys(): void
    {
        $c = new Container();

        $c->add('key', function () { });
        $c->add('key', function () { });
    }

    /**
     * @test
     * @expectedException \SFW\Container\Exception\MissingResolver
     */
    public function it_throws_when_no_resolver_key_found(): void
    {
        (new Container())->resolve('missing');
    }
}

class Test {}
