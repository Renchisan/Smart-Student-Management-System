<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PredictionController extends Controller
{
    public function predictRisk()
    {
        // Step 1: Get student features
        $students = DB::table('academic_data')
            ->select('id', 'student_id', 'studytime', 'failures', 'schoolsup', 'famsup', 'paid', 'activities', 'absences', 'G1', 'G2', 'g1_g2_diff', 'g_avg')
            ->get();

        // Step 2: Prepare for Python
        $features = $students->map(function ($s) {
            return [
                'studytime' => $s->studytime,
                'failures' => $s->failures,
                'schoolsup' => $s->schoolsup,
                'famsup' => $s->famsup,
                'paid' => $s->paid,
                'activities' => $s->activities,
                'absences' => $s->absences,
                'G1' => $s->G1,
                'G2' => $s->G2,
                'G1_G2_diff' => $s->g1_g2_diff,
                'G_avg' => $s->g_avg,
            ];
        });

      // Step 3: Run Python script via STDIN using proc_open
        $jsonInput = json_encode($features);

        $descriptorspec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];

        $process = proc_open("python C:/laragon/www/jam-sms/predict_risk.py", $descriptorspec, $pipes);

        if (is_resource($process)) {
            // Send JSON input to Python's stdin
            file_put_contents(storage_path('logs/predict_input.json'), $jsonInput);

            fwrite($pipes[0], $jsonInput);
            fclose($pipes[0]);

            // Read Python's stdout and stderr
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            file_put_contents(storage_path('logs/predict_output.json'), $output);
            file_put_contents(storage_path('logs/predict_error.log'), $error);

            fclose($pipes[1]);
            fclose($pipes[2]);

            $return_value = proc_close($process);

            if ($return_value !== 0) {
                return response()->json(['error' => 'Python error', 'details' => $error], 500);
            }

            // Decode the prediction output
            $predictions = json_decode($output);

            if (!is_array($predictions)) {
                return response()->json(['error' => 'Invalid response from Python', 'raw' => $output], 500);
            }

            // Step 4: Save results to performances table
            foreach ($students as $index => $student) {
                DB::table('performances')->insert([
                    'student_id' => $student->student_id,
                    'risk' => $predictions[$index],
                    'prediction_date' => Carbon::now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json(['message' => 'Predictions saved!']);
        } else {
            return response()->json(['error' => 'Failed to start Python script'], 500);
        }

    }
}
