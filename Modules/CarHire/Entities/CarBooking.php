<?php

namespace Modules\CarHire\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\BookingModule\Entities\Booking;

class CarBooking extends Model
{
    protected $fillable = [
        'booking_id',
        'car_id',
        'user_id',
        'start_date',
        'end_date',
        'pickup_location',
        'drop_location',
        'total_amount',
        'payment_status',
        'booking_status',
        'pickup_type',
        'pickup_time',
        'drop_time',
        'delivery_address',
        'description',
        'rent_amount',
        'delivery_fee',
        'security_deposit',
        'total_amount',
        'payment_method',
        'transaction_id',
        'is_paid',
        'delivery_latitude',
        'delivery_longitude',
        'pickup_coordinates',
        'drop_coordinates',
    ];

    protected $casts = [
        'rent_amount' => 'float',
        'delivery_fee' => 'float',
        'security_deposit' => 'float',
        'total_amount' => 'float',
        'pickup_coordinates' => 'array',
        'drop_coordinates' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($model) {
            // Keep linked regular booking in sync (mirror approach)
            if ($model->isDirty('booking_status') && !empty($model->booking_id)) {
                $linkedBooking = Booking::find($model->booking_id);
                if ($linkedBooking) {
                    $linkedBooking->booking_status = $model->booking_status;

                    // When car booking is completed, regular booking must be marked paid for CAS flows
                    if ($model->booking_status === 'completed') {
                        $linkedBooking->payment_method = $model->payment_method ?? $linkedBooking->payment_method;
                        $linkedBooking->transaction_id = $model->transaction_id ?? $linkedBooking->transaction_id;
                        $linkedBooking->is_paid = 1;
                        $linkedBooking->service_schedule = $linkedBooking->service_schedule ?? now();
                    }

                    $linkedBooking->save();
                }
            }

            // Trigger transaction handling when booking status changes to 'completed'
            if ($model->isDirty('booking_status') && $model->booking_status == 'completed') {
                // For digital/wallet payments, process commission and provider payments
                if ($model->is_paid && $model->payment_method != 'cash_after_service') {
                    placeCarBookingTransactionForCompleted($model);
                }
                // For cash after service, mark as paid and process provider payment
                elseif ($model->payment_method == 'cash_after_service') {
                    $model->is_paid = 1;
                    $model->payment_status = 'paid';
                    $model->save();
                    placeCarBookingTransactionForCompletedCas($model);
                }
            }
        });
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function user()
    {
        return $this->belongsTo(\Modules\UserManagement\Entities\User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function booking_partial_payments()
    {
        return $this->hasMany(\Modules\BookingModule\Entities\BookingPartialPayment::class, 'booking_id', 'booking_id');
    }
}
