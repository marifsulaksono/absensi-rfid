<?php

namespace App\Models;

use App\Models\PresensiModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['nis', 'name', 'address', 'class', 'birthday', 'photo'];

    public function presences()
    {
        return $this->hasMany(PresensiModel::class, 'id_student');
    }
}
