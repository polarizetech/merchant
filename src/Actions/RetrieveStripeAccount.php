<?php

namespace Merchant\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class RetrieveStripeAccount
{
    use AsAction;

    /**
     * Retrieve the Stripe account details for the given merchant.
     * @param mixed $merchant
     * @param array|null $data
     */
    public function handle(mixed $merchant, ?array $data = []): array
    {
        $stripeAccountData = (
            collect($merchant->stripeAccount()->toArray())->only([
                'capabilities',
                'business_profile',
                'default_currency',
                'external_accounts',
                'requirements',
                'payouts_enabled',
                'settings',
                'details_submitted',
                'future_requirements',
            ])
        );

        $stripeClient = $merchant->stripe();

        $stripePayoutAmount = collect($stripeClient->payouts->all(['limit' => 100])?->data);
        $stripePayoutAmount = $stripePayoutAmount->count() > 0 ? dollars($stripePayoutAmount->sum('amount')) : 0;

        $stripeAccountData = $stripeAccountData->put(
            'external_accounts',
            $stripeAccountData->get('external_accounts', [])['data'] ?? null
        );

        $stripeAccountData = $stripeAccountData->put('stripe_payouts_total', $stripePayoutAmount);

        $details = [
            'stripeAccount' => $stripeAccountData,
            'payoutScheduleOptions' => [
                ['label' => __('Every day'), 'value' => 'daily'],
                ['label' => __('Once per week'), 'value' => 'weekly'],
                ['label' => __('Once per month'), 'value' => 'monthly'],
            ],
        ];

        return array_merge($details, $data ?: []);
    }
}
