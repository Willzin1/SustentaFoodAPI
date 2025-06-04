<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Requests\PratoRequest;
use App\Models\Prato;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Controller responsável pelo gerenciamento do cardápio
 */
class CardapioController extends Controller
{
    public readonly Prato $prato;

    /**
     * Constructor - Inicializa uma nova instância do modelo Prato
     */
    public function __construct()
    {
        $this->prato = new Prato;
    }

    /**
     * Lista todos os pratos do cardápio
     *
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - paginate: Lista paginada de pratos (5 por página)
     * - pratos: Lista completa de pratos
     *
     * Permite filtrar por:
     * - Nome do prato
     * - Descrição
     * - Categoria
     *
     * Campos retornados:
     * - id
     * - nome
     * - descricao
     * - imagem
     * - categoria
     */
    public function index(): JsonResponse
    {
        $query = Prato::orderBy('id', 'DESC');
        $getDishes = $query->get();

        if (request()->has('search')) {
            $search = request('search');
            $filter = request('filter');

            switch ($filter) {
                case 'Nome':
                    $query->where('nome', 'like', "%$search%");
                    break;
                case 'Descricao':
                    $query->where('descricao', 'like', "%$search%");
                    break;
                case 'Categoria':
                    $query->where('categoria', 'like', "%$search%");
                    break;
                default:
                $query->where('nome', 'like', "%$search%")
                ->orWhere('descricao', 'like', "%$search%")
                ->orWhere('categoria', 'like', "%$search%");
            };
        };

        $pratos = $query->paginate(5, ['id', 'nome', 'descricao', 'imagem', 'categoria']);

        return response()->json(['paginate' => $pratos, 'pratos' => $getDishes], 200);
    }

    /**
     * Cadastra um novo prato no cardápio
     *
     * @param PratoRequest $request Dados do prato a ser cadastrado
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - message: Mensagem de sucesso
     * - prato: Dados do prato cadastrado
     *
     * Em caso de erro:
     * - message: Mensagem de erro
     * - error: Detalhes do erro
     *
     * @throws Exception Em caso de erro no cadastro
     */
    public function store(PratoRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $prato = $this->prato->create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'categoria' => $request->categoria,
            ]);

            if ($request->hasFile('imagem')) {
                $imgFile = $request->file('imagem');
                $slug = Str::slug($request->nome);
                $extension = $imgFile->getClientOriginalExtension();
                $filename = "{$slug}-" . uniqid() . ".{$extension}";
                $path = $request->file('imagem')->storeAs('pratos', $filename, 'public');
                $prato->update(['imagem' => $path]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Prato cadastrado com sucesso!',
                'prato' => $prato->only('id', 'nome', 'descricao', 'categoria', 'imagem'),
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao cadastrar prato',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exibe os detalhes de um prato específico
     *
     * @param string $id ID do prato
     * @return JsonResponse Retorna uma resposta JSON contendo os dados do prato:
     * - id
     * - nome
     * - descricao
     * - categoria
     * - imagem
     *
     * Em caso de não encontrado:
     * - message: Mensagem informando que o prato não foi encontrado
     */
    public function show(string $id): JsonResponse
    {
        $prato = $this->prato->find($id);

        if (! $prato) {
            return response()->json([
                'message' => 'Prato não encontrado!',
            ], 404);
        }

        return response()->json($prato->only('id', 'nome', 'descricao', 'categoria', 'imagem'), 200);
    }

    /**
     * Atualiza os dados de um prato existente
     *
     * @param PratoRequest $request Dados atualizados do prato
     * @param string $id ID do prato
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - message: Mensagem de sucesso
     * - prato: Dados atualizados do prato
     *
     * Em caso de erro:
     * - message: Mensagem de erro
     * - error: Detalhes do erro
     *
     * @throws Exception Em caso de erro na atualização
     */
    public function update(PratoRequest $request, string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $prato = $this->prato->find($id);

            if (! $prato) {
                return response()->json([
                    'message' => 'Prato não encontrado!'
                ], 404);
            }

            $prato->update($request->except('_token', '_method'));

            if ($request->hasFile('imagem')) {
                $imgFile = $request->file('imagem');
                $slug = Str::slug($request->nome);
                $extension = $imgFile->getClientOriginalExtension();
                $filename = "{$slug}-" . uniqid() . ".{$extension}";
                $path = $request->file('imagem')->storeAs('pratos', $filename, 'public');
                $prato->update(['imagem' => $path]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Prato alterado com sucesso!',
                'prato' => $prato->only('id', 'nome', 'descricao', 'categoria', 'imagem')
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            $errors = [
                'message' => 'Ocorreu um erro ao alterar reserva',
                'error' => $e->getMessage()
            ];

            return response()->json($errors, 400);
        }
    }

    /**
     * Remove um prato do cardápio
     *
     * @param string $id ID do prato a ser removido
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - message: Mensagem de sucesso
     * - prato: Dados do prato removido
     *
     * Em caso de erro:
     * - message: Mensagem de erro
     * - error: Detalhes do erro
     *
     * @throws Exception Em caso de erro na remoção
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $prato = $this->prato->find($id);

            if (! $prato) {
                return response()->json([
                    'message' => 'Prato não encontrado!'
                ], 404);
            }

            $prato->delete();

            DB::commit();

            return response()->json([
                'message' => 'Prato deletado com sucesso!',
                'prato' => $prato->only(['id', 'nome', 'descricao', 'categoria', 'imagem'])
            ], 200);
        } catch(Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Ocorreu um erro ao deletar prato',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
