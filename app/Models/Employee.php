<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Person;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'hire_date',
        'work_card',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
