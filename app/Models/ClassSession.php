<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSession extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'batch_id',
        'instructor_id',
        'topic',
        'description',
        'start_time',
        'duration',
        'meeting_link',
        'is_cancelled',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'duration' => 'integer',
        'is_cancelled' => 'boolean',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }
}
