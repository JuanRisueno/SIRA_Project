<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: index.php"); exit(); }

require_once 'includes/config.php';

$id_inv   = $_GET['id'] ?? 1;
$nombre_inv = $_GET['nombre'] ?? 'Invernadero';
$token    = $_SESSION['jwt_token'];

/**
 * Función genérica para API REST (GET/POST)
 */
function callIoTAPI($method, $url, $token, $data = null) {
    $ch = curl_init($url);
    $headers = [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ];
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($data) {
            $json = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $headers[] = "Content-Type: application/json";
        }
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout de 5 seg
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error_msg = curl_error($ch);
    curl_close($ch);
    
    if ($http_code >= 200 && $http_code < 300) {
        return json_decode($response, true) ?: [];
    } else {
        // Enviar error como un field virtual para poder depurarlo en la vista
        return ["_error" => true, "_code" => $http_code, "_response" => $response, "_curl_err" => $error_msg];
    }
}

/**
 * Helper para renderizar gráfica SVG
 */
function render_svg_chart($sensor_id, $token, $color) {
    $url = SIRA_API_BASE . "/api/v1/iot/mediciones/sensor/{$sensor_id}?limit=15";
    $mediciones = callIoTAPI('GET', $url, $token) ?: [];
    
    if (empty($mediciones)) return '<p class="no-data">Sin datos</p>';
    
    $datos = array_reverse($mediciones);
    $valores = array_column($datos, 'valor');
    
    $max = max($valores);
    $min = min($valores);
    $range = ($max == $min) ? 1 : ($max - $min);
    
    $width = 250; $height = 50; $padding = 5;
    $points = "";
    $step = ($width - (2 * $padding)) / max(1, count($valores) - 1);
    
    foreach ($valores as $i => $v) {
        $x = $padding + ($i * $step);
        $y = ($height - $padding) - (($v - $min) / $range * ($height - 2 * $padding));
        $points .= "$x,$y ";
    }
    
    return "<svg width='100%' height='100%' viewBox='0 0 $width $height' preserveAspectRatio='none' class='mini-chart'><polyline fill='none' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' points='$points' /></svg>";
}

// ==========================================
// CONTROL DE ACCIONES MANUALES Y SIMULADOR
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['simular_escenario'])) {
        $id_target = intval($_POST['invernadero_id'] ?? $id_inv);
        $escenario = urlencode($_POST['simular_escenario']);
        $resp = callIoTAPI('POST', SIRA_API_BASE . "/api/v1/iot/simular/{$id_target}/{$escenario}", $token);
        if (!isset($resp['_error'])) {
            // Asegurar que la estructura de sesión existe
            if (!isset($_SESSION['simulacion_activa']) || !is_array($_SESSION['simulacion_activa'])) {
                $_SESSION['simulacion_activa'] = [];
            }
            // Guardamos el banner usando la ID de destino para consistencia total
            $key = (string)$id_target;
            $_SESSION['simulacion_activa'][$key] = [
                'id_escenario' => $_POST['simular_escenario'],
                'nombre' => $resp['escenario_aplicado'] ?? 'Simulación Activa',
                'momento' => $resp['momento'] ?? 'En curso...',
                'hora_virtual' => $resp['hora_virtual'] ?? null,
                'ubicacion_full' => $resp['ubicacion_simulada'] ?? null,
                'desc' => $resp['descripcion'] ?? 'Los sensores están respondiendo al escenario inyectado.'
            ];
        }
    } elseif (isset($_POST['override_actuador'])) {
        $act_id = intval($_POST['actuador_id']);
        $estado_deseado = $_POST['nuevo_estado'];
        callIoTAPI('POST', SIRA_API_BASE . "/api/v1/iot/override/", $token, [
            "actuador_id" => $act_id,
            "nuevo_estado" => $estado_deseado
        ]);
    }
    // Redirigir para limpiar el POST (Patrón PRG)
    // Redirigir manteniendo todos los parámetros originales (v9.0)
    session_write_close();
    $query = $_GET;
    $redirect_url = "sensores.php?" . http_build_query($query);
    header("Location: $redirect_url");
    exit();
}

