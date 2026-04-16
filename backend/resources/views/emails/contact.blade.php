<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nuevo Contacto</title>
<style>
  body {
    margin: 0;
    padding: 0;
    -webkit-text-size-adjust: 100%;
    -ms-text-size-adjust: 100%;
    background-color: #ffffff;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  }
  table, td {
    border-collapse: collapse;
    mso-table-lspace: 0pt;
    mso-table-rspace: 0pt;
  }
  img {
    border: 0;
    line-height: 100%;
    outline: none;
    text-decoration: none;
    -ms-interpolation-mode: bicubic;
  }
  p, h1, h2, h3, h4, ul, li {
    margin: 0;
    padding: 0;
  }
</style>
</head>
<body style="margin: 0; padding: 0; background-color: #ffffff; font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ffffff;">
    <tr>
      <td align="center" style="padding: 40px 10px;">
        <!-- Card Container -->
        <table border="0" cellpadding="0" cellspacing="0" width="600" style="max-width: 600px; background-color: #ffffff; border: 1px solid #eeeeee; border-radius: 8px; overflow: hidden;">
          <!-- Header -->
          <tr>
            <td align="center" style="background-color: #003366; padding: 40px 20px;">
              <img src="https://via.placeholder.com/200x50/003366/ffffff?text=LOGO" alt="Logo" style="display: block; max-width: 200px; border: 0; color: #ffffff; font-size: 20px; font-weight: bold;" />
            </td>
          </tr>
          <!-- Body -->
          <tr>
            <td align="left" style="padding: 40px 30px; color: #1a1a1a;">
              <h1 style="font-size: 24px; font-weight: 700; color: #003366; margin-bottom: 20px;">¡Tienes un nuevo mensaje!</h1>
              
              <p style="font-size: 16px; line-height: 1.5; color: #1a1a1a; margin-bottom: 20px;">
                Alguien ha rellenado el formulario de contacto con los siguientes detalles:
              </p>
              
              <!-- Details List -->
              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px;">
                <tr>
                  <td style="padding-bottom: 8px; font-size: 16px; line-height: 1.5; color: #1a1a1a;">
                    <strong>Nombre:</strong> {{ $clientName }}
                  </td>
                </tr>
                <tr>
                  <td style="font-size: 16px; line-height: 1.5; color: #1a1a1a;">
                    <strong>Email:</strong> <a href="mailto:{{ $clientEmail }}" style="color: #3b82f6; text-decoration: none;">{{ $clientEmail }}</a>
                  </td>
                </tr>
              </table>

              <!-- Message Box / Inner Card -->
              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px; background-color: #fafafa; border: 1px solid #eeeeee; border-radius: 8px;">
                <tr>
                  <td style="padding: 20px; font-size: 16px; line-height: 1.5; color: #1a1a1a;">
                    {{ $msg }}
                  </td>
                </tr>
              </table>

              <p style="font-size: 14px; line-height: 1.5; color: #6c757d; margin: 0;">
                <i>Nota: Si le das al botón de "Responder" en tu cliente de correo, tu respuesta irá directa al email introducido ({{ $clientEmail }}).</i>
              </p>
            </td>
          </tr>
          <!-- Divider -->
          <tr>
            <td style="padding: 0 30px;">
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="border-top: 1px solid #eeeeee;"></td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- Footer -->
          <tr>
            <td align="center" style="padding: 30px; background-color: #ffffff; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
              <p style="font-size: 14px; line-height: 1.5; color: #6c757d; margin: 0;">
                Este es un mensaje generado automáticamente de la web. No respondas directamente a esta copia de sistema.
              </p>
              <p style="font-size: 14px; line-height: 1.5; color: #6c757d; margin: 10px 0 0 0;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
