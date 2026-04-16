<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>¡Vuelve a estar en stock!</title>
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
  p, h1, h2, h3, h4 {
    margin: 0;
    padding: 0;
  }
  .cta-button:hover {
    background-color: #2563eb !important;
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
              <h1 style="font-size: 24px; font-weight: 700; color: #003366; margin-bottom: 20px;">¡Buenas noticias!</h1>
              
              <p style="font-size: 16px; line-height: 1.5; color: #1a1a1a; margin-bottom: 20px;">
                El producto <strong>{{ $productName }}</strong> que estabas esperando vuelve a estar en stock.
              </p>
              
              <p style="font-size: 16px; line-height: 1.5; color: #1a1a1a; margin-bottom: 30px;">
                Puedes comprarlo ahora mismo antes de que se vuelva a agotar haciendo clic en el siguiente botón:
              </p>

              <!-- CTA Button -->
              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 35px;">
                <tr>
                  <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td align="center" bgcolor="#3b82f6" style="border-radius: 8px;">
                          <a href="{{ env('VITE_APP_URL', 'http://54.85.120.231') . '/products' }}" class="cta-button" target="_blank" style="display: inline-block; padding: 12px 24px; font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 8px; background-color: #3b82f6;">Ir a la Tienda</a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <p style="font-size: 16px; line-height: 1.5; color: #1a1a1a; margin: 0;">
                Gracias por confiar en nosotros,<br>
                <strong>{{ config('app.name') }}</strong>
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
                Este es un mensaje generado automáticamente. Por favor, no respondas a este correo.
              </p>
              <p style="font-size: 14px; line-height: 1.5; color: #6c757d; margin: 10px 0 0 0;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los enlaces pertinentes, alineados al centro.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