// ==========================================
// Carga del Estado Actual
// ==========================================
$sim_key = (string)$id_inv;
$hora_virtual_api = $_SESSION['simulacion_activa'][$sim_key]['hora_virtual'] ?? null;
$ubicacion_ctx = $_SESSION['simulacion_activa'][$sim_key]['ubicacion_full'] ?? null;

$url_estado = SIRA_API_BASE . "/api/v1/iot/estado/{$id_inv}";
$params = [];
if ($hora_virtual_api) {
    $params['hora_virtual'] = $hora_virtual_api;
}
if (isset($_SESSION['simulacion_activa'][$sim_key]['id_escenario'])) {
    $params['escenario'] = $_SESSION['simulacion_activa'][$sim_key]['id_escenario'];
}
if ($ubicacion_ctx) {
    // Intentamos extraer el TZ para pasarlo limpio
    if (preg_match('/^(.*) \((.*)\)$/', $ubicacion_ctx, $m)) {
        $params['ubicacion'] = $m[1];
        $params['tz'] = $m[2];
    } else {
        $params['ubicacion'] = $ubicacion_ctx;
    }
}

if (!empty($params)) {
    $url_estado .= "?" . http_build_query($params);
}

$estado_iot = callIoTAPI('GET', $url_estado, $token);
$sensores_raw = $estado_iot['sensores'] ?? [];
$actuadores_raw = $estado_iot['actuadores'] ?? [];

// --- LÓGICA DE ALINEACIÓN MAESTRA (Fidelidad MD 5x5) ---
$priority_sensors = ['radiaci', 'suelo', 'temp', 'lluvia', 'viento'];
$priority_actuators = ['ilumina', 'electrov', 'calefac', 'ventilad', 'motor'];

$sensores_final = [];
$actuadores_final = [];

// 1. Clasificar dispositivos según prioridad exacta
foreach ($sensores_raw as $s) {
    $matched = false;
    foreach ($priority_sensors as $keyword) {
        if (stripos($s['tipo'], $keyword) !== false) {
            $s['_priority'] = array_search($keyword, $priority_sensors);
            $matched = true;
            break;
        }
    }
    if (!$matched) $s['_priority'] = 99;
    $sensores_final[] = $s;
}

foreach ($actuadores_raw as $a) {
    $matched = false;
    foreach ($priority_actuators as $keyword) {
        if (stripos($a['tipo'], $keyword) !== false) {
            $a['_priority'] = array_search($keyword, $priority_actuators);
            $matched = true;
            break;
        }
    }
    if (!$matched) $a['_priority'] = 99;
    $actuadores_final[] = $a;
}

// 2. Ordenar por prioridad para que el loop los empareje i-i
usort($sensores_final, function($a, $b) { return $a['_priority'] <=> $b['_priority']; });
usort($actuadores_final, function($a, $b) { return $a['_priority'] <=> $b['_priority']; });

$jornada_activa = $estado_iot['jornada_activa'] ?? false;
$jornada_configurada = $estado_iot['jornada_configurada'] ?? true;
$nombre_cultivo = $estado_iot['cultivo_nombre'] ?? 'Sin información de planta';
$parametros_optimos = $estado_iot['parametros_optimos'] ?? null;
$diagnostico_humano = $estado_iot['diagnostico_humano'] ?? 'Esperando lectura de sensores...';

// [NUEVO] Extracción robusta de contexto desde la respuesta del API (v10.3)
$contexto_api = $estado_iot['contexto_simulacion'] ?? null;
if ($contexto_api) {
    if (!$hora_virtual_api) $hora_virtual_api = $contexto_api['hora'];
    if (!$ubicacion_ctx) $ubicacion_ctx = $contexto_api['ubicacion'] . " (" . $contexto_api['tz'] . ")";
}

// Configuración de la página
$page_title = "SIRA Console | " . htmlspecialchars($nombre_inv);
$page_css   = "view_sensores";    
// Auto-refresco desactivado a petición del usuario para estabilizar la vista del preset (v11.5)
// echo '<meta http-equiv="refresh" content="15">'; 
require_once 'includes/header.php';
?>

