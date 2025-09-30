<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
   
    function registrar(Request $request) 
    {
        $dados = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $dados['password'] = bcrypt($dados['password']);
        $dados['picture'] = 'https://cdn0.iconfinder.com/data/icons/seo-web-4-1/128/Vigor_User-Avatar-Profile-Photo-02-1024.png';
        $dados['status'] = 'active';
        $dados['enabled'] = true;

        $usuario = User::create($dados);

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuário registrado com sucesso.',
            'user' => $usuario,
            'token' => $token
        ], 201);
    }

    function login(Request $request)
    {
        $credenciais = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $usuario = User::where('email', $credenciais['email'])->first();

        if (!$usuario || !\Hash::check($credenciais['password'], $usuario->password)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'user' => $usuario,
            'token' => $token
        ]);
    }


    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

function fotoUpload(Request $request)
{
    // Verifique se o usuário está autenticado
    $usuario = $request->user();
    
    if (!$usuario) {
        return response()->json([
            'message' => 'Usuário não autenticado'
        ], 401);
    }

    $request->validate([
        'picture' => 'required|image|mimes:jpg,jpeg,png|max:10120'
    ]);

    try {
        // Verifique se o arquivo foi enviado corretamente
        if (!$request->hasFile('picture')) {
            return response()->json([
                'message' => 'Nenhuma imagem foi enviada'
            ], 400);
        }

        $file = $request->file('picture');
        
        // Verifique se o upload foi bem-sucedido
        if (!$file->isValid()) {
            return response()->json([
                'message' => 'Arquivo inválido'
            ], 400);
        }

        $path = $file->store('pictures', 'public');

        // Atualize o usuário
        $usuario->update(['picture' => $path]);

        return response()->json([
            'message' => 'Foto enviada com sucesso.',
            'picture_url' => asset('storage/' . $path)
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erro ao fazer upload: ' . $e->getMessage()
        ], 500);
    }
}

    function desativarConta(Request $request)
    {
        $usuario = $request->user();
        $usuario->update(['enabled' => false, 'status' => 'inactive']);

        return response()->json(['message' => 'Conta desativada com sucesso.']);
    }

    function perfil(Request $request)
    {
        return response()->json($request->user());
    }

    function editar(Request $request)
    {
        $usuario = $request->user();

        $dados = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:6|confirmed'
        ]);

        if (!empty($dados['password'])) {
            $dados['password'] = bcrypt($dados['password']);
        } else {
            unset($dados['password']);
        }

        $usuario->update($dados);

        return response()->json([
            'message' => 'Dados atualizados com sucesso.',
            'user' => $usuario
        ]);
    }
}
