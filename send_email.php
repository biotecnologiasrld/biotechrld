<?php
header('Content-Type: application/json');

// Validar que los datos fueron enviados por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Validar que los campos existen y no están vacíos
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$tel = isset($_POST['tel']) ? trim($_POST['tel']) : '';
$correo = isset($_POST['mail']) ? trim($_POST['mail']) : '';
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

if (empty($nombre) || empty($tel) || empty($correo) || empty($mensaje)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
    exit;
}

// Validar email
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Correo no válido']);
    exit;
}

// Datos del correo
$destinatario = 'kromero@rld.mx'; // Cambia esto por tu correo real
$asunto = 'Nuevo mensaje de contacto desde blog';
$contenido = "Nombre: $nombre\n";
$contenido .= "Teléfono: $tel\n";
$contenido .= "Correo: $correo\n";
$contenido .= "Mensaje:\n$mensaje\n";

$encabezados = "From: $nombre <$correo>\r\n";
$encabezados .= "Reply-To: $correo\r\n";
$encabezados .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Enviar el correo
if (mail($destinatario, $asunto, $contenido, $encabezados)) {
    echo json_encode(['status' => 'success', 'message' => '¡Mensaje enviado exitosamente!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Hubo un problema al enviar el mensaje.']);
}
?>
