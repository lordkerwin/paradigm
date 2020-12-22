<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->email)->first();
            if ($user && $user->admin) {
                if (Hash::check($request->password, $user->password)) {
                    $token = $user->createToken('authToken')->plainTextToken;
                    $response = [
                        'token' => $token,
                        'user' => [
                            'name' => $user->name,
                            'email' => $user->email,
                        ]
                    ];
                    return $this->sendResponse($response, 'Login Successful');
                }
                return $this->sendError('Username or Password incorrect.', 422);
            }
            return $this->sendError('Forbidden', 403);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in Login',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
