<?php

namespace Merchant\Actions;

class CreateStripeAccount
{
    public static function run(mixed $merchant, string $email, string $country): \Stripe\Account
    {
        $stripe = $merchant->stripe();

        return $stripe->accounts->create([
            'type' => 'express',
            'country' => $country,
            'email' => $email,
            'capabilities' => [
                'transfers' => ['requested' => true],
                'card_payments' => ['requested' => true],
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
