<?php
/**
 * ============================================================
 *  HEIMDALL ERP — Instalador Web
 * ============================================================
 *  ⚠️  ATENÇÃO: DELETE ESTE ARQUIVO APÓS A INSTALAÇÃO!
 *      Acesse /install.php e clique em "Excluir instalador"
 * ============================================================
 */

// Auto-detectar o caminho do Laravel (funciona tanto em public/ quanto em public_html/)
$homeDir   = dirname(__DIR__);
$basePath  = null;
$candidates = array_merge(
    glob($homeDir . '/*/site_heimdall', GLOB_ONLYDIR) ?: [],
    [$homeDir . '/site_heimdall', $homeDir]
);
foreach ($candidates as $dir) {
    if (file_exists($dir . '/artisan')) { $basePath = $dir; break; }
}
if (!$basePath) { $basePath = $homeDir; } // fallback

$envFile  = $basePath . '/.env';
$step     = $_POST['step'] ?? 'form';
$logs     = [];
$errors   = [];

// ── helpers ──────────────────────────────────────────────────
function runCmd(string $cmd, string $cwd): array {
    $out  = [];
    $code = 0;
    $full = sprintf('cd %s && %s 2>&1', escapeshellarg($cwd), $cmd);
    exec($full, $out, $code);
    return ['out' => implode("\n", $out), 'code' => $code];
}

function artisan(string $args, string $base): array {
    return runCmd(PHP_BINARY . ' artisan ' . $args, $base);
}

function sysCheck(): array {
    return [
        'PHP >= 8.1'        => version_compare(PHP_VERSION, '8.1.0', '>='),
        'exec() disponível' => function_exists('exec'),
        'PDO MySQL'         => extension_loaded('pdo_mysql'),
        'OpenSSL'           => extension_loaded('openssl'),
        'Mbstring'          => extension_loaded('mbstring'),
        'Tokenizer'         => extension_loaded('tokenizer'),
        'XML'               => extension_loaded('xml'),
        '.env gravável'     => is_writable(dirname($envFile)),
    ];
}

// ── processar instalação ──────────────────────────────────────
$installResult = null;
if ($step === 'install') {
    $dbHost  = trim($_POST['db_host']  ?? '127.0.0.1');
    $dbPort  = trim($_POST['db_port']  ?? '3306');
    $dbName  = trim($_POST['db_name']  ?? '');
    $dbUser  = trim($_POST['db_user']  ?? '');
    $dbPass  = trim($_POST['db_pass']  ?? '');
    $appUrl  = rtrim(trim($_POST['app_url'] ?? 'https://seudominio.com.br'), '/');
    $doSeed  = isset($_POST['do_seed']);

    // Validação básica
    if (!$dbName || !$dbUser) {
        $errors[] = 'Preencha o banco de dados e o usuário!';
        $step = 'form';
    } else {
        // Gerar APP_KEY
        $appKey = 'base64:' . base64_encode(random_bytes(32));

        // Criar .env
        $env = <<<ENV
APP_NAME="Heimdall ERP"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL={$appUrl}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPass}

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
ENV;
        file_put_contents($envFile, $env);
        $logs[] = ['label' => 'Arquivo .env criado', 'ok' => true, 'out' => "APP_KEY={$appKey}"];

        // Composer install (se vendor não existir)
        if (!is_dir($basePath . '/vendor')) {
            $phpBin = PHP_BINARY;
            $composerBin = file_exists('/usr/local/bin/composer') ? '/usr/local/bin/composer' : 'composer';
            
            // Tenta rodar usando o PHP atual para garantir a versão correta
            $r = runCmd("{$phpBin} {$composerBin} install --no-dev --optimize-autoloader --no-interaction", $basePath);
            
            // Se falhar, tenta rodar o comando direto
            if ($r['code'] !== 0) {
                $r = runCmd("{$composerBin} install --no-dev --optimize-autoloader --no-interaction", $basePath);
            }
            
            // Se ainda falhar, baixa o composer.phar localmente e executa
            if ($r['code'] !== 0) {
                $logs[] = ['label' => 'Aviso: Composer global falhou, tentando baixar composer.phar...', 'ok' => true, 'out' => $r['out']];
                @copy('https://getcomposer.org/installer', $basePath . '/composer-setup.php');
                if (file_exists($basePath . '/composer-setup.php')) {
                    runCmd("{$phpBin} composer-setup.php --quiet", $basePath);
                    @unlink($basePath . '/composer-setup.php');
                }
                
                if (file_exists($basePath . '/composer.phar')) {
                    $r = runCmd("{$phpBin} composer.phar install --no-dev --optimize-autoloader --no-interaction", $basePath);
                    @unlink($basePath . '/composer.phar');
                }
            }
            
            $logs[] = ['label' => 'composer install', 'ok' => $r['code'] === 0, 'out' => $r['out']];
        }

        // Migrations
        $r = artisan('migrate --force', $basePath);
        $logs[] = ['label' => 'php artisan migrate', 'ok' => $r['code'] === 0, 'out' => $r['out']];

        // Seeders (opcional)
        if ($doSeed) {
            $r = artisan('db:seed --force', $basePath);
            $logs[] = ['label' => 'php artisan db:seed', 'ok' => $r['code'] === 0, 'out' => $r['out']];
        }

        // Storage link
        $r = artisan('storage:link', $basePath);
        $logs[] = ['label' => 'php artisan storage:link', 'ok' => $r['code'] === 0, 'out' => $r['out']];

        // Cache
        artisan('config:cache',  $basePath);
        artisan('route:cache',   $basePath);
        artisan('view:cache',    $basePath);
        $logs[] = ['label' => 'Cache otimizado', 'ok' => true, 'out' => 'config:cache, route:cache, view:cache executados.'];

        $installResult = empty(array_filter($logs, fn($l) => !$l['ok'])) ? 'success' : 'partial';
    }
}

