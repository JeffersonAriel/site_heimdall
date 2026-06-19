<?php

/**
 * ============================================================
 *  HEIMDALL ERP — Bootstrap para public_html (HostGator)
 * ============================================================
 *  Este arquivo fica em public_html/index.php e detecta
 *  automaticamente onde o Laravel está instalado.
 * ============================================================
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ── Auto-detectar o caminho do Laravel ────────────────────────
$homeDir    = dirname(__DIR__);   // /home1/jeff2892
$laravelPath = null;

// Procura em subpastas comuns pelo artisan do Laravel
$candidates = array_merge(
    glob($homeDir . '/*/site_heimdall',   GLOB_ONLYDIR) ?: [],
    glob($homeDir . '/site_heimdall',     GLOB_ONLYDIR) ?: [],
    [$homeDir . '/site_heimdall']
);

foreach ($candidates as $dir) {
    if (file_exists($dir . '/artisan')) {
        $laravelPath = $dir;
        break;
    }
}

if (!$laravelPath) {
    http_response_code(500);
    echo '<h2 style="font-family:sans-serif;color:#c00">Erro 500 — Aplicação não encontrada</h2>';
    echo '<p style="font-family:sans-serif">Verifique se o repositório foi clonado corretamente pelo Git Version Control do cPanel.</p>';
    exit;
}

// ── Modo manutenção ──────────────────────────────────────────
if (file_exists($laravelPath . '/storage/framework/maintenance.php')) {
    require $laravelPath . '/storage/framework/maintenance.php';
}

// ── Iniciar aplicação ────────────────────────────────────────
require $laravelPath . '/vendor/autoload.php';

$app = require_once $laravelPath . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
