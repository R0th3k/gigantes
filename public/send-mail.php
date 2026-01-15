<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener y limpiar datos del formulario
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

// Validar campos requeridos
$errors = [];

if (empty($nombre)) {
    $errors[] = 'El nombre es requerido';
} elseif (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre)) {
    $errors[] = 'El nombre solo debe contener letras y espacios';
}

if (empty($email)) {
    $errors[] = 'El email es requerido';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'El email no es válido';
}

if (empty($telefono)) {
    $errors[] = 'El teléfono es requerido';
} elseif (!preg_match('/^[0-9\s\-\(\)]+$/', $telefono) || strlen(preg_replace('/\D/', '', $telefono)) < 10) {
    $errors[] = 'El teléfono debe tener al menos 10 dígitos';
}

if (empty($mensaje)) {
    $errors[] = 'El mensaje es requerido';
} elseif (strlen($mensaje) < 10) {
    $errors[] = 'El mensaje debe tener al menos 10 caracteres';
}

// Si hay errores, retornarlos
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Errores de validación', 'errors' => $errors]);
    exit;
}

// Configurar destinatario
$to = 'hola@hektor.mx,gigantesags@gmail.com';
$subject = 'Nuevo mensaje de contacto - Gigantes de Aguascalientes';

// Construir el cuerpo del mensaje
$body = "Nuevo mensaje de contacto desde el sitio web de Gigantes de Aguascalientes\n\n";
$body .= "Nombre: " . htmlspecialchars($nombre) . "\n";
$body .= "Email: " . htmlspecialchars($email) . "\n";
$body .= "Teléfono: " . htmlspecialchars($telefono) . "\n";
$body .= "Mensaje:\n" . htmlspecialchars($mensaje) . "\n";
$body .= "\n---\n";
$body .= "Enviado desde: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
$body .= "Fecha: " . date('Y-m-d H:i:s') . "\n";

// Configurar headers del correo
$headers = [];
$headers[] = 'From: ' . htmlspecialchars($nombre) . ' <' . $email . '>';
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'X-Mailer: PHP/' . phpversion();
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'Content-Transfer-Encoding: 8bit';

$headers_string = implode("\r\n", $headers);

// Intentar enviar el correo
$mail_sent = @mail($to, $subject, $body, $headers_string);

if ($mail_sent) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Mensaje enviado correctamente. Nos pondremos en contacto contigo pronto.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al enviar el mensaje. Por favor, intenta nuevamente más tarde.'
    ]);
}
?>

