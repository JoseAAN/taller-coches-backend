<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        // Número de citas pendientes para hoy
        $appointmentsToday = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('appointment_date', '>=', now())
            ->count();

        // Usuarios nuevos esta semana
        $newUsersThisWeek = User::where('created_at', '>=', Carbon::now()->startOfWeek())->count();

        // Productos sin stock
        $outOfStockProducts = Product::where('stock', '<=', 0)->count();

        // Ingresos de los últimos 7 días (para la gráfica)
        $revenueLast7Days = [];
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d/m');
            
            // Sumar el total de las facturas de ese dia
            $dailyTotal = Invoice::whereDate('created_at', $date)->sum('total');
            $data[] = (float) $dailyTotal;
        }

        return response()->json([
            'cards' => [
                'appointments_today' => $appointmentsToday,
                'new_users_week' => $newUsersThisWeek,
                'out_of_stock' => $outOfStockProducts,
                'today_revenue' => $data[count($data) - 1]
            ],
            'chart' => [
                'labels' => $labels,
                'data' => $data
            ]
        ]);
    }
}
