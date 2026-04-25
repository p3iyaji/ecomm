<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook.secret');

        if (! $secret) {
            Log::warning('Stripe webhook received but STRIPE_WEBHOOK_SECRET is not configured.');

            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret,
                config('services.stripe.webhook.tolerance', 300)
            );
        } catch (\Exception $e) {
            Log::warning('Stripe webhook signature verification failed.', ['message' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        if ($event->type === 'payment_intent.payment_failed') {
            Log::notice('Stripe payment failed', ['id' => $event->data->object->id ?? null]);
        }

        if ($event->type === 'charge.dispute.created') {
            Log::warning('Stripe charge dispute created', ['event' => $event->id]);
        }

        return response('OK', 200);
    }
}
