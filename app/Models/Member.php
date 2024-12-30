<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Person;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'registration_number',
        'member_type',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
