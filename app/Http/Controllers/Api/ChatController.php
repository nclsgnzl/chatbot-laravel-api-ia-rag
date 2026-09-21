<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Recibe la pregunta del usuario (ya autenticado vía Sanctum),
     * la reenvía al servicio Python (RAG de Taekwondo) y devuelve
     * la respuesta al frontend.
     *
     * Esta es la pieza de arquitectura clave del proyecto: Laravel
     * actúa como capa de negocio/autenticación, y NO reimplementa
     * el RAG — delega esa responsabilidad al microservicio Python
     * ya construido, comunicándose con él vía HTTP.
     */
    public function preguntar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pregunta' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Pregunta inválida',
                'errors' => $validator->errors(),
            ], 422);
        }

        $client = new Client([
            'timeout' => 30, // el RAG puede tardar unos segundos (embeddings + Claude)
        ]);

        try {
            $response = $client->post(config('services.rag_api.url') . '/preguntar', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-Key' => config('services.rag_api.key'),
                ],
                'json' => [
                    'pregunta' => $request->input('pregunta'),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return response()->json([
                'pregunta' => $request->input('pregunta'),
                'respuesta' => $data['respuesta'] ?? 'Sin respuesta del servicio RAG.',
                'usuario' => $request->user()->name,
            ]);

        } catch (RequestException $e) {
            Log::error('Error consultando el servicio RAG: ' . $e->getMessage());

            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 502;

            return response()->json([
                'message' => 'No se pudo obtener respuesta del asistente en este momento.',
                'detalle' => $status === 401
                    ? 'Credenciales inválidas con el servicio RAG.'
                    : 'El servicio RAG no está disponible o demoró demasiado en responder.',
            ], $status === 401 ? 401 : 502);
        }
    }
}
