<?php
// includes/header.php
// Variables que se deben definir ANTES de incluir este archivo:
//   $page_title (string) — Título de la pestaña del navegador
//   $page_css   (string) — Nombre del CSS de página sin extensión ('dashboard', 'sensores', 'login')

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ── Lógica de cambio de tema (100% PHP, sin JavaScript) ──────────────────
// Si el usuario ha pulsado el botón de tema, se recibe ?theme=light o ?theme=dark
if (isset($_GET['theme'])) {
    $_SESSION['sira_theme'] = ($_GET['theme'] === 'light') ? 'light' : 'dark';
    
    // ── Redirección inteligente: mantenemos todos los parámetros excepto 'theme' ──
    $params = $_GET;
    unset($params['theme']);
    $url_base = strtok($_SERVER['REQUEST_URI'], '?');
    $query_string = http_build_query($params);
    $url_final = $url_base . ($query_string ? '?' . $query_string : '');
    
    header("Location: $url_final");
    exit();
}

// Leemos el tema guardado en sesión (oscuro por defecto)
$tema_actual = $_SESSION['sira_theme'] ?? 'dark';
$tema_opuesto = ($tema_actual === 'dark') ? 'light' : 'dark';
$tema_icono   = ($tema_actual === 'dark')  ? '☀️ Claro' : '🌙 Oscuro';

$page_title = $page_title ?? "SIRA";
$page_css   = $page_css   ?? null;

// ── Ruta base dinámica (100% portable: Docker, XAMPP Windows/Linux, subdirectorio, raíz) ──
// En Windows, __DIR__ usa backslashes (\) y DOCUMENT_ROOT puede usar forward slashes (/).
// Normalizamos ambos a forward slashes antes de comparar para que str_replace funcione.
$_doc_root  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$_front_dir = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
$base_url   = str_replace($_doc_root, '', $_front_dir);
// Si está en la raíz del dominio, $base_url queda vacío — correcto (ej: localhost/)
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?= $tema_actual ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Auto-refresh para el Dashboard (30s) -->
    <meta http-equiv="refresh" content="30">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- CSS Base: Variables globales, reset y componentes compartidos -->
    <link rel="stylesheet" href="<?= $base_url ?>/css/style.css">
    <!-- CSS específico de esta página -->
    <?php if ($page_css): ?>
        <link rel="stylesheet" href="<?= $base_url ?>/css/<?= htmlspecialchars($page_css) ?>.css">
    <?php endif; ?>
</head>
<body>

<nav>
    <a href="<?= $base_url ?>/dashboard.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 8px;">
        <h2 style="margin: 0;">SIRA 🌱 <span style="font-weight:300; font-size:0.9rem; color:var(--color-text-muted);">| Gestión Dinámica</span></h2>
    </a>
    <div class="nav-actions">
        <!-- Botón de tema: siempre visible, preservando parámetros de la URL -->
        <?php
            $params_tema = $_GET;
            $params_tema['theme'] = $tema_opuesto;
            $toggle_url = '?' . http_build_query($params_tema);
        ?>
        <a href="<?= $toggle_url ?>" class="theme-toggle" title="Cambiar a modo <?= $tema_opuesto ?>">
            <?= $tema_icono ?>
        </a>

        <!-- Botón de Panel Global (Solo Admin/Root) -->
        <?php if (isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'], ['admin', 'root'])): ?>
            <a href="<?= $base_url ?>/dashboard.php" class="global-btn" title="Volver al Panel de Gestión Global">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Panel Global
            </a>
        <?php endif; ?>

        <!-- Cerrar sesión: solo si el usuario está logueado -->
        <?php if (isset($_SESSION['jwt_token'])): ?>
            <a href="<?= $base_url ?>/logout.php" class="logout-btn">Cerrar Sesión</a>
        <?php endif; ?>
    </div>
</nav>
