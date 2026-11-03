<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable = [
        'user_id',
        'balance'
    ];

    protected $casts = [
        'balance' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function addFunds($amount, $type = 'credit', $description = null, $metadata = null, $reference = null)
    {
        DB::beginTransaction();

        try {
            $balanceBefore = $this->balance;
            $this->balance += $amount;
            $this->save();

            $transactionData = [
                'wallet_id' => $this->id,
                'user_id' => $this->user_id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $this->balance,
                'description' => $description,
                'metadata' => $metadata,
            ];

            if ($reference) {
                $transactionData['reference_id'] = $reference['id'] ?? null;
                $transactionData['reference_type'] = $reference['type'] ?? null;
            }

            WalletTransaction::create($transactionData);

            DB::commit();
            return $this;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deductFunds($amount, $description = null, $metadata = null, $reference = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient wallet balance');
        }

        DB::beginTransaction();

        try {
            $balanceBefore = $this->balance;
            $this->balance -= $amount;
            $this->save();

            $transactionData = [
                'wallet_id' => $this->id,
                'user_id' => $this->user_id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $this->balance,
                'description' => $description,
                'metadata' => $metadata,
            ];

            if ($reference) {
                $transactionData['reference_id'] = $reference['id'] ?? null;
                $transactionData['reference_type'] = $reference['type'] ?? null;
            }

            WalletTransaction::create($transactionData);

            DB::commit();
            return $this;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function canAfford($amount)
    {
        return $this->balance >= $amount;
    }

    public function getFormattedBalanceAttribute()
    {
        return '₹' . number_format($this->balance, 2);
    }

    public static function getUserWallet($userId)
    {
        return self::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
    }

    public function scopeWithSufficientBalance($query, $amount)
    {
        return $query->where('balance', '>=', $amount);
    }
}
