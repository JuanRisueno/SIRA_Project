<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: ../index.php"); exit(); }
require_once '../includes/config.php';
$token = $_SESSION['jwt_token'];
$from = $_GET['from'] ?? '';
$inv_id = isset($_GET['inv_id']) ? (int)$_GET['inv_id'] : null;
$type = $_GET['type'] ?? 'invernadero';
$cliente_id_url = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;

// Validación de entrada
if ($type === 'global' && !$cliente_id_url) { header("Location: ../dashboard.php"); exit(); }
if ($type === 'invernadero' && !$inv_id) { header("Location: ../dashboard.php"); exit(); }

// 1. Obtener Datos de Identidad
$inv_nombre = "Configuración Maestro";
if ($type === 'invernadero') {
    $api_get_inv_url = SIRA_API_BASE . "/api/v1/invernaderos/$inv_id";
    $ch = curl_init($api_get_inv_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $inv_res = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
        $inv_data = json_decode($inv_res, true);
        $inv_nombre = $inv_data['nombre'] ?? "Invernadero #$inv_id";
    }
    curl_close($ch);
} else {
    $inv_nombre = "Política Global del Agricultor";
}

// 2. Config Actual
$api_get_jornada_url = ($type === 'global') 
    ? SIRA_API_BASE . "/api/v1/config/jornada/cliente/$cliente_id_url"
    : SIRA_API_BASE . "/api/v1/config/jornada/invernadero/$inv_id";

$ch = curl_init($api_get_jornada_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
$response = curl_exec($ch);
$current_config = (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) ? json_decode($response, true) : ["default" => [], "es_laborable" => true, "heredar_de_global" => false];
curl_close($ch);

// 3. Procesar Guardado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $es_laborable = isset($_POST['es_laborable']) && $_POST['es_laborable'] === '1';
    $heredar_de_global = isset($_POST['heredar_de_global']) && $_POST['heredar_de_global'] === '1';
    
    $new_config = [
        "es_laborable" => $es_laborable, 
        "heredar_de_global" => $heredar_de_global,
        "default" => []
    ];

    function prc($data, $p) {
        $t = [];
        for ($i = 0; $i < 2; $i++) {
            $ini = $data[$p."_ini_".$i] ?? ''; $fin = $data[$p."_fin_".$i] ?? '';
            if (!empty($ini) && !empty($fin)) $t[] = ["inicio" => $ini, "fin" => $fin];
        }
        return $t;
    }
    if ($es_laborable && !$heredar_de_global) {
        $new_config["default"] = prc($_POST, "def");
        foreach([1,2,3,4,5,6,0] as $d) {
            $tipo = $_POST["tipo_dia_$d"] ?? 'null';
            if ($tipo === 'null') $new_config[strval($d)] = null;
            elseif ($tipo === 'off') $new_config[strval($d)] = [];
            else $new_config[strval($d)] = prc($_POST, "d$d");
        }
    }
    $api_put_url = ($type === 'global')
        ? SIRA_API_BASE . "/api/v1/config/jornada/cliente/$cliente_id_url"
        : SIRA_API_BASE . "/api/v1/config/jornada/invernadero/$inv_id";

    $ch = curl_init($api_put_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($new_config));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200 || curl_exec($ch)) {
        if ($from === 'jornadas_resumen') {
            $dest = "../dashboard.php?seccion=jornadas_resumen";
            if ($cliente_id_url) $dest .= "&cliente_id=" . $cliente_id_url;
            header("Location: " . $dest);
        } else {
            $dest = "../dashboard.php?parcela_id=" . ($inv_data['parcela_id'] ?? '');
            if ($cliente_id_url) $dest .= "&cliente_id=" . $cliente_id_url;
            header("Location: " . $dest);
        }
        exit();
    }
    curl_close($ch);
}

$page_title = "SIRA - Jornada";
$page_css = "dashboard";
require_once '../includes/header.php';
$semana = [1 => "Lunes", 2 => "Martes", 3 => "Miércoles", 4 => "Jueves", 5 => "Viernes", 6 => "Sábado", 0 => "Domingo"];
?>

