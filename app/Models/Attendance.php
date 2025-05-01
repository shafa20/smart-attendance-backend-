<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'user_id',
        'status',
        'marked_at',
        'remarks',
    ];

    protected $casts = [
        'marked_at' => 'datetime',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
