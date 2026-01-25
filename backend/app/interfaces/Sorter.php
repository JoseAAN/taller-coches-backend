<?php

namespace App\interfaces;

use Illuminate\Http\Request;

interface Sorter
{
    public function sort($query, Request $request, array $allowedSorts, string $defaultSort = 'created_at', string $defaultOrder = 'desc');
}