<div class="container" style="max-width: 1200px; padding-top: var(--spacing-xl);">
    
    <div class="glass-form-container">
        
        <form action="formulario_jornada.php?<?= http_build_query($_GET) ?>" method="POST" class="sira-form">
            
            <div class="form-header-premium">
                <div class="title-group">
                    <span class="icon-badge">🕒</span>
                    <div>
                        <h1 class="main-title">Configuración de Jornada</h1>
                        <p class="sub-title">Semana laboral para <span class="highlight"><?= htmlspecialchars($inv_nombre) ?></span></p>
                    </div>
                </div>
                
                <div class="status-toggle-box">
                    <span class="toggle-label">MODO PRODUCTIVO</span>
                    <label class="sira-switch-wow">
                        <input type="checkbox" id="check-laborable" name="es_laborable" value="1" <?= ($current_config['es_laborable'] ?? true) ? 'checked' : '' ?> onchange="toggleProductivo(this)">
                        <div class="slider-wow"></div>
                    </label>
                </div>

                <?php if ($type === 'invernadero'): ?>
                <div class="status-toggle-box" style="background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.3);">
                    <span class="toggle-label" style="color: #60a5fa;">HEREDAR GLOBAL</span>
                    <label class="sira-switch-wow">
                        <input type="checkbox" id="check-heredar" name="heredar_de_global" value="1" <?= ($current_config['heredar_de_global'] ?? false) ? 'checked' : '' ?> onchange="toggleHeredar(this)">
                        <div class="slider-wow" style="background: rgba(59, 130, 246, 0.2); border-color: #60a5fa;"></div>
                    </label>
                </div>
                <?php endif; ?>
            </div>

            <div id="j-body" style="transition: var(--transition-smooth); <?php 
                $show_body = ($current_config['es_laborable'] ?? true) && !($current_config['heredar_de_global'] ?? false);
                echo $show_body ? '' : 'opacity: 0.3; pointer-events: none; filter: grayscale(0.8);';
            ?>">
                
                <div class="base-jornada-strip">
                    <div class="strip-label">
                        <span class="icon">📅</span>
                        <span>JORNADA BASE</span>
                    </div>
                    <div class="tramos-container">
                        <?php for ($i = 0; $i < 2; $i++): $val = $current_config['default'][$i] ?? null; ?>
                            <div class="tramo-pill-wow">
                                <span class="tramo-id">T<?= $i+1 ?></span>
                                <input type="time" name="def_ini_<?= $i ?>" id="def_ini_<?= $i ?>" value="<?= $val['inicio'] ?? '' ?>" oninput="validateForm()">
                                <span class="arrow">➜</span>
                                <input type="time" name="def_fin_<?= $i ?>" id="def_fin_<?= $i ?>" value="<?= $val['fin'] ?? '' ?>" oninput="validateForm()">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="exceptions-grid">
                    <?php foreach ($semana as $idx => $nombre): 
                        $conf = $current_config[strval($idx)] ?? null;
                        $tipo = is_array($conf) ? (empty($conf) ? 'off' : 'spec') : 'null';
                    ?>
                        <div class="day-card-wow">
                            <div class="card-header">
                                <span class="day-name"><?= $nombre ?></span>
                                <select name="tipo_dia_<?= $idx ?>" class="select-wow" onchange="tglDia(<?= $idx ?>, this.value)">
                                    <option value="null" <?= $tipo === 'null' ? 'selected' : '' ?>>J. Base</option>
                                    <option value="off" <?= $tipo === 'off' ? 'selected' : '' ?>>Cerrado</option>
                                    <option value="spec" <?= $tipo === 'spec' ? 'selected' : '' ?>>Específico</option>
                                </select>
                            </div>

                            <div id="inputs_<?= $idx ?>" class="card-body" style="display: <?= $tipo === 'spec' ? 'flex' : 'none' ?>;">
                                <?php for ($i = 0; $i < 2; $i++): $v = $conf[$i] ?? null; ?>
                                    <div class="mini-pill">
                                        <input type="time" name="d<?= $idx ?>_ini_<?= $i ?>" value="<?= $v['inicio'] ?? '' ?>">
                                        <input type="time" name="d<?= $idx ?>_fin_<?= $i ?>" value="<?= $v['fin'] ?? '' ?>">
                                    </div>
                                <?php endfor; ?>
                            </div>
                            
                            <div id="label_<?= $idx ?>" class="card-status-label" style="display: <?= $tipo === 'spec' ? 'none' : 'block' ?>;">
                                <?= ($tipo === 'null') ? 'Sincronizado' : 'Sin Actividad' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <div class="form-actions-wow">
                <button type="submit" id="save-btn" class="btn-wow btn-save">
                    <span class="icon">💾</span> GUARDAR CONFIGURACIÓN
                </button>
                <?php
                $cancel_url = "../dashboard.php";
                if ($from === 'jornadas_resumen') {
                    $cancel_url = "../dashboard.php?seccion=jornadas_resumen";
                    if ($cliente_id_url) $cancel_url .= "&cliente_id=" . $cliente_id_url;
                } elseif ($cliente_id_url) {
                    $cancel_url .= "?cliente_id=" . $cliente_id_url;
                }
                ?>
                <a href="<?= htmlspecialchars($cancel_url) ?>" class="btn-wow btn-cancel">CANCELAR</a>
            </div>
        </form>

    </div>
