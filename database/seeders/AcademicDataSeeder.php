<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcademicDataSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();

        foreach ($students as $student) {
            $G1 = rand(0, 19);
            $G2 = rand(0, 19);
            $G3 = rand(0, 19);
            DB::table('academic_data')->insert([
                'student_id' => $student->id,
                'studytime' => rand(1, 4),
                'failures' => rand(0, 3),
                'schoolsup' => rand(0, 1),
                'famsup' => rand(0, 1),
                'paid' => rand(0, 1),
                'activities' => rand(0, 1),
                'absences' => rand(0, 30),
                'G1' => $G1,
                'G2' => $G2,
                'G1_G2_diff' => $G2 - $G1,
                'G_avg' => ($G1 + $G2) / 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
