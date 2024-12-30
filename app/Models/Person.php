<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\Member;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cpf',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function member()
    {
        return $this->hasOne(Member::class);
    }
}
