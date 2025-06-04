<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Controller responsável pelo gerenciamento de usuários
 */
class UserController extends Controller
{
    public readonly User $user;

    /**
     * Constructor - Inicializa uma nova instância do modelo User
     */
    public function __construct()
    {
        $this->user = new User;
    }

    /**
     * Lista todos os usuários
     *
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * Lista de usuários com os campos:
     * - id
     * - name
     * - email
     * - phone
     * - role
     */
    public function index(): JsonResponse
    {
        $users = $this->user->orderBy('id', 'DESC')->get(['id', 'name', 'email', 'phone', 'role']);
        return response()->json($users, 200);
    }

    /**
     * Cadastra um novo usuário
     *
     * @param UserRequest $request Dados do usuário
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - message: Mensagem de sucesso
     * - user: Dados do usuário criado
     *
     * Funcionalidades:
     * - Criptografa senha
     * - Define role (admin/user)
     * - Envia email de verificação
     *
     * @throws Exception Em caso de erro no cadastro
     */
    public function store(UserRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $role = User::isAdmin($request->email) ? 'admin' : 'user';

            $user = $this->user->create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'role' => $role,
            ]);

            $user->sendEmailVerificationNotification();

            DB::commit();

            return response()->json([
                'message' => 'Usuário cadastrado com sucesso',
                'user' => $user->only(['id', 'name', 'email', 'phone', 'role']),
            ], 201);

        } catch(Exception $e) {
            DB::rollBack();

            $errors = [
                'message' => 'Usuário não cadastrado',
                'errors' => [
                    'exception' => $e->getMessage(),
                ]
            ];

            return response()->json($errors, 400);
        }
    }

    /**
     * Exibe detalhes de um usuário específico
     *
     * @param string $id ID do usuário
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * Dados do usuário:
     * - id
     * - name
     * - email
     * - phone
     * - role
     *
     * Em caso de não encontrado:
     * - message: Usuário não encontrado
     */
    public function show(String $id): JsonResponse
    {
        $user = $this->user->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado'
            ], 404);
        }

        return response()->json($user->only(['id', 'name', 'email', 'phone', 'role']), 200);
    }

    /**
     * Atualiza dados de um usuário
     *
     * @param Request $request Novos dados do usuário
     * @param string $id ID do usuário
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - message: Mensagem de sucesso
     * - user: Dados atualizados
     *
     * Validações:
     * - Nome obrigatório
     * - Telefone válido (BR)
     *
     * @throws ValidationException Em caso de dados inválidos
     * @throws Exception Em caso de erro na atualização
     */
    public function update(Request $request, String $id): JsonResponse
    {
        $user = $this->user->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        try {
            DB::beginTransaction();

            $request->validate([
                'name' => 'required',
                'phone' => 'required|phone:br'
            ], [
                'name.required' => 'Nome é obrigatório',
                'phone.required' => 'Telefone é obrigatório',
                'phone.phone' => 'Insira um telefone válido'
            ]);

            $user->update([
                'name' => $request->name,
                'phone' => $request->phone
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Informações alteradas com sucesso',
                'user' => $user->only(['id', 'name', 'email', 'phone']),
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();

            $errors = collect($e->validator->errors()->all());

            return response()->json([
                'message' => 'Erro ao alterar informações',
                'error' => $errors
            ], 422);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao alterar informações',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove um usuário
     *
     * @param string $id ID do usuário
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - message: Confirmação da remoção
     *
     * Em caso de erro:
     * - message: Mensagem de erro
     * - error: Detalhes do erro
     *
     * @throws Exception Em caso de erro na remoção
     */
    public function destroy(String $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        try {
            $user->delete();

            return response()->json(['message' => 'Usuário deletado com sucesso'], 200);

        } catch(Exception $e) {
            return response()->json([
                'message' => 'Erro ao deletar usuário',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
