<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: index.php"); exit(); }

$id_inv   = $_GET['id']     ?? 1;
$nombre_inv = $_GET['nombre'] ?? 'Invernadero';
$token    = $_SESSION['jwt_token'];

// Datos demo de sensores (Fase IV: se sustituirán por API real)
$sensores_demo = [
    ['tipo' => 'Temp. Aire',     'valor' => '24.5', 'unidad' => '°C',   'ubicacion' => 'Sector Norte', 'progreso' => 50],
    ['tipo' => 'Humedad Aire',   'valor' => '65',   'unidad' => '%',    'ubicacion' => 'Sector Sur',   'progreso' => 65],
    ['tipo' => 'Humedad Suelo',  'valor' => '32',   'unidad' => '%',    'ubicacion' => 'Sector Centro','progreso' => 32],
    ['tipo' => 'Radiación PAR',  'valor' => '850',  'unidad' => 'W/m²', 'ubicacion' => 'Techo Ext.',   'progreso' => 85],
];

$page_title = "SIRA - Sensores | " . htmlspecialchars($nombre_inv);
$page_css   = "sensores";    // <- Carga /css/sensores.css
require_once 'includes/header.php';
?>

<div class="container">

    <!-- Botón de vuelta -->
    <a href="dashboard.php" class="btn-back">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Volver a la Jerarquía
    </a>

    <!-- Cabecera del invernadero -->
    <div class="invernadero-header">
        <div>
            <h1>🌱 <?= htmlspecialchars($nombre_inv) ?></h1>
            <p>Monitoreo IoT en Tiempo Real</p>
        </div>
        <div class="connection-badge">
            <div class="pulse-dot"></div>
            ONLINE
        </div>
    </div>

    <!-- Grid de tarjetas de sensores -->
    <div class="grid">
        <?php foreach ($sensores_demo as $s): ?>
            <div class="card sensor-card">
                <div class="meta sensor-label"><?= htmlspecialchars($s['tipo']) ?></div>

                <div class="sensor-value">
                    <?= htmlspecialchars($s['valor']) ?>
                    <span class="sensor-unit"><?= htmlspecialchars($s['unidad']) ?></span>
                </div>

                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: <?= (int)$s['progreso'] ?>%"></div>
                </div>

                <div class="meta sensor-location">📍 <?= htmlspecialchars($s['ubicacion']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Placeholder gráficas -->
    <div class="chart-placeholder">
        <h3>🚀 Dashboard Analítico (Próximamente)</h3>
        <p>En la Fase IV, este espacio mostrará gráficas históricas en vivo conectadas a la base de datos PostgreSQL.</p>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>