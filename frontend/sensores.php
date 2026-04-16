<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: index.php"); exit(); }

require_once 'includes/config.php';

$id_inv   = $_GET['id']     ?? 1;
$nombre_inv = $_GET['nombre'] ?? 'Invernadero';
$token    = $_SESSION['jwt_token'];

/**
 * Función para obtener datos de la API mediante cURL
 */
function fetchAPI($url, $token) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200) ? json_decode($response, true) : [];
}

/**
 * Renderiza una gráfica lineal SVG simple basándose en un array de mediciones
 */
function render_svg_chart($mediciones, $color = "#10b981") {
    if (empty($mediciones)) return '<p class="no-data">Sin datos históricos</p>';
    
    // Invertimos para que el tiempo fluya de izquierda a derecha
    $datos = array_reverse($mediciones);
    $valores = array_column($datos, 'valor');
    
    $max = max($valores);
    $min = min($valores);
    $range = ($max == $min) ? 1 : ($max - $min);
    
    $width = 300;
    $height = 60;
    $padding = 5;
    
    $points = "";
    $step = ($width - (2 * $padding)) / (count($valores) - 1);
    
    foreach ($valores as $i => $v) {
        $x = $padding + ($i * $step);
        // Invertimos Y para que el valor alto esté arriba (0 es el techo del SVG)
        $y = ($height - $padding) - (($v - $min) / $range * ($height - 2 * $padding));
        $points .= "$x,$y ";
    }
    
    return "
    <svg width='$width' height='$height' class='mini-chart'>
        <polyline fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' points='$points' />
    </svg>";
}

// 1. Obtener Sensores del Invernadero
$api_sensores_url = SIRA_API_BASE . "/api/v1/iot/sensores/invernadero/" . $id_inv;
$sensores = fetchAPI($api_sensores_url, $token);

$page_title = "SIRA - Sensores | " . htmlspecialchars($nombre_inv);
$page_css   = "sensores";    
echo '<meta http-equiv="refresh" content="10">'; // Telemetría en vivo (Robust MVP)
require_once 'includes/header.php';
?>

<div class="container">

    <!-- Botón de vuelta -->
    <a href="dashboard.php<?= isset($_GET['cliente_id']) ? '?cliente_id=' . htmlspecialchars($_GET['cliente_id']) : '' ?>" class="btn-back">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Volver a la Jerarquía
    </a>

    <!-- Cabecera del invernadero -->
    <div class="invernadero-header">
        <div>
            <h1>🌱 <?= htmlspecialchars($nombre_inv) ?></h1>
            <p>Monitoreo IoT - Almería & Murcia Robust Hub</p>
        </div>
        <div class="connection-badge">
            <div class="pulse-dot"></div>
            EN VIVO
        </div>
    </div>

    <!-- Grid de tarjetas de sensores -->
    <div class="grid">
        <?php if (empty($sensores)): ?>
            <div class="card sensor-card" style="grid-column: 1 / -1; text-align: center;">
                <p>No se han encontrado sensores vinculados a este invernadero en la base de datos.</p>
            </div>
        <?php else: ?>
            <?php foreach ($sensores as $s): 
                // Obtener últimas 20 mediciones para la gráfica
                $api_med_url = SIRA_API_BASE . "/api/v1/iot/mediciones/sensor/" . $s['sensor_id'] . "?limit=20";
                $mediciones = fetchAPI($api_med_url, $token);
                $ultima = !empty($mediciones) ? $mediciones[0] : null;

                // Estilos según el tipo de sensor
                $color = "#10b981"; // Verde por defecto
                if (stripos($s['ubicacion_sensor'], 'Exterior') !== false) $color = "#3b82f6"; // Azul
                ?>
                <div class="card sensor-card">
                    <div class="meta sensor-label">
                        <?= htmlspecialchars($s['tipo_sensor'] ?? 'Sensor General') ?>
                    </div>

                    <div class="sensor-value">
                        <?= $ultima ? htmlspecialchars($ultima['valor']) : '---' ?>
                        <span class="sensor-unit"><?= htmlspecialchars($s['unidad_medida'] ?? '') ?></span>
                    </div>

                    <!-- Gráfica SVG Polyline -->
                    <div class="sensor-chart">
                        <?= render_svg_chart($mediciones, $color) ?>
                    </div>

                    <div class="meta sensor-location">
                        📍 <?= htmlspecialchars($s['ubicacion_sensor']) ?> 
                        <span class="status-tag status-<?= strtolower($s['estado_sensor']) ?>">
                            <?= htmlspecialchars($s['estado_sensor']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Panel de Recomendación (Simulado para defensa) -->
    <div class="chart-placeholder">
        <h3>💡 Asistente de Riego Inteligente</h3>
        <p>Basado en los parámetros óptimos de tu cultivo local, el sistema recomienda mantener la humedad del suelo por encima del 60%.</p>
    </div>

</div>


<?php require_once 'includes/footer.php'; ?>