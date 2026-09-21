<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Servicio RAG (Python / FastAPI) - Asistente de Taekwondo
    |--------------------------------------------------------------------------
    |
    | Laravel actúa como capa intermedia: recibe la pregunta ya autenticada
    | vía Sanctum, y la reenvía a este microservicio, que es el que corre
    | el RAG (retrieval + Claude). Ver ChatController.
    |
    */

    'rag_api' => [
        'url' => env('RAG_API_URL', 'http://127.0.0.1:8000'),
        'key' => env('RAG_API_KEY'),
    ],

];
