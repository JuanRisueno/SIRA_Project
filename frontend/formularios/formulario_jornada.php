<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: ../index.php"); exit(); }

require_once '../includes/config.php';

$token = $_SESSION['jwt_token'];
$user_rol = $_SESSION['user_rol'] ?? '';
$id_target = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id_target) {
    header("Location: ../dashboard.php");
    exit();
}

// 1. Control de Acceso: Solo root/admin o el propio cliente
$es_mi_perfil = ($user_rol === 'cliente' && $id_target == ($_SESSION['cliente_id'] ?? 0));
if (!in_array($user_rol, ['admin', 'root']) && !$es_mi_perfil) {
    header("Location: ../dashboard.php");
    exit();
}

// 2. Obtener Nombre del Cliente (para la UI)
$client_name = "Agricultor #$id_target";
$api_get_cli_url = SIRA_API_BASE . "/api/v1/clientes/$id_target";
$ch = curl_init($api_get_cli_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
$cli_res = curl_exec($ch);
$cli_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($cli_code == 200) {
    $cli_json = json_decode($cli_res, true);
    $client_name = $cli_json['nombre_empresa'] ?? $client_name;
}

// 3. Obtener Configuración Actual
$api_get_url = SIRA_API_BASE . "/api/v1/config/jornada/$id_target";
$ch = curl_init($api_get_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$current_config = ($http_code == 200) ? json_decode($response, true) : ["default" => []];

$error_msg = "";
$success_msg = "";

// 4. Procesar Guardado (POST 0% JS)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_config = ["default" => []];
    
    // Función helper para procesar bloques de 3 tramos
    function procesar_bloque($data, $prefijo) {
        $tramos = [];
        for ($i = 0; $i < 3; $i++) {
            $inicio = $data[$prefijo . "_ini_" . $i] ?? '';
            $fin    = $data[$prefijo . "_fin_" . $i] ?? '';
            if (!empty($inicio) && !empty($fin)) {
                $tramos[] = ["inicio" => $inicio, "fin" => $fin];
            }
        }
        return $tramos;
    }

    // Procesar Global (Default)
    $new_config["default"] = procesar_bloque($_POST, "def");

    // Procesar Días 0-6
    for ($d = 0; $d <= 6; $d++) {
        $tipo = $_POST["tipo_dia_$d"] ?? 'null';
        if ($tipo === 'null') {
            $new_config[strval($d)] = null;
        } elseif ($tipo === 'off') {
            $new_config[strval($d)] = [];
        } else {
            $new_config[strval($d)] = procesar_bloque($_POST, "d$d");
        }
    }

    // Petición PUT a la API
    $api_put_url = SIRA_API_BASE . "/api/v1/config/jornada/$id_target";
    $ch = curl_init($api_put_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // En FastAPI definimos /jornada/{id} como POST para guardar
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($new_config));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code == 200) {
        $success_msg = "Configuración de jornada actualizada correctamente.";
        $auto_redirect = "../dashboard.php";
        $current_config = $new_config;
    } else {
        $res_json = json_decode($res, true);
        $error_msg = $res_json['detail'] ?? "Error al guardar (Código: $code)";
    }
}

$page_title = "SIRA - Configurar Jornada";
$page_css   = "dashboard";
require_once '../includes/header.php';

