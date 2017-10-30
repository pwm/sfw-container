<?php
declare(strict_types=1);

namespace SFW\Container;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Scenarios for testing that the container detects cycles in its dependency graph
 */
class DCGTest extends TestCase
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
        $this->container->add(C1C1::class, function () { return new C1C1($this->resolve(C1C2::class)); });
        $this->container->add(C1C2::class, function () { return new C1C2($this->resolve(C1C1::class)); });

        $this->itDetectsCycleFromKey(C1C1::class, 'C1C1 -> C1C2 -> C1C1');
    }

    /**
     * @test
     */
    public function scenario2(): void
    {
        $this->container->add(C2C1::class, function () { return new C2C1($this->resolve(C2C2::class)); });
        $this->container->add(C2C2::class, function () { return new C2C2($this->resolve(C2C3::class)); });
        $this->container->add(C2C3::class, function () { return new C2C3($this->resolve(C2C1::class)); });

        $this->itDetectsCycleFromKey(C2C1::class, 'C2C1 -> C2C2 -> C2C3 -> C2C1');
    }

    /**
     * @test
     */
    public function scenario3(): void
    {
        $this->container->add(C3C1::class, function () { return new C3C1($this->resolve(C3C2::class)); });
        $this->container->add(C3C2::class, function () { return new C3C2($this->resolve(C3C3::class)); });
        $this->container->add(C3C3::class, function () { return new C3C3($this->resolve(C3C4::class)); });
        $this->container->add(C3C4::class, function () { return new C3C4($this->resolve(C3C1::class)); });

        $this->itDetectsCycleFromKey(C3C1::class, 'C3C1 -> C3C2 -> C3C3 -> C3C4 -> C3C1');
    }

    /**
     * @test
     */
    public function scenario4(): void
    {
        $this->container->add(C4C1::class, function () { return new C4C1($this->resolve(C4C2::class), $this->resolve(C4C3::class)); });
        $this->container->add(C4C2::class, function () { return new C4C2(); });
        $this->container->add(C4C3::class, function () { return new C4C3($this->resolve(C4C4::class)); });
        $this->container->add(C4C4::class, function () { return new C4C4($this->resolve(C4C5::class)); });
        $this->container->add(C4C5::class, function () { return new C4C5($this->resolve(C4C2::class), $this->resolve(C4C3::class)); });

        $this->itDetectsCycleFromKey(C4C1::class, 'C4C1 -> C4C3 -> C4C4 -> C4C5 -> C4C3');

        // a sink can be resolved even in a cyclic graph
        self::assertInstanceOf(C4C2::class, $this->container->resolve(C4C2::class));
    }

    private function itDetectsCycleFromKey(string $key, string $expectedCycle): void
    {
        try {
            $this->container->resolve($key);
        } catch (RuntimeException $e) {
            self::assertStringEndsWith($expectedCycle, str_replace('SFW\Container\\', '', $e->getMessage()));
        }
    }
}

// test data for scenario1
class C1C1 { public function __construct(C1C2 $c1c2) {} }
class C1C2 { public function __construct(C1C1 $c1c1) {} }

// test data for scenario2
class C2C1 { public function __construct(C2C2 $c2c2) {} }
class C2C2 { public function __construct(C2C3 $c2c3) {} }
class C2C3 { public function __construct(C2C1 $c2c1) {} }

// test data for scenario3
class C3C1 { public function __construct(C3C2 $c3c2) {} }
class C3C2 { public function __construct(C3C3 $c3c3) {} }
class C3C3 { public function __construct(C3C4 $c3c4) {} }
class C3C4 { public function __construct(C3C1 $c3c1) {} }

// test data for scenario4
class C4C1 { public function __construct(C4C2 $c4c2, C4C3 $c4c3) {} }
class C4C2 {}
class C4C3 { public function __construct(C4C4 $c4c4) {} }
class C4C4 { public function __construct(C4C5 $c4c5) {} }
class C4C5 { public function __construct(C4C2 $c4c2, C4C3 $c4c3) {} }
