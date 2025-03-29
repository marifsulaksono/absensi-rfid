<?php

namespace App\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PresensiModel extends Model
{
    use HasFactory;

    protected $table = 'presents';

    protected $fillable = ['id_student', 'date', 'in', 'out', 'is_displayed'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }
}
