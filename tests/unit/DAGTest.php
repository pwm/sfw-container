<?php
declare(strict_types=1);

namespace SFW\Container;

use PHPUnit\Framework\TestCase;

/**
 * Scenarios for testing that the container resolves dependencies if its dependency graph is acyclic
 */
class DAGTest extends TestCase
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
    public function scenario1(): void
    {
        $this->container->add(A1C1::class, function () { return new A1C1(); });

        self::assertInstanceOf(A1C1::class, $this->container->resolve(A1C1::class));
    }

    /**
     * @test
     */
    public function scenario2(): void
    {
        $this->container->add(A2C1::class, function () { return new A2C1($this->resolve(A2C2::class)); });
        $this->container->add(A2C2::class, function () { return new A2C2(); });

        self::assertInstanceOf(A2C1::class, $this->container->resolve(A2C1::class));
    }

    /**
     * @test
     */
    public function scenario3(): void
    {
        $this->container->add(A3C1::class, function () { return new A3C1($this->resolve(A3C2::class)); });
        $this->container->add(A3C2::class, function () { return new A3C2($this->resolve(A3C3::class)); });
        $this->container->add(A3C3::class, function () { return new A3C3(); });

        self::assertInstanceOf(A3C1::class, $this->container->resolve(A3C1::class));
    }

    /**
     * @test
     */
    public function scenario4(): void
    {
        $this->container->add(A4C1::class, function () { return new A4C1($this->resolve(A4C2::class), $this->resolve(A4C3::class)); });
        $this->container->add(A4C2::class, function () { return new A4C2(); });
        $this->container->add(A4C3::class, function () { return new A4C3(); });

        self::assertInstanceOf(A4C1::class, $this->container->resolve(A4C1::class));
    }

    /**
     * @test
     */
    public function scenario5(): void
    {
        $this->container->add(A5C1::class, function () { return new A5C1($this->resolve(A5C2::class), $this->resolve(A5C3::class)); });
        $this->container->add(A5C2::class, function () { return new A5C2($this->resolve(A5C3::class)); });
        $this->container->add(A5C3::class, function () { return new A5C3(); });

        self::assertInstanceOf(A5C1::class, $this->container->resolve(A5C1::class));
    }

    /**
     * @test
     */
    public function scenario6(): void
    {
        $this->container->add(A6C1::class, function () { return new A6C1($this->resolve(A6C2::class), $this->resolve(A6C3::class)); });
        $this->container->add(A6C2::class, function () { return new A6C2($this->resolve(A6C4::class)); });
        $this->container->add(A6C3::class, function () { return new A6C3($this->resolve(A6C4::class)); });
        $this->container->add(A6C4::class, function () { return new A6C4(); });

        self::assertInstanceOf(A6C1::class, $this->container->resolve(A6C1::class));
    }

    /**
     * @test
     */
    public function scenario7(): void
    {
        $this->container->add(A7C1::class, function () { return new A7C1($this->resolve(A7C2::class), $this->resolve(A7C3::class)); });
        $this->container->add(A7C2::class, function () { return new A7C2($this->resolve(A7C3::class), $this->resolve(A7C4::class)); });
        $this->container->add(A7C3::class, function () { return new A7C3($this->resolve(A7C4::class)); });
        $this->container->add(A7C4::class, function () { return new A7C4(); });

        self::assertInstanceOf(A7C1::class, $this->container->resolve(A7C1::class));
    }
}

// test data for scenario1
class A1C1 {}

// test data for scenario2
class A2C1 { public function __construct(A2C2 $a2c2) {} }
class A2C2 {}

// test data for scenario3
class A3C1 { public function __construct(A3C2 $a3c2) {} }
class A3C2 { public function __construct(A3C3 $a3c3) {} }
class A3C3 {}

// test data for scenario4
class A4C1 { public function __construct(A4C2 $a4c2, A4C3 $a4c3) {} }
class A4C2 {}
class A4C3 {}

// test data for scenario5
class A5C1 { public function __construct(A5C2 $a5c2, A5C3 $a5c3) {} }
class A5C2 { public function __construct(A5C3 $a5c3) {} }
class A5C3 {}

// test data for scenario6
class A6C1 { public function __construct(A6C2 $a6c2, A6C3 $a6c3) {} }
class A6C2 { public function __construct(A6C4 $a6c4) {} }
class A6C3 { public function __construct(A6C4 $a6c4) {} }
class A6C4 {}

// test data for scenario7
class A7C1 { public function __construct(A7C2 $a7c2, A7C3 $a7c3) {} }
class A7C2 { public function __construct(A7C3 $a7c3, A7C4 $a7c4) {} }
class A7C3 { public function __construct(A7C4 $a7c4) {} }
class A7C4 {}
