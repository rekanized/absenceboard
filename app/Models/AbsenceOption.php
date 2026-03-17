<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsenceOption extends Model
{
    protected $fillable = [
        'code',
        'label',
        'color',
        'sort_order',
    ];
}
