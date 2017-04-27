# SFW Container

A simple Container that ensures a cycle free dependency graph.

## Requirements

PHP 7.1+

## Installation

    composer require pwm/sfw-container

## Usage

```php
// Have some classes that optionally depend on each other
class A { public function __construct(B $b, C $c) {} }
class B { public function __construct(int $x) {} }
class C { public function __construct(string $s) {} }

// Create a container
$container = new Container();

// Add your classes to the container defining their dependencies
$container->add(A::class, function () {
    return new A(
        $this->resolve(B::class),
        $this->resolve(C::class)
    );
});
$container->add(B::class, function () {
    return new B(1234);
});
$container->add(C::class, function () {
    return new C('foobar');
});

// Resolve them as needed
$a = $container->resolve(A::class);
$b = $container->resolve(B::class);
$c = $container->resolve(C::class);
```
