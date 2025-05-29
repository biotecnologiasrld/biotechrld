<?php
// Función para validar reCAPTCHA
function validar_recaptcha($token) {
    $secretKey = "cambiar"; // Reemplázala con la nueva clave secreta
    $url = "cambiar";

    $data = [
        'secret'   => $secretKey,
        'response' => $token
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $result = curl_exec($ch);
    curl_close($ch);

    if (!$result) {
        return false; // Falló la conexión con la API de Google
    }

    $response = json_decode($result, true);

    // Validar reCAPTCHA v2
    if (isset($response['success']) && $response['success'] === true) {
        return true;
    }

    // Validar reCAPTCHA v3 (mínima puntuación de confianza recomendada: 0.5)
    if (isset($response['score']) && $response['score'] >= 0.5) {
        return true;
    }

    return false;
}

// function validar_recaptcha($token) {
//     $url = "https://www.google.com/recaptcha/api/siteverify";
//     $data = [
//         'secret' => "6LcEjuoqAAAAAIwVVRPAfMXckqgXaiw7Z7XYWe-y",
//         'response' => $token
//     ];

//     $options = [
//         'http' => [
//             'header'  => "Content-type: application/x-www-form-urlencoded",
//             'method'  => 'POST',
//             'content' => http_build_query($data)
//         ]
//     ];

//     $context  = stream_context_create($options);
//     $result = curl($url, false, $context);
//     $response = json_decode($result, true);

//     return $response['success'] ?? false;
// }

// // Validar que el formulario se envió con reCAPTCHA
// if (!isset($_POST['recaptcha_token']) || !validar_recaptcha($_POST['recaptcha_token'])) {
//     echo json_encode(['status' => 'error', 'message' => 'Error de validación reCAPTCHA.']);
//     exit;
// }

// Sanitización y validación de datos
$nombre  = filter_var(trim($_POST['nombre']), FILTER_SANITIZE_STRING);
$correo  = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
$mensaje = filter_var(trim($_POST['mensaje']), FILTER_SANITIZE_STRING);

// Validación del correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || !preg_match('/\.(com|mx|com\.mx)$/', $correo)) {
    echo json_encode(['status' => 'error', 'message' => 'Correo inválido']);
    exit;
}

// // Honeypot (para evitar bots)
// if (!empty($_POST['telefono'])) {
//     exit;
// }

// Bloquear URLs en nombre y mensaje
$patronURL = '/(http:\/\/|https:\/\/|www\.|mailto:)/i';
if (preg_match($patronURL, $nombre) || preg_match($patronURL, $mensaje)) {
    echo json_encode(['status' => 'error', 'message' => 'No se permiten enlaces en el nombre o el mensaje.']);
    exit;
}

// Prevenir inyección de encabezados de email
$correo = str_replace(["\r", "\n", "%0a", "%0d"], '', $correo);
$nombre = str_replace(["\r", "\n", "%0a", "%0d"], '', $nombre);

// Configuración del correo
$to = "kromero@rld.mx";
$subject = "Nuevo mensaje de contacto de $nombre";
$headers = "From: noreply@rld.mx\r\n";
$headers .= "Reply-To: $correo\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Cuerpo del mensaje
$body = "Has recibido un nuevo mensaje de contacto.\n\n";
$body .= "Nombre: $nombre\n";
$body .= "Correo: $correo\n";
$body .= "Mensaje:\n$mensaje\n";

// Intentar enviar el correo
if (mail($to, $subject, $body, $headers)) {
    echo json_encode(['status' => 'success', 'message' => '¡Tu mensaje se ha enviado!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al enviar el mensaje.']);
}
exit;
?>