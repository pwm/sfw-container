# SFW Container

[![Build Status](https://travis-ci.org/pwm/sfw-container.svg?branch=master)](https://travis-ci.org/pwm/sfw-container)
[![codecov](https://codecov.io/gh/pwm/sfw-container/branch/master/graph/badge.svg)](https://codecov.io/gh/pwm/sfw-container)
[![Maintainability](https://api.codeclimate.com/v1/badges/e9df833499b7885e0f21/maintainability)](https://codeclimate.com/github/pwm/sfw-container/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/e9df833499b7885e0f21/test_coverage)](https://codeclimate.com/github/pwm/sfw-container/test_coverage)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A simple Container that ensures a cycle-free dependency graph.

## Table of Contents

* [Why](#why)
* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)
* [How it works](#how-it-works)
* [Tests](#tests)
* [Changelog](#changelog)
* [Licence](#licence)

## Why

To create a minimalistic DI container with cycle detection and cacheable instance resolution. 


## Requirements

PHP 7.1+

## Installation

    composer require pwm/sfw-container

## Usage

Basic usage:

```php
// Some classes with dependencies
class A { public function __construct(B $b, C $c) {} }
class B { public function __construct(int $x) {} }
class C { public function __construct(string $s) {} }

// Create a container
$c = new Container();

// Add dependencies to the container
// Resolving from within resolvers is easy as seen below with class A
$c->add(A::class, function (): A {
    return new A(
        $this->resolve(B::class),
        $this->resolve(C::class)
    );
});
$c->add(B::class, function (): B {
    return new B(1234);
});
$c->add(C::class, function (): C {
    return new C('foobar');
});

// Resolve them
assert($c->resolve(A::class) instanceof A);
assert($c->resolve(B::class) instanceof B);
assert($c->resolve(C::class) instanceof C);
```

Caching dependencies:

```php
// Simple class that saves a timestamp
class TS {
    /** @var int */
    private $timestamp;
    public function __construct(int $timestamp) {
        $this->timestamp = $timestamp;
    }
    public function getTimestamp(): int {
        return $this->timestamp;
    }
}

$c = new Container();

// Add our TS class both as normal and as cached
$c->add('nTS', function (): TS {
    return new TS(time());
});
$c->addCached('cTS', function (): TS {
    return new TS(time());
});

// Get an instance for both
$nTS = $c->resolve('nTS'); // instantiate
$cTS = $c->resolve('cTS'); // instantiate and cache

// Wait a sec ...
sleep(1);

// Normal gets instantiated again, hence timestamps will differ
assert($nTS->getTimestamp() !== $c->resolve('nTS')->getTimestamp());

// Cached is not instantiated again, hence timestamps will match
assert($cTS->getTimestamp() === $c->resolve('cTS')->getTimestamp());
```

Cycle detection:

```php
// X depends on Y and Y depends on X ...
class X { public function __construct(Y $y) {} }
class Y { public function __construct(X $x) {} }

$c = new Container();

$c->add(X::class, function (): X {
    return new X($this->resolve(Y::class));
});
$c->add(Y::class, function (): Y {
    return new Y($this->resolve(X::class));
});

try {
    $c->resolve(X::class);
} catch (CycleDetected $e) {
    // prints "Circular dependency detected: X -> Y -> X"
    echo $e->getMessage();
}
```

## How it works

Adding an instance to the container requires a key that is unique and a resolver function that, upon execution, returns the instance.

All resolvers are bound to the container which means that `$this` points to the container from within resolvers. This makes it possible to resolve from within resolvers using `$this->resolve()`.

Resolving means executing the resolver function to instantiate a class. This process is recursive as classes may have other classes as dependencies. If the container encounters a cycle while traversing the dependency graph it stops the resolution process. This ensures that only acyclic graphs (DAGs) are handled.

If a resolver was added via the `addCached()` method then, upon successful resolution, the instance is cached meaning that any subsequent resolution will return the same instance.

## Tests

	$ vendor/bin/phpunit
	$ composer phpcs
	$ composer phpstan
	$ composer infection

## Changelog

[Click here](changelog.md)

## Licence

[MIT](LICENSE)
