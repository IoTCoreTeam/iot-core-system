<?php

namespace App\Http\Controllers;

abstract class Controller
{

    // base controller methods have been defined here
    protected function success($data, $message = 'OK', $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error($message = 'Error', $status = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $status);
    }
}
