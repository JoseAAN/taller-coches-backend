<?php
use App\Models\User;
use App\Models\Pedido;
use App\Models\Cita;
use App\Models\Factura;
use Illuminate\Support\Facades\Route;

Route::get('/prueba-usuarios', function () {

    // ==========================================
    // 1. PREPARACIÓN DE DATOS (SEEDING)
    // ==========================================

    // CORRECCIÓN AQUÍ: Usamos solo 2 argumentos.
    // 1º Array: ¿Qué buscamos? (El email es único)
    // 2º Array: Si no existe, ¿qué datos guardamos? (Nombre, pass, etc)
    
    $user1 = User::firstOrCreate(
        ['email' => 'pepe@test.com'], 
        [
            'name' => 'Pepe Cliente', 
            'password' => bcrypt('123456')
        ]
    );
    
    $user2 = User::firstOrCreate(
        ['email' => 'maria@test.com'], 
        [
            'name' => 'Maria Dueña', 
            'password' => bcrypt('123456')
        ]
    );

    // --- DATOS PARA PEPE (Usuario 1) ---
    
    // Nota: Al usar $user1->pedidos()->create(), Laravel rellena el 'user_id' solo.
    // No hace falta ponerlo manual, pero no da error si lo dejas.
    $pedidoPepe = $user1->pedidos()->create([
        'total' => 50.00, 
        'estado' => 'pagado'
    ]);
    
    // Generamos factura para ese pedido (con tu campo user_id extra)
    $pedidoPepe->factura()->create([
        'numero_factura' => 'FAC-PEPE-001', 
        'total' => 50.00,
        'user_id' => $user1->id // Aquí sí es necesario pasarlo manual
    ]);

    // Pepe pide cita
    $citaPepe = $user1->citas()->create([
        'fecha_hora' => now(), 
        'matricula' => '1234-ABC'
    ]);
    
    // Generamos factura para esa cita
    $citaPepe->factura()->create([
        'numero_factura' => 'FAC-PEPE-002', 
        'total' => 30.00, 
        'user_id' => $user1->id
    ]);


    // --- DATOS PARA MARIA (Usuario 2) ---
    $pedidoMaria = $user2->pedidos()->create([
        'total' => 120.00, 
        'estado' => 'enviado'
    ]);
    
    $pedidoMaria->factura()->create([
        'numero_factura' => 'FAC-MARIA-001', 
        'total' => 120.00, 
        'user_id' => $user2->id
    ]);


    // ==========================================
    // 2. PETICIÓN A: OBTENER FACTURAS DE UN USUARIO
    // ==========================================
    
    $usuarioConsultado = User::with(['pedidos.factura', 'citas.factura'])
                             ->where('email', 'pepe@test.com')
                             ->first();

    $facturasDePepe = [];

    // Recopilamos facturas de sus pedidos
    if ($usuarioConsultado) { // Pequeña seguridad por si borras la BD y no hay usuario
        foreach ($usuarioConsultado->pedidos as $pedido) {
            if ($pedido->factura) {
                $facturasDePepe[] = [
                    'origen' => 'Tienda (Pedido #' . $pedido->id . ')',
                    'numero' => $pedido->factura->numero_factura,
                    'total'  => $pedido->factura->total
                ];
            }
        }

        // Recopilamos facturas de sus citas
        foreach ($usuarioConsultado->citas as $cita) {
            if ($cita->factura) {
                $facturasDePepe[] = [
                    'origen' => 'Taller (Cita ' . $cita->matricula . ')',
                    'numero' => $cita->factura->numero_factura,
                    'total'  => $cita->factura->total
                ];
            }
        }
    }


    // ==========================================
    // 3. PETICIÓN B: SABER DE QUIÉN ES UNA FACTURA
    // ==========================================

    $facturaPerdida = Factura::where('numero_factura', 'FAC-MARIA-001')->first();
    
    $datosFacturaPerdida = null;

    if ($facturaPerdida) {
        $origen = $facturaPerdida->facturable; 
        $dueno = $facturaPerdida->user; // ¡TRUCO! Como añadimos la relación directa user(), es más fácil
        
        $datosFacturaPerdida = [
            'factura_encontrada' => $facturaPerdida->numero_factura,
            'tipo_origen' => class_basename($origen),
            'nombre_usuario' => $dueno->name,
            'email_usuario' => $dueno->email
        ];
    }


    // ==========================================
    // RESULTADO
    // ==========================================
    return [
        '--- CASO 1: LAS FACTURAS DE PEPE ---' => $facturasDePepe,
        '--- CASO 2: ¿DE QUIÉN ES LA FACTURA FAC-MARIA-001? ---' => $datosFacturaPerdida
    ];
});