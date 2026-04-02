<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nuevo Contacto</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f5f7; padding: 30px; margin: 0;">
    <div style="background-color: #ffffff; padding: 25px 35px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-top: 5px solid #2dd4bf;">
        
        <h2 style="color: #1e293b; margin-top: 0;">¡Tienes un nuevo reporte desde la web!</h2>
        
        <p style="color: #475569; font-size: 16px;">
            Alguien ha rellenado el formulario de contacto con los siguientes detalles:
        </p>

        <ul style="color: #475569; font-size: 15px; padding-left: 20px;">
            <li><strong>Nombre:</strong> {{ $clientName }}</li>
            <li><strong>Email:</strong> {{ $clientEmail }}</li>
        </ul>

        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin: 25px 0;">
            <p style="color: #0f172a; margin: 0; font-size: 15px;">
                {{ $msg }}
            </p>
        </div>

        <p style="color: #475569; font-size: 14px;">
            <i>Nota: Si le das al botón de "Responder" en tu cliente de correo, tu respuesta irá directa a {{ $clientEmail }}.</i>
        </p>
    </div>
</body>
</html>
