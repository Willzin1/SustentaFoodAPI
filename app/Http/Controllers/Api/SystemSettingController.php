<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemSettingController extends Controller
{
    public function getMaxCapacity(): JsonResponse
    {
        return response()->json([
            'capacidade_maxima' => SystemSetting::getValue('capacidade_maxima', 80)
        ]);
    }

    public function changeMaxCapacity(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'capacidade_maxima' => 'required|integer|min:1|max:160'
        ], [
            'capacidade_maxima.required' => 'Informe a capacidade',
            'capacidade_maxima.integer' => 'Somente números inteiros são válidos',
            'capacidade_maxima.min' => 'No mínimo :min',
            'capacidade_maxima.max' => 'No máximo :max'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        SystemSetting::setValue(
            'capacidade_maxima',
            $request->capacidade_maxima,
            'Capacidade máxima de pessoas no restaurante'
        );

        return response()->json([
            'message' => 'Capacidade máxima atualizada com sucesso',
            'capacidade_maxima' => $request->capacidade_maxima
        ]);
    }

    public function pauseReservations(): JsonResponse
    {
        SystemSetting::setValue(
            'reservas_pausadas',
            'true',
            'Controla se as reservas estão pausadas ou não'
        );

        return response()->json([
            'message' => 'Reservas pausadas com sucesso',
            'reservas_pausadas' => true
        ]);
    }

    public function unpauseReservations(): JsonResponse
    {
        SystemSetting::setValue(
            'reservas_pausadas',
            'false',
            'Controla se as reservas estão pausadas ou não'
        );

        return response()->json([
            'message' => 'Reservas retomadas com sucesso',
            'reservas_pausadas' => false
        ]);
    }

    public function getSettings(): JsonResponse
    {
        return response()->json([
            'reservas_pausadas' => SystemSetting::getValue('reservas_pausadas', 'false'),
            'capacidade_maxima' => SystemSetting::getValue('capacidade_maxima', 80)
        ]);
    }
}