# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-03-31

### Changed
- Complete rewrite targeting PHP 8.2+ and Laravel 11+
- Replaced monolithic trait with SOLID-compliant interface-driven architecture
- All classes now depend on contracts (interfaces) instead of concrete implementations
- Configuration is now published via standard Laravel config publishing

### Added
- `JsonRequestDetectorInterface` and implementation for configurable request detection
- `StatusCodeResolverInterface` and implementation with extensible exception-to-status-code mapping
- `ExceptionPayloadBuilderInterface` and implementation with debug/production payload modes
- `ExceptionRendererInterface` and implementation for JSON response rendering
- `ExceptionHandlerRegistrarInterface` and implementation for Laravel 11 `withExceptions()` integration
- `ExceptionResponseServiceProvider` with full container bindings
- Configurable JSON request URL patterns
- Custom exception-to-status-code mapping via configuration
- Swappable contract implementations via config or service container
- PHPStan level 10 compliance with Larastan
- Pest PHP test suite with unit and feature tests

### Removed
- `ApiExceptionResponse` trait
- `ApiExceptionProvider` service provider
- `helper.php` helper file
- Legacy `src/config/exception.php` configuration
