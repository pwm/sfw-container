# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2018-10-12

### Added
  * The container is passed into resolvers as their first parameter. This removes the need for resolvers closing over the container or binding `$this` to the container in resolvers.

### Removed
  * Binding `$this` in resolvers to the container.

## [2.1.0] - 2018-10-03

### Added
  * Optional parameters to the `resolve()` method which it passes on to the resolver. This enables dynamic loading. 

## [2.0.0] - 2018-06-15
### Changed
  * Cacheability is now decided at creation instead of instantiation. To support this `resolveFromCache()` is removed, `add()` is now caching by default and `factory()` resolves a new instance every time.

### Added
  * Specific exception types

## [1.1.0] - 2017-10-30
### Added
  * Resolve new not cached instances by default
  * Ability to resolve cached instances

## [1.0.0] - 2017-04-27
### Added
  * Initial release
