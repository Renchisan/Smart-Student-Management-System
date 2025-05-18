<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacherId = Auth::id();

        // Example: average grade per subject for this teacher
        $gradeData = DB::table('academic_data')
            ->join('users', 'academic_data.student_id', '=', 'users.id')
            ->where('users.teacher_id', $teacherId)
            ->select(DB::raw('AVG(academic_data.absences) as avg_absences'),'users.program as label', DB::raw('AVG(academic_data.g_avg) as value'))
            ->groupBy('users.program')
            ->get();
        
        $lowestAbsences = DB::table('academic_data')
            ->join('users', 'academic_data.student_id', '=', 'users.id')
            ->where('users.teacher_id', $teacherId)
            ->orderBy('academic_data.absences', 'asc')
            ->limit(5)
            ->select(
                'users.name',
                'users.program',
                'academic_data.absences',
                'academic_data.g_avg'
            )
            ->get();

        $highestAbsences = DB::table('academic_data')
            ->join('users', 'academic_data.student_id', '=', 'users.id')
            ->where('users.teacher_id', $teacherId)
            ->orderBy('academic_data.absences', 'desc')
            ->limit(5)
            ->select(
                'users.name',
                'users.program',
                'academic_data.absences',
                'academic_data.g_avg'
            )
            ->get();

        $latestPerformances = DB::table('performances as p1')
            ->select('p1.student_id', 'p1.risk')
            ->whereRaw('p1.prediction_date = (SELECT MAX(p2.prediction_date) FROM performances as p2 WHERE p2.student_id = p1.student_id)');

        $students = DB::table('users')
            ->join('academic_data', 'users.id', '=', 'academic_data.student_id')
            ->leftJoinSub($latestPerformances, 'latest_perf',function ($join) {
                $join->on('users.id', '=', 'latest_perf.student_id');
            })
            ->where('users.teacher_id', $teacherId)
            ->select(
                'users.studentID as student_id',
                'users.name',
                'users.program',
                'academic_data.g_avg',
                'academic_data.absences',
                'academic_data.schoolsup',
                'academic_data.famsup',
                'academic_data.paid',
                'academic_data.activities',
                'latest_perf.risk as recent_risk'
            )
            ->get();

        $data = $students->map(function ($student) {
            $totalParticipation = $student->schoolsup + $student->famsup + $student->paid + $student->activities;
            $participationPercent = ($totalParticipation / 4) * 100;
            $suggestion = '';

            if ($student->g_avg < 10 && $student->absences > 10 && $participationPercent < 75) {
                $suggestion = 'At risk: low grade, high absences & low participation';
            } elseif ($student->g_avg < 10 && $student->absences > 10) {
                $suggestion = 'At risk: low grade & high absences';
            } elseif ($student->g_avg < 10 && $participationPercent < 75) {
                $suggestion = 'Needs grade improvement & improve participation';
            } elseif ($student->absences > 10 && $participationPercent < 75) {
                $suggestion = 'Monitor attendance & improve participation';
            } elseif ($student->g_avg < 10) {
                $suggestion = 'Needs grade improvement';
            } elseif ($student->absences > 10) {
                $suggestion = 'Monitor attendance';
            } elseif ($participationPercent < 75) {
                $suggestion = 'Improve participation';
            } else {
                $suggestion = 'Performing well';
            }

            return [
                'student_id' => $student->student_id,
                'name' => $student->name,
                'g_avg' => $student->g_avg,
                'program' => $student->program,
                'absences' => $student->absences,
                'schoolsup' => $student->schoolsup,
                'famsup' => $student->famsup,
                'paid' => $student->paid,
                'activities' => $student->activities,
                'recent_risk' => $student->recent_risk,
                'suggestion' => $suggestion,
            ];
        });
        return Inertia::render('TeacherDashboard', [
            'chartData' => $gradeData,
            'lowestAbsences' => $lowestAbsences,
            'highestAbsences' => $highestAbsences,
            // 'students' => $students,
            'students' => $data,

        ]);
    }
}
