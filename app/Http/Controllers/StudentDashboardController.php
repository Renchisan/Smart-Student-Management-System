<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::id();

        $student = DB::table('users')
        ->join('academic_data', 'users.id', '=', 'academic_data.student_id')
        ->leftJoin('performances', 'users.id', '=', 'performances.student_id')
        ->where('users.id', $user)
        ->select(
            'users.studentID as student_id',
            'users.name',
            'users.program',
            'users.yearLevel',
            'academic_data.g_avg as ave',
            'academic_data.absences',
            'academic_data.schoolsup',
            'academic_data.famsup',
            'academic_data.paid',
            'academic_data.activities',
            'performances.risk as risk'
        )
        ->first();
        

        
        return inertia('StudentDashboard', [
            'student' => $student,
        ]);

    }
}
