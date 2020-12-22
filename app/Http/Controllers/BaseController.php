<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @param $data
     * @param $message
     * @param int $code
     * @return JsonResponse
     */
    public function sendResponse($data, $message, $code = 200)
    {
        $response = [
            'meta' => [
                'success' => true,
                'message' => $message
            ],
            'data' => $data,
        ];
        return response()->json($response, $code);
    }


    /**
     * return error response.
     *
     * @param $error
     * @param int $code
     * @return JsonResponse
     */
    public function sendError($error, $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        return response()->json($response, $code);
    }
}
