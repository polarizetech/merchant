<?php

namespace Merchant\Actions;

class CreateStripeAccountLink
{
    public static function run(mixed $merchant, string $stripeAccountId, string $returnUrl, string $refreshUrl): \Stripe\AccountLink
    {
        $stripe = $merchant->stripe();

        return $stripe->accountLinks->create([
            'account' => $stripeAccountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
            'collection_options' => [
                'fields' => 'eventually_due',
                'future_requirements' => 'include',
            ],
        ]);
    }
}
