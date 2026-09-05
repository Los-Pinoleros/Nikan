<?php
/**
 * Cargador de variables de entorno (.env)
 * El archivo .env vive en la raíz del proyecto. Soporta:
 *   CLAVE=valor
 *   CLAVE="valor con espacios"
 *   # comentarios
 *   lineas vacías
 */
function load_env($file = null) {
    if ($file === null) {
        $file = dirname(__DIR__) . '/.env';
    }
    if (!is_file($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($value === '') {
            $value = '';
        } elseif (isset($value[0]) && $value[0] === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
        }

        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

load_env();

/**
 * Lectura segura de variable de entorno con valor por defecto.
 */
function env($key, $default = '') {
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? $default;
    }
    return $val !== '' || ($val !== false && $val !== null) ? $val : $default;
}