# Changelog

All notable changes to `creem/laravel` will be documented in this file.

## v1.0.0 - 2024-01-01

### Initial Release

**Core Features:**
- Full Creem API integration (Products, Checkouts, Customers, Subscriptions)
- Multi-profile configuration support
- Inline configuration via `withConfig()`
- Laravel-native HTTP client with automatic retries
- Comprehensive error handling with specific exceptions

**Webhooks:**
- Automatic webhook route registration
- HMAC signature verification middleware
- Laravel event dispatching for all webhook types
- Built-in webhook testing command

**Developer Experience:**
- Facade for clean, readable API access
- Full PHPDoc annotations
- PSR-12 compliant code
- >70% test coverage
- Example controllers and routes
- Comprehensive documentation

**Services:**
- `ProductService`: Create, retrieve, and list products
- `CheckoutService`: Create checkout sessions
- `CustomerService`: Manage customers and generate portal links
- `SubscriptionService`: Manage subscription lifecycle

**Events:**
- `CheckoutCompleted`
- `SubscriptionCreated`
- `SubscriptionCanceled`
- `PaymentFailed`

**Commands:**
- `creem:test-webhook`: Test webhook handling locally

**Testing:**
- Unit tests for all service classes
- Feature tests for webhooks and configuration
- HTTP fake examples
- PHPUnit configuration included
