<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'status',
        'color_index',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'color_index' => 'integer',
    ];

    /**
     * Task status constants
     */
    const STATUS_TODO = 'todo';
    const STATUS_PROGRESS = 'progress';
    const STATUS_DONE = 'done';

    /**
     * Available task colors (matching Flutter model)
     */
    const TASK_COLORS = [
        0 => '#2196F3', // Blue
        1 => '#4CAF50', // Green
        2 => '#FF9800', // Orange
        3 => '#9C27B0', // Purple
        4 => '#F44336', // Red
    ];

    /**
     * Get the user that owns the task.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the color hex code for the task.
     */
    public function getColorAttribute()
    {
        return self::TASK_COLORS[$this->color_index] ?? self::TASK_COLORS[0];
    }

    /**
     * Scope for filtering tasks by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering tasks by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
