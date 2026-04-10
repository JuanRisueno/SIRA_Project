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
    // Redirigimos a la misma página pero sin el parámetro ?theme= en la URL
    $url_limpia = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $url_limpia");
    exit();
}

// Leemos el tema guardado en sesión (oscuro por defecto)
$tema_actual = $_SESSION['sira_theme'] ?? 'dark';
$tema_opuesto = ($tema_actual === 'dark') ? 'light' : 'dark';
$tema_icono   = ($tema_actual === 'dark')  ? '☀️ Claro' : '🌙 Oscuro';

$page_title = $page_title ?? "SIRA";
$page_css   = $page_css   ?? null;
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?= $tema_actual ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- CSS Base: Variables globales, reset y componentes compartidos -->
    <link rel="stylesheet" href="/css/style.css">
    <!-- CSS específico de esta página -->
    <?php if ($page_css): ?>
        <link rel="stylesheet" href="/css/<?= htmlspecialchars($page_css) ?>.css">
    <?php endif; ?>
</head>
<body>

<nav>
    <h2>SIRA 🌱 <span style="font-weight:300; font-size:0.9rem; color:var(--color-text-muted);">| Gestión Dinámica</span></h2>
    <div class="nav-actions">
        <!-- Botón de tema: siempre visible, en todas las páginas -->
        <a href="?theme=<?= $tema_opuesto ?>" class="theme-toggle" title="Cambiar a modo <?= $tema_opuesto ?>">
            <?= $tema_icono ?>
        </a>
        <!-- Cerrar sesión: solo si el usuario está logueado -->
        <?php if (isset($_SESSION['jwt_token'])): ?>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        <?php endif; ?>
    </div>
</nav>
