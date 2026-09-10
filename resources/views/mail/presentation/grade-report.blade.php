<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $report_title }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1e293b; line-height: 1.5;">
    <p>Hola,</p>
    <p>Adjuntamos el reporte de notas de <strong>{{ $recipient_name }}</strong>.</p>
    @if($note !== '')
    <p>{{ $note }}</p>
    @endif
    <p>El PDF va en este correo. Este envío es de demostración del módulo de presentación.</p>
    <p>Saludos.</p>
</body>
</html>
