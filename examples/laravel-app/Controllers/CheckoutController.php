<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Romansh\LaravelCreem\Facades\Creem;

/**
 * Example controller showing how to create checkouts with Creem.
 */
class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function show(string $productId)
    {
        try {
            $product = Creem::products()->find($productId);

            return view('checkout.show', compact('product'));
        } catch (\Exception $e) {
            return back()->with('error', 'Product not found');
        }
    }

    /**
     * Create a new checkout session.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|string',
            'email' => 'required|email',
        ]);

        try {
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
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create checkout: '.$e->getMessage());
        }
    }

    /**
     * Handle successful checkout.
     */
    public function success(Request $request)
    {
        return view('checkout.success');
    }

    /**
     * Example: Using a different profile for a specific product.
     */
    public function storeWithProfile(Request $request)
    {
        $checkout = Creem::profile('product_a')
            ->checkouts()
            ->create([
                'product_id' => $request->input('product_id'),
                'success_url' => route('checkout.success'),
            ]);

        return redirect($checkout['checkout_url']);
    }

    /**
     * Example: Using inline configuration.
     */
    public function storeWithInlineConfig(Request $request)
    {
        $checkout = Creem::withConfig([
            'api_key' => config('custom.creem_key'),
            'test_mode' => true,
        ])->checkouts()->create([
            'product_id' => $request->input('product_id'),
            'success_url' => route('checkout.success'),
        ]);

        return redirect($checkout['checkout_url']);
    }
}
