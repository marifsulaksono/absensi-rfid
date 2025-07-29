<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\ClassModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $class = ClassModel::where('name', $row['class_name'])->first();

        if (!$class) {
            // Skip jika class_name tidak ditemukan
            return null;
        }

        return new Student([
            'nis'        => $row['nis'],
            'name'       => $row['name'],
            'address'    => $row['address'],
            'class_id'   => $class->id,
            'birthday'   => $row['birthday'] ?? null,
            'phone'      => $row['phone'] ?? null,
            'email'      => $row['email'] ?? null,
        ]);
    }
}
