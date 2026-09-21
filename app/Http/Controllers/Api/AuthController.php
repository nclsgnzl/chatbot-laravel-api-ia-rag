<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login: recibe email + password, devuelve un token de acceso (Sanctum).
     * Este token es el que el chatbot (JS) debe enviar en cada request
     * posterior como header: Authorization: Bearer {token}
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        // Cada login genera un token nuevo. Puedes nombrar el token
        // según el dispositivo/cliente (aquí usamos 'chatbot-web').
        $token = $user->createToken('chatbot-web')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user' => $user->only(['id', 'name', 'email']),
            'token' => $token,
        ]);
    }

    /**
     * Logout: revoca el token actual usado en la petición.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }

    /**
     * Devuelve el usuario autenticado actual (útil para que el frontend
     * verifique si el token sigue siendo válido).
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
