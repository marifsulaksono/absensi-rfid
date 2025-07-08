<?php

namespace App\Models;

use App\Models\PresensiModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, HasUuids, softDeletes;
    protected $fillable = ['nis', 'name', 'address', 'class_id', 'birthday', 'phone', 'email', 'rfid_number', 'is_active', 'photo'];
    protected $table = 'students';
    public $timestamp = true;

    public function presences()
    {
        return $this->hasMany(PresensiModel::class, 'id_student');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id', 'id');
    }
}
