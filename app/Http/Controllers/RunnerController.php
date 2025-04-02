<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class RunnerController extends Controller
{
    public function runPhp(): JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $code = $validator->validate('code');

        file_put_contents('/var/www/code/script.php', $code);

        $response = Http::post("http://nginx:80/run.php");

        return response()->json([
            'output' => $response->body()
        ]);
    }

    public function runPython()
    {
        $validator = Validator::make(request()->all(), [
            'code' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->fails()]);
        }

        $code = $validator->validate('code');

        file_put_contents('/var/www/code/script.py', $code);

        dd($code);
    }
}
