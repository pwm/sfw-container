# SFW Container

[![Build Status](https://travis-ci.org/pwm/sfw-container.svg?branch=master)](https://travis-ci.org/pwm/sfw-container)
[![codecov](https://codecov.io/gh/pwm/sfw-container/branch/master/graph/badge.svg)](https://codecov.io/gh/pwm/sfw-container)
[![Maintainability](https://api.codeclimate.com/v1/badges/e9df833499b7885e0f21/maintainability)](https://codeclimate.com/github/pwm/sfw-container/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/e9df833499b7885e0f21/test_coverage)](https://codeclimate.com/github/pwm/sfw-container/test_coverage)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A minimalistic DI container with cycle detection, cacheable instance resolution and dynamic loading.

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

There are many DI containers out there. My design goals were:

- Minimalism
- Cycle detection in the dependency graph
- Instance caching by default
- Dynamic loading

## Requirements

PHP 7.1+

## Installation

    composer require pwm/sfw-container

## Usage

Basic usage:

```php
// Have some classes, some of them depend on others
class A { public function __construct(int $x) {} }
class B { public function __construct(string $s) {} }
class C { public function __construct(A $a, B $b) {} }

// Create a container
$c = new Container();

// Add resolver functions to the container that will instantiate your classes
$c->add(A::class, function (): A {
    return new A(1);
});
$c->add(B::class, function (): B {
    return new B('x');
});

// Resolving from within resolvers is easy as the Container is passed to the functions as the 1st parameter
$c->add(C::class, function (Container $c): C {
    return new C(
        $c->resolve(A::class),
        $c->resolve(B::class)
    );
});

// Resolve your classes
assert($c->resolve(A::class) instanceof A);
assert($c->resolve(B::class) instanceof B);
assert($c->resolve(C::class) instanceof C);
```

Cycle detection:

```php
// X depends on Y and Y depends on X ...
class X { public function __construct(Y $y) {} }
class Y { public function __construct(X $x) {} }

$c = new Container();

$c->add(X::class, function (Container $c): X {
    return new X($c->resolve(Y::class));
});
$c->add(Y::class, function (Container $c): Y {
    return new Y($c->resolve(X::class));
});

try {
    $c->resolve(X::class);
} catch (CycleDetected $e) {
    assert('X -> Y -> X' === $e->getMessage());
}
```

Factory vs. cached instances:

```php
// Simple class that saves a timestamp
class TS {
    private $timestamp;
    public function __construct(int $timestamp) {
        $this->timestamp = $timestamp;
    }
    public function getTimestamp(): int {
        return $this->timestamp;
    }
}

$c = new Container();

// Add our TS class both cached and as a factory (using different keys)
$c->add('cachedTS', function (): TS {
    return new TS(time());
});
$c->factory('factoryTS', function (): TS {
    return new TS(time());
});

// Get an instance for both
$cTS = $c->resolve('cachedTS'); // instantiate and cache
$fTS = $c->resolve('factoryTS'); // just instantiate

// Wait a sec ...
sleep(1);

// For the cached ones timestamps will match
assert($cTS->getTimestamp() === $c->resolve('cachedTS')->getTimestamp());

// Factory instantiates again, hence timestamps will differ
assert($fTS->getTimestamp() !== $c->resolve('factoryTS')->getTimestamp());
```

Dynamic loading:

```php
interface Reader {
    public function read(): string;
}
class XmlReader implements Reader {
    public function read(): string {
        return 'Reading Xml...';
    }
}
class JsonReader implements Reader {
    public function read(): string {
        return 'Reading Json...';
    }
}

$c = new Container();

// Reader's resolver has to be added via factory otherwise
// it will be bound to whatever it first resolves to
$c->factory(Reader::class, function (Container $c, string $strategy): Reader {
    switch ($strategy) {
        case 'xml':
            return new XmlReader();
        case 'json':
            return new JsonReader();
        default:
            throw new RuntimeException(sprintf('No reader found for %s', $strategy));
    }
});

assert('Reading Xml...' === $c->resolve(Reader::class, 'xml')-> read());
assert('Reading Json...' === $c->resolve(Reader::class, 'json')-> read());

try {
    $c->resolve(Reader::class, 'csv');
} catch (RuntimeException $e) {
    assert('No reader found for csv' === $e->getMessage());
}
```

## How it works

A container is just a map where keys are ids of classes defined by us and values are functions, called resolvers, that know how to instantiate these classes. Resolving a class simply means executing its resolver which will return an instance of the class. It's good practice to use full class names namepsace included as ids but we are free to use any string we like.

All resolvers get the container itself as their first argument which makes it easy to resolve from within a resolver, making the resolution process recursive. This is very useful as classes may have other classes as dependencies.

While we are free to build any dependency graph we like, the resolution process will stop when it encounters a cycle while traversing it. This ensures that only acyclic graphs (ie. DAGs) are handled and saves us from blowing the callstack.

The container caches resolved instances by default meaning that any subsequent resolution will return the same instance. This is good and usually what we want. However, if a resolver was added via the `factory()` method then every resolution will return a new instance of that class.

We can also pass extra parameters to resolvers. This, combined with `factory()`, makes it possible to implement dynamic loading, ie. to instantiate an interface in various different ways. An example of this would be what is known as the Strategy pattern, ie. using runtime information, eg. a user supplied CLI parameter, to instantiate the appropriate implementation of some interface.

## Tests

	$ vendor/bin/phpunit
	$ composer phpcs
	$ composer phpstan
	$ composer infection

## Changelog

[Click here](changelog.md)

## Licence

[MIT](LICENSE)
