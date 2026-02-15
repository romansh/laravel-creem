# LaravelCreem Package

A Laravel package for [Creem.io](https://creem.io) payment provider. Built with Laravel-native patterns, clean architecture, and developer experience as top priorities.

[![Latest Version](https://img.shields.io/packagist/v/creem/laravel.svg)](https://packagist.org/packages/creem/laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/creem/laravel.svg)](https://packagist.org/packages/creem/laravel)
[![License](https://img.shields.io/packagist/l/creem/laravel.svg)](https://packagist.org/packages/creem/laravel)

## Features

- **Laravel-Native**: Built on `Illuminate\Http\Client` with automatic retries and timeouts
- **Multi-Profile Configuration**: Support multiple API keys and environments
- **Complete API Coverage**: Products, Checkouts, Customers, Subscriptions, Transactions, Licenses, and Discounts
- **Webhooks**: Built-in signature verification and event dispatching
- **Type-Safe**: Full PHPDoc annotations and Laravel IDE helper compatible
- **Well-Tested**: Comprehensive unit and feature tests
- **Event-Driven**: Laravel events for all webhook types
- **Artisan Commands**: Test webhooks locally with ease
- **PSR-12 Compliant**: Clean, readable, maintainable code

## Requirements

- PHP 8.1 or higher
- Laravel 10.x, 11.x, 12.x

## Installation

Install via Composer:

```bash
composer require romansh/laravel-creem
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

// Find a checkout session
$checkout = Creem::checkouts()->find('chk_123');

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

// List subscriptions
$subscriptions = Creem::subscriptions()->list($page = 1, $limit = 20);

// Cancel a subscription
$subscription = Creem::subscriptions()->cancel('sub_123');

// Pause a subscription
$subscription = Creem::subscriptions()->pause('sub_123');

// Resume a paused subscription
$subscription = Creem::subscriptions()->resume('sub_123');

// Upgrade/change subscription to a different product
$subscription = Creem::subscriptions()->upgrade(
    subscriptionId: 'sub_123',
    productId: 'prod_456',
    updateBehavior: 'proration-charge-immediately'
);

// Update subscription data
$subscription = Creem::subscriptions()->update('sub_123', [
    'metadata' => ['updated' => true],
]);
```

### Transactions

```php
use Romansh\LaravelCreem\Facades\Creem;

// Find a transaction by ID
$transaction = Creem::transactions()->find('txn_123');

// List all transactions (paginated)
$transactions = Creem::transactions()->list([], $page = 1, $pageSize = 20);

// List transactions with filters
$transactions = Creem::transactions()->list([
    'customer_id' => 'cust_123',
    'product_id' => 'prod_456',
], $page = 1, $pageSize = 20);

// Get transactions for a specific customer
$transactions = Creem::transactions()->byCustomer('cust_123');

// Get transactions for a specific order
$transactions = Creem::transactions()->byOrder('ord_456');

// Get transactions for a specific product
$transactions = Creem::transactions()->byProduct('prod_789');
```

### Licenses

```php
use Romansh\LaravelCreem\Facades\Creem;

// Validate a license key
$license = Creem::licenses()->validate(
    key: 'ABC123-XYZ456-XYZ456-XYZ456',
    instanceId: 'inst_123'
);

if ($license['status'] === 'active') {
    // Grant access to premium features
}

// Activate a license on a new device
$license = Creem::licenses()->activate(
    key: 'ABC123-XYZ456-XYZ456-XYZ456',
    instanceName: 'johns-macbook-pro'
);

$instanceId = $license['instance']['id'];

// Deactivate a license instance
$license = Creem::licenses()->deactivate(
    key: 'ABC123-XYZ456-XYZ456-XYZ456',
    instanceId: 'inst_123'
);
```

### Discount Codes

```php
use Romansh\LaravelCreem\Facades\Creem;

// Create a percentage discount
$discount = Creem::discounts()->create([
    'name' => 'Summer Sale 2024',
    'code' => 'SUMMER50',
    'type' => 'percentage',
    'percentage' => 50,
    'duration' => 'once',
    'max_redemptions' => 100,
    'expiry_date' => '2024-12-31T23:59:59Z',
]);

// Create a fixed amount discount
$discount = Creem::discounts()->create([
    'name' => 'Welcome Bonus',
    'code' => 'WELCOME20',
    'type' => 'fixed',
    'amount' => 2000, // $20.00 in cents
    'currency' => 'USD',
    'duration' => 'once',
]);

// Find discount by ID
$discount = Creem::discounts()->find('disc_123');

// Find discount by code
$discount = Creem::discounts()->findByCode('SUMMER50');

// Delete a discount code
$result = Creem::discounts()->delete('disc_123');
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

### License Validation Controller

```php
namespace App\Http\Controllers;

use Romansh\LaravelCreem\Facades\Creem;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function validate(Request $request)
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
            'instance_id' => 'required|string',
        ]);

        try {
            $license = Creem::licenses()->validate(
                $validated['license_key'],
                $validated['instance_id']
            );

            if ($license['status'] === 'active') {
                return response()->json([
                    'valid' => true,
                    'expires_at' => $license['expires_at'],
                ]);
            }

            return response()->json(['valid' => false], 403);
        } catch (\Exception $e) {
            return response()->json(['valid' => false], 403);
        }
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

If you discover a security vulnerability, please email the package maintainer.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

- **Creem Documentation**: https://docs.creem.io
- **Package Issues**: https://github.com/romansh/laravel-creem/issues
