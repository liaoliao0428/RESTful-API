<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'name',
        'writer',
        'publishdate',
        'summary',
    ];

    public function type()
    {
        return $this->belongsTo('App\Models\Type');
    }
}
