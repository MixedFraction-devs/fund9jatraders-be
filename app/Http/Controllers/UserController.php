<?php

namespace App\Http\Controllers;

use App\Mail\VerifyOTP;
use App\Models\User;
use App\Notifications\WelcomeUserNotification;
use App\Services\Utils;
use App\Settings\PlatformSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tzsk\Otp\Facades\Otp;

class UserController extends Controller
{

    // register

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        //check OTP
        if (!$this->checkOtp($request->otp, $request->email)) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 401);
        }

        $referral = User::whereCode($request->referral)->first();
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'address_country' => $request->address_country,
            'address_state' => $request->address_state,
            'password' => $request->password,
            'referrer_id' => $referral?->id,
            'code' => static::generateSafeUserCode(),
        ]);

        $user->notify(new WelcomeUserNotification());

        //create sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function getEmailOtp(Request $request)
    {

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8'
        ]);

        $otp = Otp::digits(4)->generate($request->email);

        // Send email to user
        Mail::to($request->email)->send(new VerifyOTP($otp));

        return response()->json([
            'message' => 'OTP successfully sent to email',
            // 'otp' => $otp
        ], 200);
    }

    public function checkOtp($otp, $email)
    {
        return Otp::digits(4)->check($otp, $email);
    }


    // login user

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        //check if user exists
        $user = User::where('email', $request->email)->first();

        // check if user is suspended
        if (!$user) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }
        if ($user->status == 'suspended') {
            return response()->json([
                'message' => 'User is suspended'
            ], 401);
        }



        //check if password is correct

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }

        //create sanctum token

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    //reset password

    public function requestPasswordReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Account not found'
            ], 404);
        }

        $otp = Otp::digits(4)->generate($request->email);

        // Send email to user
        Mail::to($request->email)->send(new VerifyOTP($otp));

        return response()->json([
            'message' => 'OTP successfully sent to email',
            // 'otp' => $otp
        ], 200);
    }

    // verify OTP

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|min:4|max:4'
        ]);

        if (!$this->checkOtp($request->otp, $request->email)) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 401);
        }

        return response()->json([
            'message' => 'OTP verified successfully'
        ], 200);
    }

    // update password

    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Account not found'
            ], 404);
        }

        $user->update([
            'password' => $request->password
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ], 200);
    }

    //update profile

    // register with referral


    public function getPlatformSettings(PlatformSettings $platformSettings)
    {
        return response()->json([
            'settings' => [
                'product_one_price' => $platformSettings->product_one_price,
                'product_one_title' => $platformSettings->product_one_title,
                'product_one_description' => $platformSettings->product_one_description,
                'product_two_price' => $platformSettings->product_two_price,
                'product_two_title' => $platformSettings->product_two_title,
                'product_two_description' => $platformSettings->product_two_description,
                'product_three_price' => $platformSettings->product_three_price,
                'product_three_title' => $platformSettings->product_three_title,
                'product_three_description' => $platformSettings->product_three_description,
                'affiliate_percentage' => $platformSettings->affiliate_percentage,
                'affiliate_minimum_withdrawal' => $platformSettings->affiliate_minimum_withdrawal,
                'minimum_withdrawal' => $platformSettings->minimum_withdrawal,
                'site_name' => $platformSettings->site_name,
                'site_description' => $platformSettings->site_description,
                'lock_purchases' => $platformSettings->lock_purchases,
                'lock_withdrawals' => $platformSettings->lock_withdrawals,
                'lock_referrals' => $platformSettings->lock_referrals,
            ]
        ], 200);
    }

    public function user()
    {
        $user = auth()->user();

        if ($user->status == 'suspended') {
            return response()->json([
                'message' => 'User is suspended'
            ], 401);
        }

        return $user;
    }

    public function updateBankDetails(Request $request)
    {

        $request->validate([
            'crypto_type' => 'required|string|min:3|max:20',
            'crypto_network' => 'required|string|min:5|max:20',
            'crypto_wallet_address' => 'required|min:10|max:100',
        ]);
        $user = auth()->user();


        $user->update([
            'crypto_type' => $request->crypto_type,
            'crypto_network' => $request->crypto_network,
            'crypto_wallet_address' => $request->crypto_wallet_address,
        ]);

        return response()->json([
            'message' => 'Payout details updated successfully',
            'user' => $user
        ], 200);
    }

    public function updateAddress(Request $request)
    {
        $request->validate([
            'address_country' => 'required|string|min:2',
            'address_state' => 'required|string|min:5',
        ]);
        $user = auth()->user();

        $user->update([
            'address_country' => $request->address_country,
            'address_state' => $request->address_state,
        ]);

        return response()->json([
            'message' => 'Address updated successfully',
            'user' => $user
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',

        ]);

        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => 'Old password is incorrect'
            ], 401);
        }

        $user->update([
            'password' => $request->new_password
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ], 200);
    }

    public function createWithdrawalRequest()
    {
        /**
         * @var \App\Models\User
         */
        $user = auth()->user();



        // check if user has bank settings setup
        if (!$user->crypto_type || !$user->crypto_network || !$user->crypto_wallet_address) {
            return response()->json([
                'message' => 'Please update your bank details'
            ], 401);
        }

        // check if the user has a product that is in phase 3
        if ($user->orders()->where('phase', 3)->whereNull("breached_at")->count() == 0) {
            return response()->json([
                'message' => 'You need to have a product that is at least in phase 3 to withdraw'
            ], 401);
        }

        // check if user has pending withdrawal request
        if ($user->withdrawalRequests->where('status', 'pending')->count() > 0) {
            return response()->json([
                'message' => 'Please wait, you have a pending withdrawal request'
            ], 401);
        }

        // create  withdrawal request

        $withdrawal_request = $user->withdrawalRequests()->create([
            'crypto_type' => $user->crypto_type,
            'crypto_wallet_address' => $user->crypto_wallet_address,
            'crypto_network' => $user->crypto_network,
            'affiliate_amount' => $user->balance,
        ]);
    }

    public function getWithdrawls()
    {
        $user = auth()->user();

        $withdrawals = $user->withdrawalRequests()->get();

        return response()->json([
            'withdrawals' => $withdrawals
        ], 200);
    }

    public static function generateSafeUserCode()
    {
        do {
            $exist = User::where(
                'code',
                $code = Utils::random(2, false, true) . Utils::random(3, false, true, true)
            )->count('id') > 0;
        } while ($exist);

        return $code;
    }

    // get all afiliate referrals
    public function getMyReferrals()
    {
        $user = auth()->user();
        $referrals = $user->referrals()->get();
        return response()->json([
            'referrals' => $referrals
        ], 200);
    }
    // get all affiliate referrals

}
