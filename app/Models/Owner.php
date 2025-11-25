<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Owner extends Model
{
    use HasFactory;

    protected $table = 'owners';

    protected $fillable = [
        'user_id',
        'contact',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
