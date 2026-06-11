<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StatsSnapshot extends Model {
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'revenue' => 'decimal:2', 'rating_avg' => 'decimal:1'];
}
