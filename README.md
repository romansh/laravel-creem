# LaravelCreem  Package



A Laravel package for [Creem.io](https://creem.io) payment provider. Built with Laravel-native patterns, clean architecture, and developer experience as top priorities.

[![Latest Version](https://img.shields.io/packagist/v/creem/laravel.svg)](https://packagist.org/packages/creem/laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/creem/laravel.svg)](https://packagist.org/packages/creem/laravel)
[![License](https://img.shields.io/packagist/l/creem/laravel.svg)](https://packagist.org/packages/creem/laravel)

## Features

- **Laravel-Native**: Built on `Illuminate\Http\Client` with automatic retries and timeouts
- **Multi-Profile Configuration**: Support multiple API keys and environments
- **Webhooks**: Built-in signature verification and event dispatching
- **Type-Safe**: Full PHPDoc annotations and Laravel IDE helper compatible
- **Well-Tested**: >70% test coverage with unit and feature tests
- **Event-Driven**: Laravel events for all webhook types
- **Artisan Commands**: Test webhooks locally with ease
- **PSR-12 Compliant**: Clean, readable, maintainable code

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x

## Installation

Install via Composer:

```bash
composer require creem/laravel
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=creem-config
```

Add your Creem credentials to `.env`:

```env
CREEM_API_KEY=your_api_key_here
CREEM_TEST_MODE=false
CREEM_WEBHOOK_SECRET=your_webhook_secret_here
```

## Configuration

The package supports multiple configuration profiles. Open `config/creem.php` to configure:

```php
return [
    'profiles' => [
        'default' => [
            'api_key' => env('CREEM_API_KEY'),
            'test_mode' => env('CREEM_TEST_MODE', false),
            'webhook_secret' => env('CREEM_WEBHOOK_SECRET'),
        ],

        // Add more profiles as needed
        'product_a' => [
            'api_key' => env('CREEM_PRODUCT_A_KEY'),
            'test_mode' => true,
            'webhook_secret' => env('CREEM_PRODUCT_A_WEBHOOK_SECRET'),
        ],
    ],
];
```

## Usage

### Basic Usage (Default Profile)

```php
use Romansh\LaravelCreem\Facades\Creem;

// List products
$products = Creem::products()->list();

// Find a product
$product = Creem::products()->find('prod_123');

// Create a checkout
$checkout = Creem::checkouts()->create([
    'product_id' => 'prod_123',
    'success_url' => 'https://example.com/success',
    'customer' => [
        'email' => 'user@example.com',
    ],
]);

// Redirect customer to checkout
return redirect($checkout['checkout_url']);
```

### Using Named Profiles

```php
use Romansh\LaravelCreem\Facades\Creem;

// Use the 'product_a' profile
$checkout = Creem::profile('product_a')
    ->checkouts()
    ->create([
        'product_id' => 'prod_123',
        'success_url' => 'https://example.com/success',
    ]);
```

### Using Inline Configuration

```php
use Romansh\LaravelCreem\Facades\Creem;

// Use inline configuration (does not affect global state)
$checkout = Creem::withConfig([
    'api_key' => 'custom_api_key',
    'test_mode' => true,
])->checkouts()->create([
    'product_id' => 'prod_123',
    'success_url' => 'https://example.com/success',
]);
```

## Services

### Products

```php
use Romansh\LaravelCreem\Facades\Creem;

// Create a product
$product = Creem::products()->create([
    'name' => 'Premium Plan',
    'description' => 'Monthly subscription',
    'price' => 2999, // In cents
    'currency' => 'USD',
    'billing_type' => 'recurring',
    'billing_period' => 'every-month',
]);

// Find a product
$product = Creem::products()->find('prod_123');

// List products (paginated)
$products = Creem::products()->list($page = 1, $pageSize = 20);
```

### Checkouts

```php
use Romansh\LaravelCreem\Facades\Creem;

// Create a checkout session
$checkout = Creem::checkouts()->create([
    'product_id' => 'prod_123',
    'success_url' => 'https://example.com/success',
    'customer' => [
        'email' => 'user@example.com',
        'name' => 'John Doe',
    ],
    'metadata' => [
        'user_id' => auth()->id(),
        'source' => 'web',
    ],
]);

// Redirect to checkout URL
return redirect($checkout['checkout_url']);
```

### Customers

```php
use Romansh\LaravelCreem\Facades\Creem;

// Find customer by ID
$customer = Creem::customers()->find('cust_123');

// Find customer by email
$customer = Creem::customers()->findByEmail('user@example.com');

// List customers (paginated)
$customers = Creem::customers()->list($page = 1, $pageSize = 20);

// Generate customer portal link
$portalLink = Creem::customers()->createPortalLink('cust_123');
return redirect($portalLink);
```

### Subscriptions

```php
use Romansh\LaravelCreem\Facades\Creem;

// Find a subscription
$subscription = Creem::subscriptions()->find('sub_123');

// Cancel a subscription
$subscription = Creem::subscriptions()->cancel('sub_123');

// Pause a subscription
$subscription = Creem::subscriptions()->pause('sub_123');

// Resume a paused subscription
$subscription = Creem::subscriptions()->resume('sub_123');

// Upgrade a subscription
$subscription = Creem::subscriptions()->upgrade(
    subscriptionId: 'sub_123',
    productId: 'prod_456',
    updateBehavior: 'proration-charge-immediately'
);
```

## Webhooks

### Automatic Setup

Webhook routes are automatically registered. The default endpoint is:

```
POST /creem/webhook
```

Configure the webhook URL in your Creem dashboard:

```
https://yourdomain.com/creem/webhook
```

### Webhook Events

The package dispatches Laravel events for all webhook types:

```php
use Romansh\LaravelCreem\Events\CheckoutCompleted;
use Romansh\LaravelCreem\Events\SubscriptionCreated;
use Romansh\LaravelCreem\Events\SubscriptionCanceled;
use Romansh\LaravelCreem\Events\PaymentFailed;
```

### Listening to Events

Register event listeners in `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    \Romansh\LaravelCreem\Events\CheckoutCompleted::class => [
        \App\Listeners\SendPurchaseConfirmation::class,
        \App\Listeners\ProvisionUserAccess::class,
    ],
    \Romansh\LaravelCreem\Events\SubscriptionCanceled::class => [
        \App\Listeners\RevokeUserAccess::class,
    ],
];
```

Create a listener:

```php
namespace App\Listeners;

use Romansh\LaravelCreem\Events\CheckoutCompleted;

class SendPurchaseConfirmation
{
    public function handle(CheckoutCompleted $event)
    {
        $checkout = $event->payload['data'];
        $email = $checkout['customer']['email'];
        
        // Send confirmation email
        // Mail::to($email)->send(new PurchaseConfirmation($checkout));
    }
}
```

### Custom Webhook Handling

You can also create a custom webhook controller:

```php
namespace App\Http\Controllers;

use Romansh\LaravelCreem\Http\Middleware\VerifyCreemWebhook;
use Illuminate\Http\Request;

class CustomWebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware(VerifyCreemWebhook::class);
    }

    public function handle(Request $request)
    {
        $event = $request->input('event');
        $data = $request->input('data');

        // Handle webhook...

        return response()->json(['message' => 'Processed']);
    }
}
```

### Testing Webhooks Locally

Use the built-in Artisan command to test webhooks:

```bash
# Send a test checkout.completed event
php artisan creem:test-webhook checkout.completed

# Test with a specific profile
php artisan creem:test-webhook subscription.created --profile=product_a

# Available event types
php artisan creem:test-webhook checkout.completed
php artisan creem:test-webhook subscription.created
php artisan creem:test-webhook subscription.canceled
php artisan creem:test-webhook payment.failed
```

## Error Handling

The package throws specific exceptions for different error scenarios:

```php
use Romansh\LaravelCreem\Exceptions\ApiException;
use Romansh\LaravelCreem\Exceptions\ConfigurationException;

try {
    $checkout = Creem::checkouts()->create([...]);
} catch (ApiException $e) {
    // API error (400, 403, 404, etc.)
    $statusCode = $e->getStatusCode();
    $messages = $e->getMessages();
    $traceId = $e->getTraceId(); // Include in support requests
    
    return back()->withErrors($messages);
} catch (ConfigurationException $e) {
    // Configuration error (missing profile, invalid API key, etc.)
    logger()->error($e->getMessage());
}
```

## Testing

The package includes comprehensive tests:

```bash
# Run all tests
composer test

# Run with coverage
composer test -- --coverage
```

### Using HTTP Fakes in Tests

```php
use Illuminate\Support\Facades\Http;

public function test_can_create_checkout()
{
    Http::fake([
        'test-api.creem.io/v1/checkouts' => Http::response([
            'id' => 'checkout_123',
            'checkout_url' => 'https://checkout.creem.io/123',
        ], 200),
    ]);

    $checkout = Creem::checkouts()->create([
        'product_id' => 'prod_123',
        'success_url' => 'https://example.com/success',
    ]);

    $this->assertEquals('checkout_123', $checkout['id']);
}
```

## Example Controllers

### Checkout Controller

```php
namespace App\Http\Controllers;

use Romansh\LaravelCreem\Facades\Creem;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|string',
            'email' => 'required|email',
        ]);

        $checkout = Creem::checkouts()->create([
            'product_id' => $validated['product_id'],
            'customer' => [
                'email' => $validated['email'],
            ],
            'success_url' => route('checkout.success'),
            'metadata' => [
                'user_id' => auth()->id(),
            ],
        ]);

        return redirect($checkout['checkout_url']);
    }

    public function success()
    {
        return view('checkout.success');
    }
}
```

### Subscription Management Controller

```php
namespace App\Http\Controllers;

use Romansh\LaravelCreem\Facades\Creem;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function cancel(Request $request, string $subscriptionId)
    {
        $subscription = Creem::subscriptions()->cancel($subscriptionId);

        return back()->with('success', 'Subscription canceled successfully');
    }

    public function upgrade(Request $request, string $subscriptionId)
    {
        $productId = $request->input('product_id');
        
        $subscription = Creem::subscriptions()->upgrade(
            $subscriptionId,
            $productId
        );

        return back()->with('success', 'Subscription upgraded successfully');
    }
}
```

## Advanced Configuration

### Custom HTTP Settings

Modify `config/creem.php`:

```php
'http' => [
    'timeout' => 30,
    'retry' => [
        'times' => 3,
        'sleep' => 100,
    ],
],
```

### Custom Webhook Path

```php
'webhook' => [
    'path' => '/custom/webhook/path',
    'middleware' => ['api', 'throttle:60,1'],
],
```

### Multiple Webhook Endpoints

You can set up different webhook endpoints for different profiles:

```php
// routes/web.php
use Romansh\LaravelCreem\Http\Controllers\WebhookController;
use Romansh\LaravelCreem\Http\Middleware\VerifyCreemWebhook;

Route::post('/webhooks/product-a', WebhookController::class)
    ->middleware([VerifyCreemWebhook::class.':product_a']);

Route::post('/webhooks/product-b', WebhookController::class)
    ->middleware([VerifyCreemWebhook::class.':product_b']);
```

## API Reference

### Profile Resolution Rules

1. **String (Profile Name)**: Loads named profile from config
   ```php
   Creem::profile('product_a')->checkouts()->create([...]);
   ```

2. **No Profile (Default)**: Uses 'default' profile
   ```php
   Creem::checkouts()->create([...]);
   ```

3. **Array (Inline Config)**: Uses provided configuration
   ```php
   Creem::withConfig(['api_key' => '...'])->checkouts()->create([...]);
   ```

## Troubleshooting

### Invalid Webhook Signature

Ensure your webhook secret matches between `.env` and Creem dashboard.

```bash
# Check your configuration
php artisan tinker
>>> config('creem.profiles.default.webhook_secret')
```

### API Key Issues

```php
// Verify your API key is loaded
config('creem.profiles.default.api_key')

// Check if test mode is enabled
config('creem.profiles.default.test_mode')
```

### Missing Profile Exception

```
Creem profile 'xyz' not found in configuration.
```

Solution: Add the profile to `config/creem.php` or use an existing profile name.

## Contributing

Contributions are welcome! Please ensure:

- PSR-12 code style compliance
- All tests pass
- New features include tests
- Documentation is updated

Run Laravel Pint for code formatting:

```bash
./vendor/bin/pint
```

## Security

If you discover a security vulnerability, please email security@creem.io.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

- **Documentation**: https://docs.creem.io
- **Discord**: https://discord.gg/creem
- **Email**: support@creem.io

## Credits

Built and maintained by the Creem team and contributors.
