<?php
declare(strict_types=1);

namespace SFW\Container;

use PHPUnit\Framework\TestCase;
use SFW\Container\Exception\CycleDetected;

/**
 * Scenarios for testing that the container detects cycles in its dependency graph
 */
class DCGTest extends TestCase
{
    /**
     * @test
     */
    public function scenario_1(): void
    {
        $c = new Container();

        $c->add(C1C1::class, function (Container $c) { return new C1C1($c->resolve(C1C2::class)); });
        $c->add(C1C2::class, function (Container $c) { return new C1C2($c->resolve(C1C1::class)); });

        self::assertStringEndsWith('C1C1 -> C1C2 -> C1C1', self::findCycle($c, C1C1::class));
    }

    /**
     * @test
     */
    public function scenario_2(): void
    {
        $c = new Container();

        $c->add(C2C1::class, function (Container $c) { return new C2C1($c->resolve(C2C2::class)); });
        $c->add(C2C2::class, function (Container $c) { return new C2C2($c->resolve(C2C3::class)); });
        $c->add(C2C3::class, function (Container $c) { return new C2C3($c->resolve(C2C1::class)); });

        self::assertStringEndsWith('C2C1 -> C2C2 -> C2C3 -> C2C1', self::findCycle($c, C2C1::class));
    }

    /**
     * @test
     */
    public function scenario_3(): void
    {
        $c = new Container();

        $c->add(C3C1::class, function (Container $c) { return new C3C1($c->resolve(C3C2::class)); });
        $c->add(C3C2::class, function (Container $c) { return new C3C2($c->resolve(C3C3::class)); });
        $c->add(C3C3::class, function (Container $c) { return new C3C3($c->resolve(C3C4::class)); });
        $c->add(C3C4::class, function (Container $c) { return new C3C4($c->resolve(C3C1::class)); });

        self::assertStringEndsWith('C3C1 -> C3C2 -> C3C3 -> C3C4 -> C3C1', self::findCycle($c, C3C1::class));
    }

    /**
     * @test
     */
    public function scenario_4(): void
    {
        $c = new Container();

        $c->add(C4C1::class, function (Container $c) { return new C4C1($c->resolve(C4C2::class), $c->resolve(C4C3::class)); });
        $c->add(C4C2::class, function () { return new C4C2(); });
        $c->add(C4C3::class, function (Container $c) { return new C4C3($c->resolve(C4C4::class)); });
        $c->add(C4C4::class, function (Container $c) { return new C4C4($c->resolve(C4C5::class)); });
        $c->add(C4C5::class, function (Container $c) { return new C4C5($c->resolve(C4C2::class), $c->resolve(C4C3::class)); });

        self::assertStringEndsWith('C4C1 -> C4C3 -> C4C4 -> C4C5 -> C4C3', self::findCycle($c, C4C1::class));

        // An acyclic sub-graph can be resolved even in a cyclic graph
        self::assertInstanceOf(C4C2::class, $c->resolve(C4C2::class));
    }

    private static function findCycle(Container $c, string $key): string
    {
        try {
            $c->resolve($key);
        } catch (CycleDetected $e) {
            return str_replace('SFW\Container\\', '', $e->getMessage());
        }
        return 'No cycle found.';
    }
}

// test data for scenario_1
class C1C1
{
    public function __construct(C1C2 $c1c2) { }
}

class C1C2
{
    public function __construct(C1C1 $c1c1) { }
}

// test data for scenario_2
class C2C1
{
    public function __construct(C2C2 $c2c2) { }
}

class C2C2
{
    public function __construct(C2C3 $c2c3) { }
}

class C2C3
{
    public function __construct(C2C1 $c2c1) { }
}

// test data for scenario_3
class C3C1
{
    public function __construct(C3C2 $c3c2) { }
}

class C3C2
{
    public function __construct(C3C3 $c3c3) { }
}

class C3C3
{
    public function __construct(C3C4 $c3c4) { }
}

class C3C4
{
    public function __construct(C3C1 $c3c1) { }
}

// test data for scenario_4
class C4C1
{
    public function __construct(C4C2 $c4c2, C4C3 $c4c3) { }
}

class C4C2
{
}

class C4C3
{
    public function __construct(C4C4 $c4c4) { }
}

class C4C4
{
    public function __construct(C4C5 $c4c5) { }
}

class C4C5
{
    public function __construct(C4C2 $c4c2, C4C3 $c4c3) { }
}
