<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tools extends Model
{
    use HasFactory, softDeletes;

    protected $table = 'tools';

    protected $fillable = ['code', 'name', 'description', 'status'];
    public $timestamps = true;

    public function tempRfid()
    {
        return $this->hasMany(TempRfid::class, 'tool_id', 'id');
    }
}
