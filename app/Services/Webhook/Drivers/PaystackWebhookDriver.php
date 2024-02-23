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
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Walletable\Money\Money;

class PaystackWebhookDriver implements WebhookInterface
{
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'paystack';
    }

    /**
     * @inheritDoc
     */
    public function validate(Request $request, array $data, string $raw): bool
    {
        return hash_equals(
            hash_hmac(
                'sha512',
                $raw,
                config('services.paystack.secret')
            ),
            $request->header('x-paystack-signature')
        );
    }

    /**
     * @inheritDoc
     */
    public function process(Request $request, array $data, string $raw): Response
    {
        if (data_get($data, 'event') === 'charge.success' && !is_null(data_get($data, 'data.metadata.type'))) {
            $reference = data_get($data, 'data.reference');
            $email = data_get($data, 'data.customer.email');
            $type = data_get($data, 'data.metadata.type');
            /**
             * @var PlatformSettings
             */
            $settings = app(PlatformSettings::class);

            /**
             * @var User
             */
            $user = User::whereEmail($email)->first();

            if ($user) {
                try {
                    DB::beginTransaction();
                    $order =  Order::create([
                        'user_id' => auth()->user()->id,
                        'product_type' => $data['type'] == "one" ? 'ONE' : ($data['type'] == 'two' ? 'TWO' : 'THREE'),
                        'phase' => 1,
                        'cost' => $data['type'] ==  "one" ? $settings->product_one_price : ($data['type'] == "two" ? $settings->product_two_price : $settings->product_three_price),
                    ]);
                    OrderController::store($order, $user, $settings, $data['type']);
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