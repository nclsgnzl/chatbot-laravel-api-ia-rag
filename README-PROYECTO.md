# 🥋 RAG Chat API — Laravel 8 + Python RAG

API REST en **Laravel 8 (PHP 7.4)** con autenticación por tokens (**Sanctum**), que actúa
como capa de negocio y autenticación frente a un chatbot web (HTML/JS), delegando la
inteligencia del RAG al microservicio Python construido previamente
(`asistente-taekwondo`).

## 🏗️ Arquitectura

```
Chatbot (HTML/JS) — public/chatbot.html
        │  1. POST /api/login (email + password)
        │     ← recibe un token (Sanctum)
        │
        │  2. POST /api/chat/preguntar
        │     Header: Authorization: Bearer {token}
        ▼
API Laravel 8 (este proyecto)
  - Valida el token (auth:sanctum)
  - Valida la pregunta (Form Request / Validator)
  - Reenvía la pregunta al servicio Python vía Guzzle (HTTP interno)
        │
        ▼
Servicio Python (FastAPI) — el proyecto RAG ya construido
  - Retrieval en ChromaDB + generación con Claude
  - Devuelve la respuesta
        │
        ▼
API Laravel devuelve la respuesta al chatbot
```

**Por qué esta arquitectura y no reimplementar el RAG en PHP:** separa responsabilidades.
Laravel se encarga de lo que sabe hacer bien (auth, validación, lógica de negocio,
integración con el resto de un sistema/producto real), y el RAG sigue viviendo en Python,
donde el ecosistema de IA (embeddings, vector stores, SDKs de LLMs) es más maduro. Es el
mismo patrón de "microservicios" que usarías en un sistema real donde distintas partes
del stack requieren lenguajes/herramientas distintas.

## 🛠️ Qué se usó

| Pieza | Herramienta | Para qué |
|---|---|---|
| Framework | Laravel 8 | Estructura de la API, routing, ORM |
| Lenguaje | PHP 7.4 | Requisito del proyecto |
| Autenticación | Laravel Sanctum | Tokens de acceso (API tokens, no OAuth completo) |
| Cliente HTTP | GuzzleHttp | Laravel → Python (llamada interna al RAG) |
| Base de datos | SQLite | Simplicidad para correr local sin instalar MySQL |
| Frontend | HTML + JS vanilla | Chatbot simple, sin frameworks JS |
| CORS | fruitcake/laravel-cors (incluido en Laravel 8) | Permitir llamadas desde el navegador |

## 📁 Endpoints

| Método | Ruta | Protegido | Descripción |
|---|---|---|---|
| POST | `/api/login` | No | Recibe `email` + `password`, devuelve un token |
| POST | `/api/logout` | Sí | Revoca el token actual |
| GET | `/api/me` | Sí | Devuelve el usuario autenticado |
| POST | `/api/chat/preguntar` | Sí | Recibe `pregunta`, la reenvía al RAG, devuelve `respuesta` |

Todas las rutas protegidas requieren el header:
```
Authorization: Bearer {token}
```

## 🚀 Cómo correrlo localmente

### 1. Instalar dependencias

Este proyecto se descargó con el código fuente completo, pero **sin la carpeta `vendor/`**
(así se distribuyen los proyectos Laravel — no se sube al repositorio). Necesitas
Composer instalado y conexión a internet para bajar las librerías:

```bash
composer install
```

### 2. Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

Abre el `.env` y confirma/ajusta:
```
RAG_API_URL=http://127.0.0.1:8000
RAG_API_KEY=la_misma_clave_que_usaste_en_el_proyecto_python
```

**Importante:** `RAG_API_KEY` debe ser idéntica al `APP_API_KEY` que configuraste en el
`.env` de tu proyecto `asistente-taekwondo` (Python) — si no coinciden, el RAG rechazará
las peticiones de Laravel con error 401.

### 3. Base de datos (SQLite)

El archivo `database/database.sqlite` no se sube al repositorio (está en `.gitignore`,
junto con el `.env`, para no versionar datos locales). Créalo vacío y corre las
migraciones y el seeder (que crea un usuario de prueba):

```bash
touch database/database.sqlite
php artisan migrate --seed
```

Esto crea el usuario:
- **email:** `demo@taekwondo.test`
- **password:** `password123`

(ya está precargado en `public/chatbot.html` para probar rápido)

### 4. Levantar los dos servicios

**Servicio Python (RAG)** — en la carpeta de tu proyecto `asistente-taekwondo`:
```bash
cd src
uvicorn api:app
```
Queda corriendo en `http://127.0.0.1:8000`

**API Laravel** — en la carpeta de este proyecto, en **otro puerto** (8001, para no
chocar con el servicio Python):
```bash
php artisan serve --port=8001
```

### 5. Probar el chatbot

Abre en el navegador:
```
http://127.0.0.1:8001/chatbot.html
```

Inicia sesión con el usuario demo, y pregunta algo sobre Taekwondo — el flujo completo
(login → token → pregunta autenticada → Laravel consulta al RAG → respuesta) debería
funcionar de punta a punta.

## 🔒 Decisiones de seguridad

- **Sanctum (tokens personales)** en vez de sesiones con cookies: apropiado para una API
  consumida por un cliente externo (el chatbot), no para una SPA del mismo dominio.
- **Rate limiting incluido por defecto** (`throttle:api` en el grupo de middleware `api`
  de Laravel) — protege la API de Laravel de abuso, en capa adicional a la protección
  que ya existe en el servicio Python.
- **Separación de credenciales**: la API key del RAG (`RAG_API_KEY`) vive solo en el
  backend de Laravel (`.env`), nunca se expone al navegador — el chatbot solo conoce el
  token de Sanctum del usuario, no la clave del microservicio Python.
- **Validación de entrada** en ambos endpoints (login y chat) antes de procesar cualquier
  dato.

## 🔮 Próximos pasos posibles

- Registrar usuarios (actualmente solo existe el seeder de un usuario demo)
- Guardar historial de conversación por usuario (tabla `mensajes` en la base de datos)
- Manejar expiración de tokens (`expiration` en `config/sanctum.php`)
- Dockerizar ambos servicios (Laravel + Python) con `docker-compose`

## 📌 Autor

Nicolás Abarca — Full Stack Developer & AI Engineer
