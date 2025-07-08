<?php

namespace App\Models;

use App\Models\Tools;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TempRfid extends Model
{
    use HasFactory;

    protected $table = 'temp_rfids';
    protected $fillable = ['number', 'tool_id'];
    public $timestamps = false;

    public function tool()
    {
        return $this->belongsTo(Tools::class, 'tool_id', 'id');
    }
}
