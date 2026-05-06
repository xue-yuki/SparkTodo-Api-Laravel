<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'tag',
        'time',
        'due_date',
        'priority',
        'done',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'done' => 'boolean',
        'completed_at' => 'datetime',
        'due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
