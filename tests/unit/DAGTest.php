<?php
declare(strict_types=1);

namespace SFW\Container;

use PHPUnit\Framework\TestCase;

/**
 * Scenarios for testing that the container resolves dependencies if its dependency graph is acyclic
 */
class DAGTest extends TestCase
{
    /**
     * @test
     */
    public function scenario_1(): void
    {
        $c = new Container();

        $c->add(A1C1::class, function () { return new A1C1(); });

        self::assertInstanceOf(A1C1::class, $c->resolve(A1C1::class));
    }

    /**
     * @test
     */
    public function scenario_2(): void
    {
        $c = new Container();

        $c->add(A2C1::class, function () use ($c) { return new A2C1($c->resolve(A2C2::class)); });
        $c->add(A2C2::class, function () { return new A2C2(); });

        self::assertInstanceOf(A2C1::class, $c->resolve(A2C1::class));
    }

    /**
     * @test
     */
    public function scenario_3(): void
    {
        $c = new Container();

        $c->add(A3C1::class, function () use ($c) { return new A3C1($c->resolve(A3C2::class)); });
        $c->add(A3C2::class, function () use ($c) { return new A3C2($c->resolve(A3C3::class)); });
        $c->add(A3C3::class, function () { return new A3C3(); });

        self::assertInstanceOf(A3C1::class, $c->resolve(A3C1::class));
    }

    /**
     * @test
     */
    public function scenario_4(): void
    {
        $c = new Container();

        $c->add(A4C1::class, function () use ($c) { return new A4C1($c->resolve(A4C2::class), $c->resolve(A4C3::class)); });
        $c->add(A4C2::class, function () { return new A4C2(); });
        $c->add(A4C3::class, function () { return new A4C3(); });

        self::assertInstanceOf(A4C1::class, $c->resolve(A4C1::class));
    }

    /**
     * @test
     */
    public function scenario_5(): void
    {
        $c = new Container();

        $c->add(A5C1::class, function () use ($c) { return new A5C1($c->resolve(A5C2::class), $c->resolve(A5C3::class)); });
        $c->add(A5C2::class, function () use ($c) { return new A5C2($c->resolve(A5C3::class)); });
        $c->add(A5C3::class, function () { return new A5C3(); });

        self::assertInstanceOf(A5C1::class, $c->resolve(A5C1::class));
    }

    /**
     * @test
     */
    public function scenario_6(): void
    {
        $c = new Container();

        $c->add(A6C1::class, function () use ($c) { return new A6C1($c->resolve(A6C2::class), $c->resolve(A6C3::class)); });
        $c->add(A6C2::class, function () use ($c) { return new A6C2($c->resolve(A6C4::class)); });
        $c->add(A6C3::class, function () use ($c) { return new A6C3($c->resolve(A6C4::class)); });
        $c->add(A6C4::class, function () { return new A6C4(); });

        self::assertInstanceOf(A6C1::class, $c->resolve(A6C1::class));
    }

    /**
     * @test
     */
    public function scenario_7(): void
    {
        $c = new Container();

        $c->add(A7C1::class, function () use ($c) { return new A7C1($c->resolve(A7C2::class), $c->resolve(A7C3::class)); });
        $c->add(A7C2::class, function () use ($c) { return new A7C2($c->resolve(A7C3::class), $c->resolve(A7C4::class)); });
        $c->add(A7C3::class, function () use ($c) { return new A7C3($c->resolve(A7C4::class)); });
        $c->add(A7C4::class, function () { return new A7C4(); });

        self::assertInstanceOf(A7C1::class, $c->resolve(A7C1::class));
    }
}

// test data for scenario_1
class A1C1
{
}

// test data for scenario_2
class A2C1
{
    public function __construct(A2C2 $a2c2) { }
}

class A2C2
{
}

// test data for scenario_3
class A3C1
{
    public function __construct(A3C2 $a3c2) { }
}

class A3C2
{
    public function __construct(A3C3 $a3c3) { }
}

class A3C3
{
}

// test data for scenario_4
class A4C1
{
    public function __construct(A4C2 $a4c2, A4C3 $a4c3) { }
}

class A4C2
{
}

class A4C3
{
}

// test data for scenario_5
class A5C1
{
    public function __construct(A5C2 $a5c2, A5C3 $a5c3) { }
}

class A5C2
{
    public function __construct(A5C3 $a5c3) { }
}

class A5C3
{
}

// test data for scenario_6
class A6C1
{
    public function __construct(A6C2 $a6c2, A6C3 $a6c3) { }
}

class A6C2
{
    public function __construct(A6C4 $a6c4) { }
}

class A6C3
{
    public function __construct(A6C4 $a6c4) { }
}

class A6C4
{
}

// test data for scenario_7
class A7C1
{
    public function __construct(A7C2 $a7c2, A7C3 $a7c3) { }
}

class A7C2
{
    public function __construct(A7C3 $a7c3, A7C4 $a7c4) { }
}

class A7C3
{
    public function __construct(A7C4 $a7c4) { }
}

class A7C4
{
}
