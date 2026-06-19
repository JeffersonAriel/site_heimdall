<?php
/**
 * HEIMDALL — Diagnóstico e Reset Git
 * Faça upload para public_html/gitfix.php via File Manager do cPanel
 * DELETE ESTE ARQUIVO APÓS O USO!
 */

$repoPath = null;
$homeDir = dirname(__DIR__);

// Auto-detectar o repositório
$candidates = [
    $homeDir . '/repositories/site_heimdall',
    $homeDir . '/site_heimdall',
];
foreach ($candidates as $dir) {
    if (file_exists($dir . '/.git')) {
        $repoPath = $dir;
        break;
    }
}

$action = $_GET['action'] ?? 'status';
$output = '';

if ($repoPath && $action === 'reset') {
    $cmds = [
        'git -C ' . escapeshellarg($repoPath) . ' reset --hard HEAD',
        'git -C ' . escapeshellarg($repoPath) . ' clean -fd --exclude=.env --exclude=vendor',
    ];
    foreach ($cmds as $cmd) {
        exec($cmd . ' 2>&1', $out, $code);
        $output .= "$ $cmd\n" . implode("\n", $out) . "\nExit: $code\n\n";
        $out = [];
    }
}

// Status atual
exec('git -C ' . escapeshellarg($repoPath ?? $homeDir) . ' status 2>&1', $statusOut);
$status = implode("\n", $statusOut);

exec('git -C ' . escapeshellarg($repoPath ?? $homeDir) . ' log --oneline -5 2>&1', $logOut);
$log = implode("\n", $logOut);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Heimdall — Git Diagnóstico</title>
<style>
  body { font-family: monospace; background: #0d0f1a; color: #e2e8f0; padding: 2rem; }
  pre { background: #1e2640; padding: 1rem; border-radius: 8px; white-space: pre-wrap; }
  .ok { color: #22c55e; }
  .warn { color: #f59e0b; }
  .err { color: #ef4444; }
  a { display:inline-block; margin:0.5rem 0; padding:0.6rem 1.2rem; background:#6366f1; color:#fff; text-decoration:none; border-radius:6px; }
  h2 { color: #818cf8; margin-top: 1.5rem; }
</style>
</head>
<body>
<h1>🔍 Heimdall — Git Diagnóstico</h1>

<p>Repositório: <strong><?= htmlspecialchars($repoPath ?? 'NÃO ENCONTRADO') ?></strong></p>

<?php if (!$repoPath): ?>
<p class="err">❌ Repositório não encontrado! Verifique o caminho manualmente.</p>
<?php else: ?>

<h2>📋 git log (últimos 5 commits)</h2>
<pre><?= htmlspecialchars($log) ?></pre>

<h2>📋 git status</h2>
<pre><?= htmlspecialchars($status) ?></pre>

<?php if ($output): ?>
<h2>✅ Resultado do Reset</h2>
<pre class="ok"><?= htmlspecialchars($output) ?></pre>
<?php endif; ?>

<h2>🔧 Ações</h2>
<p class="warn">⚠️ O reset vai apagar arquivos não-commitados (EXCETO .env e vendor)</p>
<a href="?action=reset" onclick="return confirm('Confirma reset do repositório?')">🔄 Executar git reset --hard + clean</a>
<a href="?action=status">🔃 Atualizar Status</a>

<?php endif; ?>

<br><br>
<p class="err">⚠️ DELETE este arquivo após o uso: <code>public_html/gitfix.php</code></p>
</body>
</html>
