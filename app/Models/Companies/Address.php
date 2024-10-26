<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $appends = ['working_hours'];

    public function working_hours(){
        return $this->hasMany(WorkingHour::class);
    }

    public function getWorkingHoursAttribute()
    {
        return $this->working_hours()->get()->mapWithKeys(function ($workingHour) {
            return [
                $workingHour->day_of_week => [
                    'start_time' => $workingHour->start_time,
                    'end_time' => $workingHour->end_time,
                    'is_selected' => $workingHour->is_selected,
                ],
            ];
        });
    }
}
