<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminNavigationItemResource;
use App\Models\AdminNavigationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminNavigationItemController extends Controller
{
    public function getSidenav(Request $request)
    {
        //cambié esta lógica para que cachee el menú entero, así se mejora el rendimiento de la llamada
        $menu = Cache::remember('admin_sidebar', 60 * 60 * 24, function () {
        return AdminNavigationItem::whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('order', 'asc'); // Ordenamos también los hijos
            }])
            ->orderBy('order', 'asc')
            ->get();
    });

    return AdminNavigationItemResource::collection($menu);
    }

    public function store(Request $request)
    {
        Cache::forget('admin_sidebar');
        $validatedData = $request->validate([
            'label' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:admin_navigation_items,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $item = AdminNavigationItem::create($validatedData);

        return new AdminNavigationItemResource($item);
    }

    public function update(Request $request, AdminNavigationItem $item)
    {
        Cache::forget('admin_sidebar');
        $validatedData = $request->validate([
            'label' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:admin_navigation_items,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $item->update($validatedData);

        return new AdminNavigationItemResource($item);
    }

    public function destroy(AdminNavigationItem $item)
    {
        Cache::forget('admin_sidebar');
        $item->delete();

        return response()->json(['message' => 'Elemento de navegación eliminado correctamente']);
    }
}
