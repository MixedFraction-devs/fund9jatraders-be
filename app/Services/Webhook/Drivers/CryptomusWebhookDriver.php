<?php

namespace App\Services\Webhook\Drivers;

use App\Http\Controllers\OrderController;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\WalletFunded;
use App\Settings\PlatformSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Walletable\Money\Money;

class CryptomusWebhookDriver implements WebhookInterface
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'cryptomus';
    }

    /**
     * @inheritDoc
     */
    public function validate(Request $request, array $data, string $raw): bool
    {
        $data = json_decode($raw, true);
        $sign = $data['sign'];
        unset($data['sign']);
        $hash = md5(base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE)) . config('services.cryptomus.key'));

        return hash_equals($sign, $hash) && in_array($request->ip(), ['91.227.144.54', '127.0.0.1', '::1']);
    }

    /**
     * @inheritDoc
     */
    public function process(Request $request, array $data, string $raw): Response
    {
        if (
            data_get($data, 'type') === 'payment' &&
            data_get($data, 'status') === 'paid' &&
            data_get($data, 'is_final')
        ) {
            $reference = data_get($data, 'order_id');
            /**
             * @var PlatformSettings
             */
            $settings = app(PlatformSettings::class);

            if (Cache::store('database')->has('cryptomus-' . $reference)) {
                $store = Cache::store('database')->get('cryptomus-' . $reference);
                /**
                 * @var User
                 */
                $user = User::find($store['user_id']);

                try {
                    DB::beginTransaction();
                    $order =  Order::create([
                        'user_id' => auth()->user()->id,
                        'product_type' => $store['type'] == "one" ? 'ONE' : ($store['type'] == 'two' ? 'TWO' : 'THREE'),
                        'phase' => 1,
                        'cost' => $store['type'] ==  "one" ? $settings->product_one_price : ($store['type'] == "two" ? $settings->product_two_price : $settings->product_three_price),
                    ]);
                    OrderController::store($order, $user, $settings, $store['type']);
                    Cache::store('database')->delete('cryptomus-' . $reference);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    throw $th;
                }
            }
        }

        return response()->json([
            'status' => 'processed'
        ], JsonResponse::HTTP_OK);
    }
}