$dias_nombres = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <a href="#">Configuración IoT</a>
        <span>/</span>
        <a href="#">Jornada Laboral</a>
    </div>

        <div class="user-form-container" style="max-width: 850px;">
            
            <div style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h2 class="section-title-premium" style="margin:0; font-size: 1.5rem;">
                        🕒 Gestión de Jornada Laboral
                    </h2>
                    <p class="dashboard-subtitle" style="margin-top: 5px;">Configuración horaria para <strong><?= htmlspecialchars($client_name) ?></strong></p>
                </div>
            </div>

            <?php if ($error_msg): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 10px; background: rgba(248, 113, 113, 0.1); border: 1px solid #f87171; color: #f87171;">
                    <strong>⚠️ Error:</strong> <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <?php if ($success_msg): ?>
                <div class="confirm-overlay" id="success-overlay">
                    <div class="confirm-card" style="border-color: var(--color-primary);">
                        <div style="font-size: 3.5rem; margin-bottom: 1rem;">✅</div>
                        <h2 style="color: var(--color-primary);">¡Configuración Actualizada!</h2>
                        <p style="margin-bottom: 0.5rem; opacity: 0.8;">
                            Los tramos horarios para el cliente se han guardado correctamente en el servidor.
                        </p>
                        <div class="sira-countdown-text">
                            Volviendo al panel en 
                            <div class="sira-countdown-number">
                                <span class="n-3">3</span>
                                <span class="n-2">2</span>
                                <span class="n-1">1</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="formulario_jornada.php?id=<?= $id_target ?>" method="POST" class="sira-form-premium">
                
                <!-- SECCIÓN 1: JORNADA BASE -->
                <div class="form-section-premium" style="background: rgba(16, 185, 129, 0.03); padding: 1.2rem; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.1); margin-bottom: 1.5rem;">
                    <h3 class="section-title-premium" style="margin-bottom: 0.8rem; font-size: 1rem;">
                        <span>🛡️</span> 1. Horario Base Semanal
                    </h3>
                    <div class="tramos-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <?php for($i=0; $i<3; $i++): 
                            $val_ini = $current_config['default'][$i]['inicio'] ?? '';
                            $val_fin = $current_config['default'][$i]['fin'] ?? '';
                        ?>
                            <div style="background: rgba(0,0,0,0.2); padding: 0.6rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.03); display: flex; align-items: center; gap: 8px;">
                                <label style="font-size: 0.7rem; color: var(--color-primary); font-weight: 700; min-width: 45px;">T<?= $i+1 ?></label>
                                <input type="time" name="def_ini_<?= $i ?>" value="<?= $val_ini ?>" style="flex: 1; padding: 4px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1); background: var(--color-bg-input); color: white; font-size: 0.8rem;">
                                <span style="opacity: 0.2;">-</span>
                                <input type="time" name="def_fin_<?= $i ?>" value="<?= $val_fin ?>" style="flex: 1; padding: 4px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1); background: var(--color-bg-input); color: white; font-size: 0.8rem;">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- SECCIÓN 2: CALENDARIO -->
                <div class="form-section-premium">
                    <h3 class="section-title-premium" style="font-size: 1rem; margin-bottom: 1rem;">
                        <span>🗓️</span> 2. Disponibilidad por día
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <?php foreach($dias_nombres as $idx => $nombre): 
                            $conf_dia = $current_config[strval($idx)] ?? null;
                            $tipo_selected = 'null';
                            if ($conf_dia === []) $tipo_selected = 'off';
                            elseif (is_array($conf_dia)) $tipo_selected = 'spec';
                        ?>
                            <div class="dia-row-compact" style="display: flex; align-items: center; gap: 15px; padding: 0.6rem 1rem; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); border-radius: 8px; transition: all 0.2s ease;">
                                
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--color-text-main); width: 100px;"><?= $nombre ?></div>
                                
                                <select name="tipo_dia_<?= $idx ?>" onchange="toggleDiaTramos(<?= $idx ?>, this.value)" style="width: 160px; padding: 4px 8px; border-radius: 6px; background: var(--color-bg-input); color: white; border: 1px solid rgba(255,255,255,0.1); font-size: 0.8rem; cursor: pointer;">
                                    <option value="null" <?= $tipo_selected === 'null' ? 'selected' : '' ?>>Usa Horario Base</option>
                                    <option value="off" <?= $tipo_selected === 'off' ? 'selected' : '' ?>>🚫 Cerrado</option>
                                    <option value="spec" <?= $tipo_selected === 'spec' ? 'selected' : '' ?>>✨ Especial...</option>
                                </select>

                                <div id="tramos_dia_<?= $idx ?>" style="display: <?= $tipo_selected === 'spec' ? 'flex' : 'none' ?>; gap: 8px; flex: 1;">
                                    <?php for($i=0; $i<3; $i++): 
                                        $val_ini = (is_array($conf_dia)) ? ($conf_dia[$i]['inicio'] ?? '') : '';
                                        $val_fin = (is_array($conf_dia)) ? ($conf_dia[$i]['fin'] ?? '') : '';
                                    ?>
                                        <div style="display: flex; align-items: center; gap: 4px; background: rgba(16, 185, 129, 0.05); padding: 3px 6px; border-radius: 5px; border: 1px solid rgba(16, 185, 129, 0.1);">
                                            <input type="time" name="d<?= $idx ?>_ini_<?= $i ?>" value="<?= $val_ini ?>" style="width: 70px; background: transparent; border: none; color: white; font-size: 0.75rem;">
                                            <span style="opacity: 0.2; font-size: 0.7rem;">-</span>
                                            <input type="time" name="d<?= $idx ?>_fin_<?= $i ?>" value="<?= $val_fin ?>" style="width: 70px; background: transparent; border: none; color: white; font-size: 0.75rem;">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                
                                <div id="label_dia_<?= $idx ?>" style="display: <?= $tipo_selected === 'spec' ? 'none' : 'block' ?>; font-size: 0.75rem; opacity: 0.4; font-style: italic;">
                                    <?= $tipo_selected === 'null' ? 'Sincronizado con base' : 'Sin actividad' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-footer-actions" style="margin-top: 2rem;">
                    <button type="submit" class="btn-sira btn-primary" style="font-size: 0.9rem; padding: 0.8rem;">
                        Guardar Configuración
                    </button>
                    <a href="../dashboard.php" class="btn-sira btn-secondary" style="font-size: 0.9rem;">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>

        <script>
        function toggleDiaTramos(idx, value) {
            const tramos = document.getElementById('tramos_dia_' + idx);
            const label = document.getElementById('label_dia_' + idx);
            
            if (value === 'spec') {
                tramos.style.display = 'flex';
                label.style.display = 'none';
            } else {
                tramos.style.display = 'none';
                label.style.display = 'block';
                label.innerText = (value === 'null') ? 'Sincronizado con base' : 'Sin actividad';
            }
        }
        </script>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