// ── deletar instalador ────────────────────────────────────────
if ($step === 'delete') {
    @unlink(__FILE__);
    header('Location: /');
    exit;
}

$checks = sysCheck();
$allOk  = !in_array(false, $checks, true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Heimdall ERP — Instalador</title>
<style>
  :root {
    --bg: #0d0f1a; --card: #151929; --border: #1e2640;
    --accent: #6366f1; --accent2: #818cf8; --green: #22c55e;
    --red: #ef4444; --yellow: #f59e0b; --text: #e2e8f0; --muted: #94a3b8;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
  .wrapper { width: 100%; max-width: 780px; }
  .logo { text-align: center; margin-bottom: 2rem; }
  .logo h1 { font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, var(--accent), var(--accent2)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
  .logo p { color: var(--muted); font-size: .9rem; margin-top: .4rem; }
  .card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 2rem; margin-bottom: 1.5rem; }
  .card h2 { font-size: 1.1rem; font-weight: 700; margin-bottom: 1.2rem; display: flex; align-items: center; gap: .5rem; }
  /* checks */
  .check-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
  .check-item { display: flex; align-items: center; gap: .5rem; font-size: .88rem; padding: .4rem .6rem; border-radius: 8px; background: rgba(255,255,255,.03); }
  .badge { width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .7rem; flex-shrink: 0; }
  .badge.ok  { background: rgba(34,197,94,.2);  color: var(--green); }
  .badge.err { background: rgba(239,68,68,.2);   color: var(--red); }
  /* form */
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .form-group { display: flex; flex-direction: column; gap: .4rem; }
  .form-group.full { grid-column: 1/-1; }
  label { font-size: .82rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
  input[type=text], input[type=password], input[type=url] {
    background: var(--bg); border: 1px solid var(--border); border-radius: 8px;
    color: var(--text); padding: .65rem 1rem; font-size: .95rem; outline: none; transition: border .2s;
  }
  input:focus { border-color: var(--accent); }
  .checkbox-row { display: flex; align-items: center; gap: .6rem; cursor: pointer; font-size: .9rem; }
  input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--accent); cursor: pointer; }
  .btn { display: inline-flex; align-items: center; gap: .5rem; background: var(--accent); color: #fff; border: none; border-radius: 10px; padding: .8rem 1.8rem; font-size: 1rem; font-weight: 700; cursor: pointer; transition: opacity .2s, transform .1s; }
  .btn:hover { opacity: .85; }
  .btn:active { transform: scale(.97); }
  .btn.danger { background: var(--red); }
  .btn-row { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1.5rem; }
  /* logs */
  .log-item { border-radius: 10px; margin-bottom: .8rem; overflow: hidden; border: 1px solid var(--border); }
  .log-header { display: flex; align-items: center; gap: .7rem; padding: .7rem 1rem; font-weight: 600; font-size: .9rem; cursor: pointer; }
  .log-header.ok   { background: rgba(34,197,94,.1);  color: var(--green); }
  .log-header.err  { background: rgba(239,68,68,.1);   color: var(--red); }
  .log-body { background: #0a0c14; padding: 1rem; font-family: monospace; font-size: .8rem; color: #94a3b8; white-space: pre-wrap; max-height: 200px; overflow-y: auto; display: none; }
  .log-body.open { display: block; }
  /* alerts */
  .alert { border-radius: 10px; padding: .9rem 1.2rem; font-size: .9rem; margin-bottom: 1rem; }
  .alert.success { background: rgba(34,197,94,.12);  border: 1px solid rgba(34,197,94,.3);  color: #4ade80; }
  .alert.warning { background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.3); color: #fbbf24; }
  .alert.error   { background: rgba(239,68,68,.12);  border: 1px solid rgba(239,68,68,.3);  color: #f87171; }
  .warn-box { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.25); border-radius: 12px; padding: 1rem 1.2rem; font-size: .88rem; color: #fca5a5; margin-bottom: 1.5rem; }
  @media (max-width: 560px) { .form-grid, .check-grid { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="wrapper">
  <div class="logo">
    <h1>⚡ Heimdall ERP</h1>
    <p>Assistente de Instalação Web</p>
  </div>

  <div class="warn-box">
    ⚠️ <strong>Segurança:</strong> Este arquivo concede acesso total ao servidor. <strong>Delete-o imediatamente após a instalação!</strong>
  </div>

  <?php if ($step === 'form' || $installResult === null): ?>

  <!-- VERIFICAÇÃO DO SISTEMA -->
  <div class="card">
    <h2>🔍 Verificação do Sistema</h2>
    <div class="check-grid">
      <?php foreach ($checks as $label => $ok): ?>
      <div class="check-item">
        <span class="badge <?= $ok ? 'ok' : 'err' ?>"><?= $ok ? '✓' : '✗' ?></span>
        <?= htmlspecialchars($label) ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if (!$allOk): ?>
    <div class="alert warning" style="margin-top:1rem">
      ⚠️ Alguns requisitos não foram atendidos. A instalação pode falhar.
    </div>
    <?php endif; ?>
  </div>

  <!-- ERROS -->
  <?php foreach ($errors as $err): ?>
  <div class="alert error">❌ <?= htmlspecialchars($err) ?></div>
  <?php endforeach; ?>

  <!-- FORMULÁRIO -->
  <div class="card">
    <h2>⚙️ Configuração do Banco de Dados</h2>
    <form method="POST">
      <input type="hidden" name="step" value="install">
      <div class="form-grid">
        <div class="form-group full">
          <label>URL do Site</label>
          <input type="url" name="app_url" value="https://seudominio.com.br" required>
        </div>
        <div class="form-group">
          <label>Host do Banco</label>
          <input type="text" name="db_host" value="127.0.0.1" required>
        </div>
        <div class="form-group">
          <label>Porta</label>
          <input type="text" name="db_port" value="3306" required>
        </div>
        <div class="form-group">
          <label>Nome do Banco</label>
          <input type="text" name="db_name" placeholder="ex: meuusuario_heimdall" required>
        </div>
        <div class="form-group">
          <label>Usuário do Banco</label>
          <input type="text" name="db_user" placeholder="ex: meuusuario_db" required>
        </div>
        <div class="form-group full">
          <label>Senha do Banco</label>
          <input type="password" name="db_pass" placeholder="Senha definida no cPanel">
        </div>
        <div class="form-group full">
          <label class="checkbox-row">
            <input type="checkbox" name="do_seed" checked>
            Popular banco com dados de exemplo (db:seed)
          </label>
        </div>
      </div>
      <div class="btn-row">
        <button type="submit" class="btn">🚀 Instalar Heimdall ERP</button>
      </div>
    </form>
  </div>

  <?php elseif ($installResult !== null): ?>

  <!-- RESULTADO DA INSTALAÇÃO -->
  <div class="alert <?= $installResult === 'success' ? 'success' : 'warning' ?>">
    <?= $installResult === 'success'
        ? '✅ Instalação concluída com sucesso! O Heimdall ERP está pronto.'
        : '⚠️ Instalação concluída com alguns avisos. Verifique os logs abaixo.' ?>
  </div>

  <div class="card">
    <h2>📋 Log de Instalação</h2>
    <?php foreach ($logs as $i => $log): ?>
    <div class="log-item">
      <div class="log-header <?= $log['ok'] ? 'ok' : 'err' ?>" onclick="toggleLog(<?= $i ?>)">
        <?= $log['ok'] ? '✅' : '❌' ?> <?= htmlspecialchars($log['label']) ?>
        <span style="margin-left:auto;font-size:.75rem;color:var(--muted)">clique para ver detalhes</span>
      </div>
      <div class="log-body" id="log-<?= $i ?>"><?= htmlspecialchars($log['out']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <h2>🗑️ Finalizar Instalação</h2>
    <p style="color:var(--muted);font-size:.9rem;margin-bottom:1rem">
      Por segurança, delete este arquivo imediatamente após confirmar que o site funciona.
    </p>
    <div class="btn-row">
      <a href="/" class="btn">🏠 Ir para o Site</a>
      <form method="POST" onsubmit="return confirm('Tem certeza? O instalador será deletado!')">
        <input type="hidden" name="step" value="delete">
        <button type="submit" class="btn danger">🗑️ Excluir Instalador</button>
      </form>
    </div>
  </div>

  <?php endif; ?>
</div>

<script>
function toggleLog(i) {
  const el = document.getElementById('log-' + i);
  el.classList.toggle('open');
}
</script>
</body>
</html>
