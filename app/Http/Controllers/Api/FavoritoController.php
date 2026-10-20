<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorito;
use App\Models\Revista;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritoController extends Controller
{
    /**
     * Listar favoritos del usuario autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([], 401);
        }

        $favoritos = Favorito::with('revista')
            ->where('user_id', $userId)
            ->whereHas('revista', fn ($q) => $q->where('activo', true))
            ->latest()
            ->get();

        return response()->json($favoritos);
    }

    /**
     * Marcar una revista como favorita.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $validated = $request->validate([
            'revista_id' => ['required', 'exists:revistas,id'],
        ]);

        $favorito = Favorito::firstOrCreate([
            'user_id' => $userId,
            'revista_id' => $validated['revista_id'],
        ]);

        return response()->json([
            'success' => true,
            'favorito' => $favorito,
        ]);
    }

    /**
     * Eliminar una revista de favoritos.
     */
    public function destroy(int|string $revistaId): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        Favorito::where('user_id', $userId)
            ->where('revista_id', $revistaId)
            ->delete();

        return response()->json(['success' => true]);
    }
}
