<?php

namespace Merchant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory, HasStripeConnect;

    protected $fillable = ['stripe_account_id'];
}
