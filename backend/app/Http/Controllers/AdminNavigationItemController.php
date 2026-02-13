<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminNavigationItem;
use App\Http\Resources\AdminNavigationItemResource;

class AdminNavigationItemController extends Controller
{
    public function getSidenav(Request $request)
    {
        $user = $request->user();

        $menu = AdminNavigationItem::whereNull('parent_id')
            ->where('is_active', true)
            ->with('children')
            ->orderBy('order', 'asc')
            ->get();

        return AdminNavigationItemResource::collection($menu);
    }

    public function store(Request $request)
    {
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
        $item->delete();

        return response()->json(['message' => 'Navigation item deleted successfully']);
    }
}
