<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'user_id',
        'generated_at',
        'details',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
}
