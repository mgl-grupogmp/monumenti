<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');
function result(int $status, string $title, string $text): void {
    http_response_code($status);
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.$title.' | Monumenti</title><link rel="stylesheet" href="/assets/site.css"><main class="intro"><small>Monumenti by Jemuti</small><h1>'.$title.'</h1><p>'.$text.'</p><p><a href="mailto:info@monumentibyjemuti.com">info@monumentibyjemuti.com</a></p><a href="/haz-tu-pedido/">Volver a encargos</a> · <a href="/">Inicio</a></main></html>';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    result(405,'Consultas','Utiliza el formulario de encargos para contactar con el taller.');
}
function field(string $name): string {
    $value = $_POST[$name] ?? '';
    return is_string($value) ? trim($value) : '';
}
$name = field('nombre');
$email = field('correo');
$phone = field('telefono');
$message = field('mensaje');
if (field('empresa_web') !== '' || field('acepto') !== 'si' || $name === '' || strlen($name)>480 || strlen($phone)>160 || $message === '' || strlen($message)>20000 || strlen($email)>254 || preg_match('/[\r\n]/',$email) || !filter_var($email,FILTER_VALIDATE_EMAIL)) {
    result(422,'Revisa la consulta','Vuelve al formulario y comprueba tu nombre, correo, mensaje y consentimiento.');
}
// Keep only a short-lived per-address submission timestamp outside the web root.
$lock = fopen(sys_get_temp_dir().'/monumenti-'.hash('sha256',__FILE__.($_SERVER['REMOTE_ADDR'] ?? '')).'.lock','c+');
if (!$lock || !flock($lock,LOCK_EX)) result(503,'Formulario temporalmente ocupado','Puedes escribir directamente al taller.');
if (time() - (int)stream_get_contents($lock) < 60) result(429,'Consulta reciente','Espera un minuto antes de volver a enviar.');
$sent = function_exists('mail') && @mail('info@monumentibyjemuti.com','Consulta web Monumenti',"Nombre: $name\nCorreo: $email\nTeléfono: $phone\n\n$message", "From: Monumenti <info@monumentibyjemuti.com>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8");
if ($sent) { rewind($lock); ftruncate($lock,0); fwrite($lock,(string)time()); }
fclose($lock);
if (!$sent) result(503,'El envío no se ha completado','Puedes enviar tu consulta al taller por correo electrónico.');
result(200,'Gracias por tu consulta','El servidor ha aceptado tu mensaje para su envío al taller.');
