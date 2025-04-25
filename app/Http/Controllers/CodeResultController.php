<?php

namespace App\Http\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CodeResultController extends Controller
{
    public function get(string $id): JsonResponse
    {
        $timer = microtime(true);

        while (microtime(true) - $timer < 30) {
            $result = Cache::get($id);

            if ($result) return response()->json(['result' => $result]);

            sleep(1);
        }

        return response()->json(['result' => 'Ответ не пришел за выделенное время'], 408);
    }

    public function add(): JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'id' => 'required|string',
            'result' => 'required|string',
            'execution_time' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $validator->validate();

        Cache::put($data['id'], ['code_result' => $data['result'], 'execution_time' => $data['execution_time']], 60);

        return response()->json(['message' => "Данные успешно добавлены в Redis"]);
    }
}
