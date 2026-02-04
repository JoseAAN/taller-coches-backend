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
}
