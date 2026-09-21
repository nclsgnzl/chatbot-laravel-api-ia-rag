<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Crea un usuario de prueba para poder hacer login contra la API
     * y probar el chatbot sin tener que construir un registro completo.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Usuario Demo',
            'email' => 'demo@taekwondo.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