<div class="container iot-console">

    <!-- Navegación -->
    <div style="margin-bottom: 2rem;">
        <a href="dashboard.php" class="btn-back">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a Jerarquía
        </a>
    </div>
    
    <!-- Banner: Diagnóstico Inteligente SIRA (¿Qué está pasando ahora?) -->
    <div style="background: linear-gradient(90deg, rgba(16, 185, 129, 0.15) 0%, rgba(6, 78, 59, 0.4) 100%); border: 1px solid rgba(16, 185, 129, 0.4); padding: 1.2rem; margin-bottom: 2rem; border-radius: 12px; display: flex; align-items: center; gap: 1.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
        <div style="background: var(--color-primary); width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);">
            <svg width="28" height="28" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <h4 style="color: var(--color-primary); margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 800;">SIRA Intelligence | Estado del Cultivo</h4>
                <?php if ($hora_virtual_api && $hora_virtual_api !== '--:--'): ?>
                    <span style="background: rgba(16, 185, 129, 0.2); color: var(--color-primary); padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; border: 1px solid var(--color-primary); white-space: nowrap;">
                        🕒 HORA VIRTUAL: <?= htmlspecialchars($hora_virtual_api) ?>
                    </span>
                <?php endif; ?>
            </div>
            <p style="color: #f8fafc; margin: 0; font-size: 1.1rem; font-weight: 600; line-height: 1.4;">
                <?= htmlspecialchars($diagnostico_humano) ?>
            </p>
        </div>
    </div>

    <!-- SIRA Console: Escenarios -->
    <div class="console-panel">
        <?php if (!empty($estado_iot['_error'])): ?>
            <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; padding: 1.5rem; margin-bottom: 2rem; border-radius: 8px;">
                <h3 style="color: #fca5a5; margin-bottom: 0.5rem;">🔥 API Error <?= htmlspecialchars($estado_iot['_code']) ?></h3>
                <p style="color: white; font-family: monospace;"><?= htmlspecialchars($estado_iot['_response']) ?></p>
                <p style="color: gray; font-size: 0.8rem;"><?= htmlspecialchars($estado_iot['_curl_err']) ?></p>
            </div>
        <?php endif; ?>

        <div class="console-header">
            <div>
                <h2>SIRA Console | 🌱 <?= htmlspecialchars($nombre_inv) ?></h2>
                <div style="display: flex; gap: 10px; align-items: center; margin-top: 5px; flex-wrap: wrap;">
                    <span style="background: rgba(16, 185, 129, 0.2); color: var(--color-primary); padding: 5px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 700;">
                        🍇 Cultivo Activo: <?= htmlspecialchars(strtoupper($nombre_cultivo)) ?>
                    </span>
                    
                    <?php if ($parametros_optimos): ?>
                        <span style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; padding: 5px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; border: 1px solid rgba(14, 165, 233, 0.3);">
                            Fase: <b><?= htmlspecialchars($parametros_optimos['fase']) ?></b> &nbsp;|&nbsp; 
                            🌡️ <?= $parametros_optimos['temp_min'] ?> - <?= $parametros_optimos['temp_max'] ?>ºC &nbsp;|&nbsp; 
                            💧 <?= $parametros_optimos['hum_min'] ?> - <?= $parametros_optimos['hum_max'] ?>%
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="jornada-badge <?= $jornada_activa ? 'jornada-on' : 'jornada-off' ?>">
                <?= $jornada_activa ? '☀ JORNADA LABORAL' : '🌙 FUERA DE HORARIO' ?>
            </div>
        </div>
        
        <form method="POST" action="sensores.php?<?= http_build_query($_GET) ?>" class="preset-bar">
            <!-- Campo oculto para asegurar consistencia de ID en el POST -->
            <input type="hidden" name="invernadero_id" value="<?= htmlspecialchars($id_inv) ?>">
            
            <!-- 6 Presets Fijos -->
            <button type="submit" name="simular_escenario" value="ideal" class="btn-preset btn-ideal">🌱 Ideal</button>
            <button type="submit" name="simular_escenario" value="tormenta" class="btn-preset btn-tormenta">⛈ Tormenta</button>
            <button type="submit" name="simular_escenario" value="calor" class="btn-preset btn-calor">🔥 Calor</button>
            <button type="submit" name="simular_escenario" value="helada" class="btn-preset btn-helada">❄ Helada</button>
            <button type="submit" name="simular_escenario" value="nublado" class="btn-preset btn-nublado">☁ Nublado</button>
            <button type="submit" name="simular_escenario" value="sequia" class="btn-preset btn-sequia">🏜 Sequía</button>
            
            <!-- Botón Aleatorio Destacado -->
            <button type="submit" name="simular_escenario" value="random" class="btn-random">🎲 RANDOMIZE</button>
        </form>
    </div> <!-- Cierre correcto de console-panel -->

    <!-- Barra de Estado Maestro IoT (Independiente) -->
    <div class="master-status-bar">
        <!-- Sensores Rápidos -->
        <div class="status-group">
            <span class="status-label" style="font-size: 0.65rem; margin-right: 5px;">📡 SENSORES:</span>
            <?php foreach (array_slice($sensores_final, 0, 5) as $s): ?>
                <div class="status-item">
                    <span title="<?= htmlspecialchars($s['tipo']) ?>"><?= mb_substr(htmlspecialchars($s['tipo']), 0, 4) ?>.</span>
                    <span style="color: var(--color-primary);"><?= number_format($s['valor'] ?? 0, 1) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="v-divider"></div>

        <!-- Actuadores Rápidos (LEDs) -->
        <div class="status-group">
            <span class="status-label" style="font-size: 0.65rem; margin-right: 5px;">⚙️ ACTUADORES:</span>
            <?php foreach (array_slice($actuadores_final, 0, 5) as $a): ?>
                <?php $is_on = (stripos($a['estado'], 'ON') !== false || stripos($a['estado'], 'ENCENDID') !== false || stripos($a['estado'], 'ABIERTO') !== false); ?>
                <div class="status-item" title="<?= htmlspecialchars($a['tipo']) ?>: <?= htmlspecialchars($a['estado']) ?>">
                    <div class="led-indicator <?= $is_on ? 'led-on' : 'led-off' ?>"></div>
                    <span><?= (stripos($a['tipo'], 'riego') !== false) ? 'RIEGO' : mb_substr(htmlspecialchars($a['tipo']), 0, 6) ?>.</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <!-- Estilos Responsivos Locales -->
    <style>
        .iot-headers {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 1rem;
            padding: 0 10px;
        }
        .iot-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem 2rem;
            align-items: stretch;
        }
        /* ── Barra de Estado Maestro (SIRA Style) ── */
        .master-status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--color-bg-card);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 15px 25px;
            margin-top: 1rem;
            margin-bottom: 2.5rem;
            gap: 20px;
            box-shadow: var(--shadow-page);
        }
        .status-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--color-text-main);
        }
        .status-label {
            color: var(--color-text-muted);
            font-weight: 600;
        }
        .led-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        .led-on {
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: sensorPulse 2s infinite;
        }
        .led-off {
            background: #ef4444;
            opacity: 0.4;
            box-shadow: none;
        }
        .v-divider {
            width: 1px;
            height: 20px;
            background: var(--border-color);
        }
        @media (max-width: 900px) {
            .iot-headers {
                display: none; /* En móvil las cartas son autoexplicativas y ahorramos espacio vertical */
            }
            .iot-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .iot-grid > .card {
                grid-column: 1 !important; /* Si fallaba span 2 */
            }
            .master-status-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .v-divider { display: none; }
        }
    </style>

    <!-- Cabeceras Alineadas -->
    <div class="iot-headers">
        <div style="display: flex; align-items: flex-end;">
            <h3 class="iot-col-title" style="margin: 0;">Lectura de Sensores</h3>
        </div>
        <div>
            <h3 class="iot-col-title" style="margin: 0;">Control de Actuadores</h3>
            <span style="font-size: 0.75rem; color: #94a3b8; display: block; margin-top: 4px; font-weight: 500;">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                La intervención manual suspende el control del SIRA durante 120 minutos
            </span>
        </div>
    </div>

    <!-- Grid Layout IoT Dinámico y Alineado -->
    <div class="iot-grid">
        <?php 
        $max_rows = max(count($sensores_final), count($actuadores_final));
        for ($i = 0; $i < $max_rows; $i++):
            $s = $sensores_final[$i] ?? null;
            $a = $actuadores_final[$i] ?? null;
        ?>
            <!-- COLUMNA SENSOR -->
            <?php if ($s): 
                $color = "var(--color-primary)";
                if(stripos($s['tipo'], 'temp') !== false) $color = "#f59e0b";
                if(stripos($s['tipo'], 'humedad') !== false || stripos($s['tipo'], 'suelo') !== false) $color = "#3b82f6";
                if(stripos($s['tipo'], 'viento') !== false) $color = "#a855f7";
                if(stripos($s['tipo'], 'lluvia') !== false) $color = "#60a5fa";
            ?>
                <div class="card iot-card sensor-card">
                    <div class="iot-card-header">
                        <span class="iot-label"><?= htmlspecialchars($s['tipo']) ?></span>
                        <span class="iot-tag">En vivo</span>
                    </div>
                    <div class="iot-value-block" style="color: <?= $color ?>; margin-bottom: 15px;">
                        <span class="iot-value"><?= $s['valor'] !== null ? htmlspecialchars($s['valor']) : '--' ?></span>
                        <span class="iot-unit"><?= htmlspecialchars($s['unidad']) ?></span>
                    </div>
                    <div class="iot-chart-box" style="margin-top: auto;">
                        <?= render_svg_chart($s['sensor_id'], $token, $color) ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-placeholder"></div>
            <?php endif; ?>

            <!-- COLUMNA ACTUADOR -->
            <?php if ($a): 
                $estado = strtoupper($a['estado'] ?? 'APAGADO');
                $is_on = (stripos($estado, 'ENCENDI') !== false || stripos($estado, 'ABIERT') !== false);
                $es_led = (stripos($a['tipo'], 'luz') !== false || stripos($a['tipo'], 'led') !== false || stripos($a['tipo'], 'ilumina') !== false);
            ?>
                <div class="card iot-card actuator-card">
                    <div class="iot-card-header">
                        <span class="iot-label"><?= htmlspecialchars($a['tipo']) ?></span>
                        <?php if ($es_led && !$jornada_configurada): ?>
                            <span class="iot-tag" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">⚠️ Sin Jornada</span>
                        <?php elseif ($a['modo_manual']): ?>
                            <span class="iot-tag manual">✋ Manual</span>
                        <?php else: ?>
                            <span class="iot-tag auto">⚙ Auto</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="actuator-status <?= $is_on ? 'status-on' : 'status-off' ?>" style="flex-grow: 1; display:flex; align-items:center; justify-content:center; margin-bottom: 20px;">
                        <?= htmlspecialchars($estado) ?>
                    </div>

                    <form method="POST" class="actuator-controls" style="margin-top: auto; display: flex; flex-direction: column; gap: 0.5rem;">
                        <input type="hidden" name="override_actuador" value="1">
                        <input type="hidden" name="actuador_id" value="<?= $a['actuador_id'] ?>">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <?php if(stripos($a['tipo'], 'ventana') !== false): ?>
                                <button type="submit" name="nuevo_estado" value="ABIERTO 100%" class="btn-override">Abrir</button>
                                <button type="submit" name="nuevo_estado" value="CERRADO" class="btn-override action-stop">Cerrar</button>
                            <?php else: ?>
                                <button type="submit" name="nuevo_estado" value="ENCENDIDO" class="btn-override">ON</button>
                                <button type="submit" name="nuevo_estado" value="APAGADO" class="btn-override action-stop">OFF</button>
                            <?php endif; ?>
                        </div>

                        <?php if ($a['modo_manual'] && !($es_led && !$jornada_configurada)): ?>
                            <button type="submit" name="nuevo_estado" value="AUTO" class="btn-override" style="background-color: transparent; border: 1px solid var(--color-primary); color: var(--color-primary); margin-top: 5px;">Volver a modo AUTO</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php else: ?>
                <div class="empty-placeholder"></div>
            <?php endif; ?>

        <?php endfor; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>