
<?php
$secret = "4dfb01702b44a3170b57abca3fd917ae7ea2613b";

// Firma de GitHub
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');

// Validación
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    exit("Acceso denegado");
}

// Deploy
$output = shell_exec('cd /home/nuevguin/domains/nikan.nuevaguineanicaragua.com/public_html && git pull origin main 2>&1');

file_put_contents("webhook.log", $output . "\n", FILE_APPEND);

echo "OK";
