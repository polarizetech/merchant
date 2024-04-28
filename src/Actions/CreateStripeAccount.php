<?php

namespace Merchant\Actions;

class CreateStripeAccount
{
    public static function run(
        mixed $merchant,
        string $email,
        string $country,
        ?array $capabilities = []
    ): \Stripe\Account
    {
        $stripe = $merchant->stripe();

        return $stripe->accounts->create([
            'type' => 'express',
            'country' => $country,
            'email' => $email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                ...$capabilities
            ],
            'settings' => [
                'payouts' => [
                    'schedule' => [
                        'delay_days' => 7,
                        'interval' => 'weekly',
                        'weekly_anchor' => 'friday',
                    ],
                ],
            ],
        ]);
    }
}
