<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ThingSettings extends Model
{
    public function thing()
    {
        return $this->belongsTo(Thing::class);
    }

    public function library()
    {
        return $this->belongsTo(Library::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['thing_id', 'library_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['loans_without_barcode', 'reminders', 'loan_time'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['loans_without_barcode', 'reminders', 'loan_time'];

    /**
     * Helper method to update setting.
     */
    public function setValue(string $key, $value)
    {
        $data = $this->data;
        Arr::set($data, $key, $value);
        $this->data = $data;
    }

    /**
     * Get the loans_without_barcode value.
     *
     * @param  boolean  $value
     * @return boolean
     */
    public function getLoansWithoutBarcodeAttribute()
    {
        return Arr::get($this->data, 'loans_without_barcode', false);
    }

    /**
     * Set the loans_without_barcode value.
     *
     * @param  boolean  $value
     */
    public function setLoansWithoutBarcodeAttribute(bool $value)
    {
        $this->setValue('loans_without_barcode', (bool) $value);
    }

    /**
     * Get the reminders value.
     *
     * @return boolean
     */
    public function getRemindersAttribute()
    {
        return Arr::get($this->data, 'reminders', true);
    }

    /**
     * Set the reminders value.
     *
     * @param  boolean  $value
     */
    public function setRemindersAttribute(bool $value)
    {
        $this->setValue('reminders', (bool) $value);
    }

    /**
     * Get the reminders value.
     *
     * @return boolean
     */
    public function getLoanTimeAttribute()
    {
        return Arr::get($this->data, 'loan_time', 1);
    }

    /**
     * Set the loan_time value.
     *
     * @param  boolean  $value
     */
    public function setLoanTimeAttribute(int $value)
    {
        $this->setValue('loan_time', (int) $value);
    }
}
