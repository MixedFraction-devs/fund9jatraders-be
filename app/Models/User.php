<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements Wallet, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasWallet;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    public function getReferralLinkAttribute()
    {
        return $this->referral_link = 'https://app.forexfreefunds.com/auth/register/' . $this->code;
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id', 'id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id', 'id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessFilament(): bool
    {
        return $this->isAdminOrManager();
    }

    public function isAdminOrManager()
    {
        return $this->role == "admin" || $this->role == "manager";
    }

    public function isManager()
    {
        return $this->role == "manager";
    }

    public function isAdmin()
    {
        return $this->role == "admin";
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function withdrawAffiliateBalance()
    {
        return $this->debitBalance($this->amount);
    }

    /**
     * Credit the meter`s recharge balance
     *
     * @param int $unit
     */
    public function creditBalance(int $amount)
    {
        $tries = 1;
        do {
            $this->refresh();

            $updated = static::whereId($this->id)->where(
                'amount',
                $this->getRawOriginal('amount')
            )->update([
                'amount' => (int)$this->getRawOriginal('amount') + $amount
            ]);

            $tries++;
        } while (!$updated && $tries !== 10);

        $this->refresh();
        return $updated;
    }

    /**
     * debit the users balance
     *
     * @param int $unit
     */
    public function debitBalance(int $amount)
    {
        $tries = 1;
        do {
            $this->refresh();

            if (!$this->amount > $amount) {
                throw new \Exception(
                    $this->name . '\'s does not have up to ' . round($amount / 100, 2) . ' in their wallet, try ' .  round($this->balance, 2) . '.',
                    56643
                );
            }

            $updated = static::whereId($this->id)->where(
                'amount',
                $this->getRawOriginal('amount')
            )->update([
                'amount' => (int)$this->getRawOriginal('amount') - $amount
            ]);

            $tries++;
        } while (!$updated && $tries !== 10);

        $this->refresh();
        return $updated;
    }
    // append users referral link

    // protected $appends = ['referral_link'];


    // show the users wallet balance as an attribute

    /**
     * Get the user's wallet balance.
     *
     * @return int
     */
}
