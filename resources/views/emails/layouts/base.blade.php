<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('subject') — Rustikan</title>
    <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#F5EFE6;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

<!-- Wrapper -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F5EFE6;min-height:100vh;">
    <tr>
        <td align="center" valign="top" style="padding:32px 16px;">

            <!-- Card -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

                <!-- Header -->
                <tr>
                    <td style="background:linear-gradient(135deg,#C97420 0%,#A85D18 50%,#874915 100%);border-radius:16px 16px 0 0;padding:36px 40px 32px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center">
                                    <!-- Logo image -->
                                    <div style="margin-bottom:14px;">
                                        <img src="{{ config('app.url') }}/images/logo.png"
                                             alt="Rustikan"
                                             width="140"
                                             style="height:auto;display:inline-block;filter:brightness(0) invert(1);"
                                        />
                                    </div>
                                    <div>
                                        <span style="color:rgba(255,255,255,0.7);font-size:11px;letter-spacing:1px;text-transform:uppercase;">Productos locales de Lanzarote</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Icon badge -->
                <tr>
                    <td style="background:#FFFFFF;padding:0;" align="center">
                        <div style="margin-top:-28px;display:inline-block;background:#FFFFFF;border-radius:50%;padding:6px;box-shadow:0 4px 20px rgba(168,93,24,0.2);">
                            <div style="background:linear-gradient(135deg,#C97420,#A85D18);border-radius:50%;width:56px;height:56px;display:flex;align-items:center;justify-content:center;">
                                @yield('icon')
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="background:#FFFFFF;padding:32px 40px 40px;border-radius:0 0 16px 16px;">
                        @yield('content')
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:24px 40px 8px;" align="center">
                        <p style="margin:0 0 8px;color:#9CA3AF;font-size:12px;line-height:1.6;">
                            Este mensaje fue enviado por <strong>Rustikan</strong> · Marketplace de Lanzarote
                        </p>
                        <p style="margin:0;color:#C4A882;font-size:11px;">
                            © {{ date('Y') }} Rustikan · Todos los derechos reservados
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
