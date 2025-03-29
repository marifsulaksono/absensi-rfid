<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfidModel extends Model
{
    use HasFactory;
    
    protected $table = 'rfids';

    protected $fillable = ['number', 'id_student', 'is_active'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }
}
