### Merchant
Marketplace payments support for Laravel (via Stripe Connect)

#### Installation

Install package:
`composer required polarize/merchant "^0.0"`

Add your stripe API credentials to your environment file (make sure Stripe Connect is enabled via the Stripe Dashboard):
```
STRIPE_KEY="..."
STRIPE_SECRET="..."
```

Publish the migration for Merchant table:
`php artisan vendor:publish --tag=merchant.migrations`

Run migrations:
`php artisan migrate`

#### Setup your models
Relate the `Merchant` model to your `User` model (or whichever model you'd like to give merchant payment capabilities to):
```
<?php
use Merchant\Merchant;

public function merchant(): MorphOne
{
    return $this->morphOne(Merchant::class, 'merchantable');
}
```

#### Facilitate payments
Here are some things you can do for your merchants:

`createStripeAccount(), deleteStripeAccount()` - Create/delete the `Stripe\Account` in stripe

`newAccountLinkUrl()` - Generate a new URL to the Stripe connect portal

`createTaxId(), taxIds(), deleteTaxId()` - Manage account level tax ids
