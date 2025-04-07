<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PratoRequest;
use App\Models\Prato;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CardapioController extends Controller
{

    public readonly Prato $prato;
    public function __construct()
    {
        $this->prato = new Prato;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $pratos = Prato::paginate(5, ['id', 'nome', 'descricao', 'imagem', 'categoria']);

        return response()->json($pratos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PratoRequest $request): JsonResponse
    {
        try{
            DB::beginTransaction();

            $prato = $this->prato->create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'categoria' => $request->categoria
            ]);

            if ($request->hasFile('imagem')) {
                $path = $request->file('imagem')->store('pratos', 'public');
                $prato->imagem = $path;
                $prato->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Prato adcionado com sucesso!',
                'prato' => $prato->only('id', 'nome', 'descricao', 'categoria', 'imagem')
            ]);

        } catch(Exception $e) {
            DB::rollBack();

            $errors = [
                'message' => 'Ocorreu um erro ao fazer reserva',
                'error' => $e->getMessage()
            ];

            return response()->json($errors, 400);
        }
    }

    /**
     * Display the specified resource.
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
     * Update the specified resource in storage.
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

            $prato->update($request->except('_token', '_method', 'imagem'));

            if ($request->hasFile('imagem')) {
                $path = $request->file('imagem')->store('pratos', 'public');
                $prato->imagem = $path;
                $prato->save();
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
     * Remove the specified resource from storage.
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
