<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'batch_student')
            ->withPivot('enrolled_at', 'is_active')
            ->withTimestamps();
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'batch_instructor')
            ->withPivot('assigned_at', 'is_active')
            ->withTimestamps();
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }
}
