<?php

namespace Merchant;

use Merchant\Actions\CreateStripeAccount;
use Merchant\Actions\CreateStripeAccountLink;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Stripe\StripeClient;

/**
 * Trait for decorating a model with Stripe Connect features
 */
trait HasStripeConnect
{
    public static function bootedHasStripeConnect(): void
    {
        static::deleting(fn ($merchant) => (
            $merchant->deleteStripeAccount()
        ));
    }

    protected function currency(): Attribute
    {
        return new Attribute(
            get: fn (): string => (string) str($this->stripeAccountFromCache()->default_currency ?: 'CAD')->upper()
        );
    }

    public function stripe()
    {
        return new StripeClient([
            'api_key' => config('services.stripe.secret'),
            'stripe_version' => config('services.stripe.version', '2023-10-16'),
        ]);
    }

    public function createStripeAccount(string $email, string $country, ?array $capabilities = []): self
    {
        $stripeAccount = CreateStripeAccount::run(
            merchant: $this,
            email: $email,
            country: $country,
            capabilities: $capabilities
        );

        $this->stripe_account_id = $stripeAccount->id;
        $this->save();

        return $this;
    }

    public function newAccountLinkUrl(string $returnUrl, string $refreshUrl): string
    {
        $stripeAccountLink = CreateStripeAccountLink::run(
            merchant: $this,
            stripeAccountId: $this->stripe_account_id,
            returnUrl: $returnUrl,
            refreshUrl: $refreshUrl
        );

        return $stripeAccountLink->url;
    }

    public function stripeAccount(): ?\Stripe\Account
    {
        if ($stripeAccount = $this->stripe()->accounts->retrieve($this->stripe_account_id)) {
            Cache::put("stripe_account_{$this->id}", $stripeAccount->toJSON());
        }

        return $stripeAccount;
    }

    public function stripeAccountFromCache(): ?object
    {
        $json = Cache::get("stripe_account_{$this->id}", fn () => $this->stripeAccount()?->toJSON());

        return json_decode($json);
    }

    public function createTaxId(string $type, string $value)
    {
        return $this->stripe()->taxIds->create([
            'type' => $type,
            'value' => $value,
            // 'owner' => [
            //     'type' => 'account',
            //     'account' => $this->stripe_account_id,
            // ]
        ], ['stripe_account' => $this->stripe_account_id]);
    }

    public function deleteTaxId(string $id)
    {
        return $this->stripe()->taxIds->delete($id, [
            // 'owner' => [
            //     'type' => 'account',
            //     'account' => $this->stripe_account_id
            // ]
        ], ['stripe_account' => $this->stripe_account_id]);
    }

    public function taxIds(): Collection
    {
        $taxIds = $this->stripe()->taxIds->all([
            'limit' => 100,
            // 'owner' => [
            //     'type' => 'account',
            //     'account' => $this->stripe_account_id
            // ]
        ], ['stripe_account' => $this->stripe_account_id]);

        return collect($taxIds->data);
    }

    protected function deleteStripeAccount(): self
    {
        $this->stripe()->accounts->delete($this->stripe_account_id);

        return $this;
    }
}
