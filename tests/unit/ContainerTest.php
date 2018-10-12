<?php
declare(strict_types=1);

namespace SFW\Container;

use Closure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
    public function it_resolves_an_instance(): void
    {
        $c = new Container();

        $c->add(Test::class, function (): Test { return new Test(); });

        self::assertInstanceOf(Test::class, $c->resolve(Test::class));
    }

    /**
     * @test
     */
    public function it_can_instantiate_via_a_factory(): void
    {
        $c = new Container();

        $sayRandomString = function (): string {
            return base64_encode(random_bytes(16));
        };

        $c->add('cached', $sayRandomString);
        $c->factory('newEveryTime', $sayRandomString);

        self::assertSame($c->resolve('cached'), $c->resolve('cached'));
        self::assertNotSame($c->resolve('newEveryTime'), $c->resolve('newEveryTime'));
    }

    /**
     * @test
     */
    public function it_dynamically_resolves_parametrised_resolvers(): void
    {
        $c = new Container();

        $c->add(StrategyA::class, function (): StrategyA {
            return new StrategyA();
        });

        $c->add(StrategyB::class, function (): StrategyB {
            return new StrategyB();
        });

        // Note that this has to be a factory otherwise Strategy will be bound to whatever it 1st resolves to
        $c->factory(Strategy::class, function (Container $c, string $strategy): Strategy {
            switch ($strategy) {
                case 'A':
                    return $c->resolve(StrategyA::class);
                case 'B':
                    return $c->resolve(StrategyB::class);
                default:
                    throw new RuntimeException(sprintf('No strategy found for %s', $strategy));
            }
        });

        self::assertInstanceOf(StrategyA::class, $c->resolve(Strategy::class, 'A'));
        self::assertInstanceOf(StrategyB::class, $c->resolve(Strategy::class, 'B'));
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

class Test
{
}

interface Strategy
{
}

class StrategyA implements Strategy
{
}

class StrategyB implements Strategy
{
}