</div>

<script>
function tglDia(idx, v) {
    document.getElementById('inputs_' + idx).style.display = (v === 'spec') ? 'flex' : 'none';
    document.getElementById('label_' + idx).style.display = (v === 'spec') ? 'none' : 'block';
    document.getElementById('label_' + idx).innerText = (v === 'null') ? 'Sincronizado' : 'Sin Actividad';
}

function validateForm() {
    const isLaborable = document.getElementById('check-laborable').checked;
    const heredarCheck = document.getElementById('check-heredar');
    const isHeredar = heredarCheck ? heredarCheck.checked : false;
    const saveBtn = document.getElementById('save-btn');
    
    if (!isLaborable || isHeredar) {
        saveBtn.disabled = false;
        saveBtn.style.opacity = '1';
        saveBtn.style.cursor = 'pointer';
        return;
    }

    let hasOneTramo = false;
    for (let i = 0; i < 2; i++) {
        const ini = document.getElementById('def_ini_' + i).value;
        const fin = document.getElementById('def_fin_' + i).value;
        if (ini && fin) {
            hasOneTramo = true;
            break;
        }
    }
    
    saveBtn.disabled = !hasOneTramo;
    saveBtn.style.opacity = hasOneTramo ? '1' : '0.3';
    saveBtn.style.cursor = hasOneTramo ? 'pointer' : 'not-allowed';
}

function toggleProductivo(cb) {
    const isHeredar = document.getElementById('check-heredar')?.checked || false;
    if (!isHeredar) {
        document.getElementById('j-body').style.opacity = cb.checked ? '1' : '0.15';
        document.getElementById('j-body').style.pointerEvents = cb.checked ? 'auto' : 'none';
        document.getElementById('j-body').style.filter = 'none';
    }
    validateForm();
}

function toggleHeredar(cb) {
    const body = document.getElementById('j-body');
    if (cb.checked) {
        body.style.opacity = '0.3';
        body.style.pointerEvents = 'none';
        body.style.filter = 'grayscale(0.8)';
    } else {
        const isLab = document.getElementById('check-laborable').checked;
        body.style.opacity = isLab ? '1' : '0.15';
        body.style.pointerEvents = isLab ? 'auto' : 'none';
        body.style.filter = 'none';
    }
    validateForm();
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', validateForm);
</script>


<?php require_once '../includes/footer.php'; ?>
