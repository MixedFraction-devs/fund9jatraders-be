<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\ProductOne;
use App\Models\ProductThree;
use App\Models\ProductTwo;
use App\Models\User;
use App\Notifications\ProductLowStockNotification;
use App\Notifications\ProductPurchaseNotification;
use App\Settings\PlatformSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /**
         * @var User
         */
        $user = auth()->user();
        $orders = $user->orders()->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function cryptomus(Request $request, string $type)
    {
        /**
         * @var User
         */
        $user = $request->user();
        /**
         * @var PlatformSettings
         */
        $settings = app(PlatformSettings::class);
        $amount = $type ==  "one" ? $settings->product_one_price : ($type == "two" ? $settings->product_two_price : $settings->product_three_price);

        $body = [
            'order_id' => Str::uuid(),
            'amount' => round($amount / 100, 2),
            'currency' => 'USD',
            'subtract' => 100,
            'url_callback' => config('services.cryptomus.webhook_url')
        ];

        $sign = md5(base64_encode(json_encode($body)) . config('services.cryptomus.key'));

        $response = Http::cryptomus($sign)->post('payment', $body);

        if ($response->successful()) {
            Cache::store('database')->put('cryptomus-' . $body['order_id'], [
                'amount' => $request->amount * 100,
                'currency' => 'USD',
                'user_id' => $user->id,
                'type' => $request->type,
                'reference' => $body['order_id'],
            ]);

            return response()->json([
                'message' => 'Payment initiated successfully.',
                'reference' => $body['order_id'],
                'url' => $response->json('result.url'),
                'expired_at' => (int)$response->json('result.expired_at')
            ]);
        } else {
            return response()->json([
                'message' => 'Payment service is currently unavailable.'
            ], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public static function store(Order $order, User $user, $type, PlatformSettings $settings)
    {
        /**
         * @var User
         */
        $referrer = $user->referrer;
        if ($referrer) {
            $referrer->creditBalance((int)$order->cost * 0.05);
        }

        if ($type == 'one') {
            // check for available product one
            $product = ProductOne::where('order_id', null)->where('mode', 'demo')->where('status', 'inactive')->first();

            $productCount = ProductOne::where('order_id', null)->where('mode', 'demo')->where('status', 'inactive')->count();

            // check if product count is less than 10 and if it is, alert the admin
            if ($productCount < 10) {
                // notify admin
                $admin = User::where('role', 'admin')->first();
                $admin->notify(new ProductLowStockNotification($productCount, app(PlatformSettings::class)->product_one_title));
            }

            if (!$product) {
                return false;
            }

            $product->order_id = $order->id;
            $product->user_id = auth()->user()->id;
            $product->save();

            $order->product_id = $product->id;
            $product->status = 'active';
            $product->purchased_at = now();
            $product->save();
            $order->save();

            $user->notify(new ProductPurchaseNotification($product, $order));


            return true;
        }

        if ($type == 'two') {

            $product = ProductTwo::where('order_id', null)->where('mode', 'demo')->where('status', 'inactive')->first();

            $productCount = ProductTwo::where('order_id', null)->where('mode', 'demo')->where('status', 'inactive')->count();

            // check if product count is less than 10 and if it is, alert the admin

            if ($productCount < 10) {
                // notify admin
                $admin = User::where('role', 'admin')->first();
                $admin->notify(new ProductLowStockNotification($productCount, app(PlatformSettings::class)->product_one_title));
            }

            if (!$product) {
                return false;
            }

            $product->order_id = $order->id;
            $product->user_id = auth()->user()->id;
            $product->save();

            $order->product_id = $product->id;
            $product->status = 'active';
            $product->purchased_at = now();
            $product->save();
            $order->save();

            $user->notify(new ProductPurchaseNotification($product, $order));

            return true;
        }

        if ($type == 'three') {

            $product = ProductThree::where('order_id', null)->where('mode', 'demo')->where('status', 'inactive')->first();

            $productCount = ProductThree::where('order_id', null)->where('mode', 'demo')->where('status', 'inactive')->count();

            // check if product count is less than 10 and if it is, alert the admin

            if ($productCount < 10) {
                // notify admin
                $admin = User::where('role', 'admin')->first();
                $admin->notify(new ProductLowStockNotification($productCount, app(PlatformSettings::class)->product_one_title));
            }

            if (!$product) {
                return false;
            }
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
