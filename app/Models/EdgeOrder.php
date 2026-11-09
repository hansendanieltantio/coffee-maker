<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EdgeOrder extends Model
{
    protected $fillable = [
        'cloud_order_id',
        'user_name',
        'menu_name',
        'weight',
        'p_kopi',
        'p_gula',
        'p_susu',
        'p_air',
        'status',
        'wfq_finish_time',
        'started_at',
        'completed_at',
        'cloud_synced_at',
        'wfq_synced_at',
    ];

    protected $casts = [
        'weight' => 'integer',
        'p_kopi' => 'integer',
        'p_gula' => 'integer',
        'p_susu' => 'integer',
        'p_air' => 'integer',
        'wfq_finish_time' => 'decimal:8',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cloud_synced_at' => 'datetime',
        'wfq_synced_at' => 'datetime',
    ];

    public function serviceTime(): int
    {
        return $this->p_kopi + $this->p_gula + $this->p_susu + $this->p_air;
    }
}
