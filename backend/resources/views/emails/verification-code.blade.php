<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            background-color: #ffffff;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        table,
        td {
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

        p,
        h1,
        h2,
        h3,
        h4 {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body
    style="margin: 0; padding: 0; background-color: #ffffff; font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ffffff;">
        <tr>
            <td align="center" style="padding: 40px 10px;">
                <!-- Card Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="600"
                    style="max-width: 600px; background-color: #ffffff; border: 1px solid #eeeeee; border-radius: 8px; overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #003366; padding: 40px 20px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold; letter-spacing: 1px;">AutoClean</h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td align="left" style="padding: 40px 30px; color: #1a1a1a;">
                            <h1 style="font-size: 24px; font-weight: 700; color: #003366; margin-bottom: 20px;">
                                ¡Bienvenido, {{ $userName }}!</h1>

                            <p style="font-size: 16px; line-height: 1.5; color: #1a1a1a; margin-bottom: 25px;">
                                Utiliza el siguiente código de verificación de 6 dígitos para activar tu cuenta de forma
                                segura:
                            </p>

                            <!-- Code Box -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center"
                                        style="background-color: #f8fafc; border: 2px dashed #003366; border-radius: 8px; padding: 25px;">
                                        <p
                                            style="font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #003366; margin: 0;">
                                            {{ $code }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 15px; line-height: 1.5; color: #6c757d; margin-bottom: 30px;">
                                Si no has creado una cuenta en {{ config('app.name', 'nuestra plataforma') }}, puedes
                                ignorar este correo de forma segura. El código expirará en poco tiempo.
                            </p>

                            <p style="font-size: 16px; line-height: 1.5; color: #1a1a1a; margin: 0;">
                                Gracias por registrarte,<br>
                                <strong>El equipo de {{ config('app.name', 'AutoClean') }}</strong>
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
                        <td align="center"
                            style="padding: 30px; background-color: #ffffff; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            <p style="font-size: 14px; line-height: 1.5; color: #6c757d; margin: 0;">
                                Este es un mensaje automático. Por favor, no respondas a este correo.
                            </p>
                            <p style="font-size: 14px; line-height: 1.5; color: #6c757d; margin: 10px 0 0 0;">
                                &copy; {{ date('Y') }} {{ config('app.name', 'AutoClean') }}. Todos los derechos
                                reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
