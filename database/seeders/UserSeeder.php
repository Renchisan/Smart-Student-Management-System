<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create teacher
        $teacher = User::create([
            'name' => 'Gerry',
            'email' => 'gerry@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'employeeID' => 'EMP-1001',
        ]);

        // Create students
        $students = [
            [
                'name' => 'April Iloreta',
                'email' => 'april@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'studentID' => '21-000001',
                'yearLevel' => 3,
                'program' => 'CS',
                'teacher_id' => $teacher->id,
            ],
            [
                'name' => 'Jam Lucas',
                'email' => 'jam@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'studentID' => '21-000002',
                'yearLevel' => 3,
                'program' => 'CS',
                'teacher_id' => $teacher->id,
            ],
        ];

        $names = [
            'Alex Santos', 'Bella Reyes', 'Caleb Cruz', 'Diana Bautista', 'Ethan Garcia', 
            'Fiona Dela Cruz', 'Gavin Gonzales', 'Hannah Torres', 'Ivan Ramos', 'Jasmine Aquino',
            'Kevin Flores', 'Luna Mendoza', 'Marcus Castro', 'Nina Navarro', 'Oscar Domingo',
            'Paula Lopez', 'Quentin Villanueva', 'Riley Marquez', 'Sophia Francisco', 'Tyler Gutierrez',
            'Uma Santos', 'Victor Reyes', 'Wendy Cruz', 'Xander Bautista', 'Yara Garcia', 'Zane Dela Cruz'
        ];

        foreach ($names as $index => $fullName) {
            $email = strtolower(str_replace(' ', '.', $fullName)) . '@gmail.com';
            $students[] = [
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'student',
                'studentID' => '21-' . str_pad($index + 3, 6, '0', STR_PAD_LEFT),
                'yearLevel' => '3',
                'program' => ['CS', 'IT'][rand(0, 1)],
                'teacher_id' => $teacher->id,
            ];
        }


        foreach ($students as $student) {
            User::create($student);
        }
    }
}
