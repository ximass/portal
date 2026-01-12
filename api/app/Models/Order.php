<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    // Status para pre_order (orçamento)
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_WAITING_RESPONSE = 'waiting_response';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_APPROVED = 'approved';

    // Status para order (pedido)
    const STATUS_WAITING_RELEASE = 'waiting_release';
    const STATUS_RELEASED_FOR_PRODUCTION = 'released_for_production';
    const STATUS_FINISHED = 'finished';

    protected $fillable = [
        'customer_id',
        'type',
        'status',
        'order_number',
        'nf_number',
        'markup',
        'final_value',
        'delivery_type',
        'delivery_value',
        'service_value',
        'discount',
        'delivery_date',
        'estimated_delivery_date',
        'payment_obs',
        'obs',
        'os_file',
    ];

    protected $casts = [
        'final_value' => 'float',
        'delivery_value' => 'float',
        'service_value' => 'float',
        'discount' => 'float',
        'markup' => 'float',
        'delivery_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sets()
    {
        return $this->hasMany(Set::class);
    }

    public static function getAvailableStatuses($type)
    {
        if ($type === 'pre_order') {
            return [
                self::STATUS_IN_PROGRESS,
                self::STATUS_WAITING_RESPONSE,
                self::STATUS_CANCELLED,
                self::STATUS_APPROVED,
            ];
        }

        return [
            self::STATUS_WAITING_RELEASE,
            self::STATUS_RELEASED_FOR_PRODUCTION,
            self::STATUS_FINISHED,
        ];
    }

    public function canTransitionTo($newStatus)
    {
        $transitions = [
            // Fluxo de pre_order
            self::STATUS_IN_PROGRESS => [
                self::STATUS_WAITING_RESPONSE,
                self::STATUS_CANCELLED,
                self::STATUS_APPROVED,
            ],
            self::STATUS_WAITING_RESPONSE => [
                self::STATUS_IN_PROGRESS,
                self::STATUS_CANCELLED,
                self::STATUS_APPROVED,
            ],
            self::STATUS_CANCELLED => [
                self::STATUS_WAITING_RESPONSE,
            ],
            self::STATUS_APPROVED => [
                self::STATUS_WAITING_RELEASE,
            ],
            
            // Fluxo de order
            self::STATUS_WAITING_RELEASE => [
                self::STATUS_RELEASED_FOR_PRODUCTION,
            ],
            self::STATUS_RELEASED_FOR_PRODUCTION => [
                self::STATUS_WAITING_RELEASE,
                self::STATUS_FINISHED,
            ],
            self::STATUS_FINISHED => [
                self::STATUS_RELEASED_FOR_PRODUCTION,
            ],
        ];

        return isset($transitions[$this->status]) && 
               in_array($newStatus, $transitions[$this->status]);
    }
}