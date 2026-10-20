<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Restablecer Contraseña - ITINEWS</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #691c32 0%, #8b2d4a 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .button {
            display: inline-block;
            background: #225b4e;
            color: white !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-img {
            max-width: 120px;
            height: auto;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .logo-text {
            color: #225b4e;
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
        }
        .url-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            word-break: break-all;
            font-family: monospace;
            font-size: 14px;
            border-left: 4px solid #225b4e;
        }
        .saludo {
            font-size: 16px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Restablecer Contraseña</h1>
        </div>
        
        <div class="content">
            <div class="logo">
                <img src="{{ $message->embed(public_path('img/logo.jpg')) }}" alt="Logo ITINEWS" class="logo-img">
            </div>
            
            <p class="saludo">¡Hola!</p>
            
            <p>Has solicitado restablecer tu contraseña para el Portal ITINEWS. Haz clic en el siguiente botón para crear una nueva contraseña:</p>
            
            <div style="text-align: center;">
                <a href="{{ $actionUrl }}" class="button">
                    Restablecer Contraseña
                </a>
            </div>
            
            <p>Este enlace de restablecimiento de contraseña expirará en <strong>60 minutos</strong>.</p>
            
            <p>Si tienes problemas para hacer clic en el botón, copia y pega la siguiente URL en tu navegador:</p>
            
            <div class="url-box">
                {{ $actionUrl }}
            </div>
            
            <p>Si no solicitaste un restablecimiento de contraseña, no es necesario realizar ninguna otra acción.</p>
            
            <p>Saludos,<br>
            <strong>Equipo ITINEWS</strong><br>
            Universidad Politécnica del Valle de Toluca</p>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 Portal ITINEWS - Universidad Politécnica del Valle de Toluca</p>
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>