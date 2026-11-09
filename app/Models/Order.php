<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'menu_name',
        'p_kopi',
        'p_gula',
        'p_susu',
        'p_air',
        'weight',
        'status',
        'processing_node',
        'wfq_finish_time',
        'started_at',
        'completed_at',
    ];

    // Paksa agar data selalu terbaca sebagai angka
    protected $casts = [
        'p_kopi' => 'integer',
        'p_gula' => 'integer',
        'p_susu' => 'integer',
        'p_air'  => 'integer',
        'weight' => 'integer',
        'wfq_finish_time' => 'decimal:8',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function serviceTime(): int
    {
        return $this->p_kopi + $this->p_gula + $this->p_susu + $this->p_air;
    }

    public function waitingTime(): ?int
    {
        if ($this->created_at === null || $this->started_at === null) {
            return null;
        }

        return $this->created_at->diffInSeconds($this->started_at);
    }

    public function processingTime(): ?int
    {
        if ($this->started_at === null || $this->completed_at === null) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    public function flowTime(): ?int
    {
        if ($this->created_at === null || $this->completed_at === null) {
            return null;
        }

        return $this->created_at->diffInSeconds($this->completed_at);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}