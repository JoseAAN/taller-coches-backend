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
        $outOfStockList = Product::where('stock', '<=', 0)->select('id', 'name', 'stock', 'price')->get();
        $outOfStockProductsCount = $outOfStockList->count();

        // Ingresos de los últimos 7 días (para la gráfica)
        $labels = [];
        $dataRevenue = [];
        $dataUsers = [];
        $dataAppointments = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d/m');
            
            // Ingresos
            $dailyTotal = Invoice::whereDate('created_at', $date)->sum('total');
            $dataRevenue[] = (float) $dailyTotal;

            // Usuarios nuevos
            $dailyUsers = User::whereDate('created_at', $date)->count();
            $dataUsers[] = $dailyUsers;

            // Citas
            $dailyAppointments = Appointment::whereDate('created_at', $date)->count();
            $dataAppointments[] = $dailyAppointments;
        }

        return response()->json([
            'cards' => [
                'appointments_today' => $appointmentsToday,
                'new_users_week' => $newUsersThisWeek,
                'out_of_stock' => $outOfStockProductsCount,
                'out_of_stock_list' => $outOfStockList,
                'today_revenue' => $dataRevenue[count($dataRevenue) - 1]
            ],
            'chart' => [
                'labels' => $labels,
                'datasets' => [
                    'revenue' => $dataRevenue,
                    'users' => $dataUsers,
                    'appointments' => $dataAppointments
                ]
            ]
        ]);
    }
}
