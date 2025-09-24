<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class UsuarioController extends Controller
{
    public function registrar(Request $request) 
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

    public function login(Request $request)
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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function fotoUpload(Request $request)
    {
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
            if (!$request->hasFile('picture')) {
                return response()->json([
                    'message' => 'Nenhuma imagem foi enviada'
                ], 400);
            }

            $file = $request->file('picture');
            
            if (!$file->isValid()) {
                return response()->json([
                    'message' => 'Arquivo inválido'
                ], 400);
            }

            $path = $file->store('pictures', 'public');

            $usuario->update(['picture' => $path]);

            // Chama o método para fixar o storage link sempre que fizer upload da foto
            $this->fixarStorageLink();

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

    public function desativarConta(Request $request)
    {
        $usuario = $request->user();
        $usuario->update(['enabled' => false, 'status' => 'inactive']);

        return response()->json(['message' => 'Conta desativada com sucesso.']);
    }

    public function perfil(Request $request)
    {
        return response()->json($request->user());
    }

    public function editar(Request $request)
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

    // Novo método para corrigir o storage link no Laravel
    public function fixarStorageLink()
    {
        $storagePath = public_path('storage');
        $backupPath = public_path('storage_backup');
        $picturesPath = public_path('storage/pictures');

        $result = [];

        // 1) Veja o que tem dentro de public/storage (se existir)
        if (File::exists($storagePath)) {
            $conteudo = File::files($storagePath);
            $nomes = [];
            foreach ($conteudo as $arquivo) {
                $nomes[] = $arquivo->getFilename();
            }
            $result['conteudo_storage'] = $nomes;
        } else {
            $result['conteudo_storage'] = 'public/storage não existe.';
        }

        // 2) Se public/storage existir, for diretório e NÃO for symlink
        if (File::exists($storagePath) && File::isDirectory($storagePath) && !is_link($storagePath)) {
            $result['acao'] = 'public/storage é diretório e não é symlink. Movendo para storage_backup...';

            if (File::exists($backupPath)) {
                File::deleteDirectory($backupPath);
            }

            File::moveDirectory($storagePath, $backupPath);
        } else {
            $result['acao'] = 'public/storage já é symlink ou não existe.';
        }

        // 3) Cria o link simbólico com storage:link
        Artisan::call('storage:link');
        $result['artisan_output'] = Artisan::output();

        // 4) Lista últimos 30 arquivos em public/storage/pictures, se existir
        if (File::exists($picturesPath) && File::isDirectory($picturesPath)) {
            $todosArquivos = File::files($picturesPath);
            $ultimosArquivos = array_slice($todosArquivos, max(0, count($todosArquivos) - 30), 30);

            $ultimosNomes = [];
            foreach ($ultimosArquivos as $arquivo) {
                $ultimosNomes[] = $arquivo->getFilename();
            }
            $result['ultimos_30_pictures'] = $ultimosNomes;
        } else {
            $result['ultimos_30_pictures'] = 'public/storage/pictures não existe.';
        }

        return $result;
    }
}
