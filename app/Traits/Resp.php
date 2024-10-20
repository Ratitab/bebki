<?php

namespace App\Traits;

trait Resp
{

    public function apiResponseSuccess($result, $status = 200, $code = 'success', $success = true): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => $success, 'code' => $code, 'status' => $status, 'result' => $result],$status);
    }

    public function apiResponseFail($result, $status = 400, $code = 'error', $success = false): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => $success, 'code' => $code, 'status' => $status, 'result' => ['message' => $result]],$status);
    }
}
