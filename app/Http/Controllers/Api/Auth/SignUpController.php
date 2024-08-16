<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SignUpController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:200',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string',
        ]);



        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin',
        ]);

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'accessToken' => $token,
            'userData' => $user,
        ]);
    }
}
