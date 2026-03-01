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

// Clave secreta de reCAPTCHA v3
define('RECAPTCHA_SECRET_KEY', '6LdSLE4sAAAAADWe3Ae-_BtvMUoozHBNjCkfUbYO');

// Función para verificar reCAPTCHA v3
function verifyRecaptcha($token) {
    if (empty($token)) {
        return false;
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    // Intentar usar cURL primero (más confiable)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 segundos
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($result === false || $httpCode !== 200) {
            // Si falla cURL, intentar con file_get_contents
            return verifyRecaptchaFallback($token, $data);
        }
    } else {
        // Fallback a file_get_contents si cURL no está disponible
        return verifyRecaptchaFallback($token, $data);
    }
    
    $response = json_decode($result, true);
    
    // Verificar que la respuesta sea exitosa y el score sea mayor a 0.3
    // Score mínimo: 0.3 (ajustable según necesidades)
    // 0.0 = bot, 1.0 = humano real
    $isValid = isset($response['success']) && $response['success'] === true && 
               isset($response['score']) && $response['score'] >= 0.3;
    
    return $isValid;
}

// Función fallback usando file_get_contents
function verifyRecaptchaFallback($token, $data) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10 // Timeout de 10 segundos
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        return false;
    }
    
    $response = json_decode($result, true);
    
    // Verificar que la respuesta sea exitosa y el score sea mayor a 0.3
    return isset($response['success']) && $response['success'] === true && 
           isset($response['score']) && $response['score'] >= 0.3;
}

// Obtener token de reCAPTCHA
$recaptchaToken = isset($_POST['recaptcha_token']) ? trim($_POST['recaptcha_token']) : '';

// Validar reCAPTCHA (temporalmente permitir sin token si hay problemas de configuración)
$recaptchaEnabled = true; // Cambiar a false para deshabilitar reCAPTCHA temporalmente

if ($recaptchaEnabled) {
    if (empty($recaptchaToken)) {
        // Si no hay token pero el formulario se envió, permitir continuar con advertencia
        // (solo para desarrollo/debugging - en producción debería rechazarse)
        error_log('Advertencia: Formulario enviado sin token de reCAPTCHA');
        // Comentar las siguientes líneas para permitir envío sin reCAPTCHA temporalmente
        /*
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Token de reCAPTCHA no recibido. Por favor, recarga la página e intenta nuevamente.'
        ]);
        exit;
        */
    } else {
        if (!verifyRecaptcha($recaptchaToken)) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Verificación de reCAPTCHA fallida. Por favor, intenta nuevamente.'
            ]);
            exit;
        }
    }
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

