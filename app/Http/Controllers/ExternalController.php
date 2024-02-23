<?php

namespace App\Http\Controllers;

use App\Services\Webhook\Webhook;
use Illuminate\Http\Request;

class ExternalController extends Controller
{
    public function webhook(Request $request, string $driver)
    {
        return Webhook::processWebhook($driver, $request);
    }
}
