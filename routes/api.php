<?php

use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;


Route::middleware('auth:sanctum')->prefix('usuario')->group(function () {
    Route::post('logout', [UsuarioController::class, 'logout']);
    Route::post('desativar-conta', [UsuarioController::class, 'desativarConta']);
    Route::post('foto-upload', [UsuarioController::class, 'fotoUpload']);
    Route::post('editar', [UsuarioController::class, 'editar']);
    Route::get('perfil', [UsuarioController::class, 'perfil']);
    Route::get('usuario', function(Request $request){
        return $request->user();
    });
    Route::post('postagens', [PostController::class, 'store']);
    Route::get('postagens', [PostController::class, 'index']);
});


Route::post('usuario/login', [UsuarioController::class, 'login']);
Route::post('usuario/registrar-se', [UsuarioController::class, 'registrar']);
