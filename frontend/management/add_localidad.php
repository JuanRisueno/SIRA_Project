<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: ../index.php"); exit(); }

require_once '../includes/config.php';

$token = $_SESSION['jwt_token'];
$user_rol = $_SESSION['user_rol'] ?? '';

// Solo admin y root
if (!in_array($user_rol, ['admin', 'root'])) {
    header("Location: ../dashboard.php");
    exit();
}

$error_msg = "";
$success_msg = "";
$geo_status_msg = "";
$candidatos = [];

// Variables de estado del formulario
$cp = $_POST['cp'] ?? '';
$municipio = $_POST['municipio'] ?? '';
$provincia = $_POST['provincia'] ?? '';
$cp_confirmado = $_POST['cp_confirmado'] ?? ''; // <--- CERROJO GEO

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // CASO A: VALIDAR POR CÓDIGO POSTAL
    if (isset($_POST['btn_validar_cp'])) {
        if (strlen($cp) === 5) {
            $api_url = SIRA_API_BASE . "/api/v1/geo/check-cp/" . urlencode($cp);
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code == 200) {
                $data = json_decode($res, true);
                $municipio = $data['municipio'];
                $provincia = $data['provincia'];
                $cp_confirmado = $cp; // <--- SE ACTIVA EL CERROJO
                $geo_status_msg = ($data['origen'] === 'local') ? "✅ Localidad ya registrada en el sistema." : "🌍 Datos obtenidos de API externa.";
            } else {
                $error_msg = "Código Postal no reconocido por el sistema central.";
                $cp_confirmado = "";
            }
        } else {
            $error_msg = "El Código Postal debe tener 5 dígitos.";
        }
    }

    // CASO B: BUSCAR POR NOMBRE (Municipio)
    elseif (isset($_POST['btn_buscar_nombre'])) {
        if (strlen($municipio) >= 3) {
            $api_url = SIRA_API_BASE . "/api/v1/geo/search-municipio/" . urlencode($municipio);
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code == 200) {
                $candidatos = json_decode($res, true);
                if (count($candidatos) === 1) {
                    $cp = $candidatos[0]['codigo_postal'];
                    $municipio = $candidatos[0]['municipio'];
                    $provincia = $candidatos[0]['provincia'];
                    $cp_confirmado = $cp; // <--- SE ACTIVA EL CERROJO
                    $geo_status_msg = "✅ Única coincidencia encontrada en SIRA.";
                    $candidatos = []; 
                } else {
                    $geo_status_msg = "ℹ️ Varios códigos postales encontrados para \"" . htmlspecialchars($municipio) . "\". Seleccione uno.";
                }
            } else {
                $error_msg = "No se encontraron resultados para \"" . htmlspecialchars($municipio) . "\" en la base de datos de SIRA.";
                $cp_confirmado = "";
            }
        } else {
            $error_msg = "Escriba al menos 3 letras para buscar.";
        }
    }

    // CASO C: SELECCIONAR CANDIDATO
    elseif (isset($_POST['btn_seleccionar_cp'])) {
        $sel_cp = $_POST['sel_cp'] ?? '';
        $api_url = SIRA_API_BASE . "/api/v1/localidades/" . urlencode($sel_cp);
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
        $res = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($res, true);
        if ($data) {
            $cp = $data['codigo_postal'];
            $municipio = $data['municipio'];
            $provincia = $data['provincia'];
            $cp_confirmado = $cp; // <--- SE ACTIVA EL CERROJO
            $geo_status_msg = "✅ Candidato seleccionado con éxito.";
        }
    }

    // CASO D: REGISTRAR (Acción Final con Cerrojado Geo)
    elseif (isset($_POST['btn_registrar'])) {
        
        // Verificación de Gating
        if ($cp !== $cp_confirmado || empty($cp_confirmado)) {
            $error_msg = "⚠️ ERROR DE SEGURIDAD: El Código Postal ha sido modificado o no ha sido validado satisfactoriamente.";
            $cp_confirmado = ""; 
        } else {
            $api_url = SIRA_API_BASE . "/api/v1/localidades/";
            $data = ["codigo_postal" => $cp, "municipio" => $municipio, "provincia" => $provincia];
            
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code == 201) {
                $success_msg = "Localidad registrada en el sistema de gestión.";
            } else {
                $res_data = json_decode($response, true);
                $error_msg = $res_data['detail'] ?? "Error crítico al registrar.";
            }
        }
    }
}

$page_title = "SIRA - Añadir Localidad";
$page_css   = "dashboard";
require_once '../includes/header.php';
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <a href="../dashboard.php?seccion=localidades">Gestión Localidades</a>
        <span>/</span>
        <a href="#">Añadir Inteligente</a>
    </div>

    <div class="user-form-container">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">📍 Registro de Localidad</h1>
            <p class="dashboard-subtitle">Gating System (Validación Forzosa SIRA GEO).</p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background: var(--color-error-bg); border-left: 4px solid var(--color-error); color: var(--color-error-text); padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius-sm);">
                <strong>⚠️ Alerta de Seguridad:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="confirm-overlay">
                <div class="confirm-card" style="border-color: #10b981;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🛰️</div>
                    <h2 style="color: #34d399;">Localidad Añadida</h2>
                    <p><?= htmlspecialchars($success_msg) ?></p>
                    <div class="confirm-actions">
                        <a href="../dashboard.php?seccion=localidades" class="btn-sira btn-primary" style="min-width: 180px;">Volver a la Lista</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <p style="color: var(--color-primary); font-size: 0.85rem; margin-bottom: 2rem;">(*) Campos obligatorios</p>
            <input type="hidden" name="cp_confirmado" value="<?= htmlspecialchars($cp_confirmado) ?>">
            
            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--color-primary); border-radius: 10px; padding: 1rem; margin-bottom: 2rem; font-size: 0.85rem; color: var(--color-text-main);">
                🔒 <strong>SIRA Gating System:</strong> Es obligatorio validar el CP o buscar el municipio. Una vez validados, puedes corregir manualmente el nombre o la provincia si es necesario.
            </div>

            <div class="input-group-premium" style="margin-bottom: 1.5rem;">
                <label>Código Postal (CP) (*)</label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" name="cp" value="<?= htmlspecialchars($cp) ?>" maxlength="5" minlength="5" placeholder="Ej. 04001" style="flex: 1;">
                    <button type="submit" name="btn_validar_cp" class="btn-sira btn-secondary" style="padding: 0 1.2rem; font-size: 0.8rem; white-space: nowrap;">🔍 Validar CP</button>
                </div>
            </div>

            <div class="input-group-premium" style="margin-bottom: 1.5rem;">
                <label>Municipio (Buscador) (*)</label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" name="municipio" value="<?= htmlspecialchars($municipio) ?>" placeholder="Ej. Águilas" style="flex: 1;">
                    <button type="submit" name="btn_buscar_nombre" class="btn-sira btn-secondary" style="padding: 0 1.2rem; font-size: 0.8rem; white-space: nowrap;">⚡ Buscar CPs</button>
                </div>
            </div>

            <?php if (!empty($candidatos)): ?>
                <div class="input-group-premium" style="margin-bottom: 1.5rem; padding: 1.2rem; background: var(--color-bg-input); border: 1px solid var(--color-primary); border-radius: 10px;">
                    <label>📦 SELECCIÓN DE CÓDIGO POSTAL:</label>
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 10px;">
                        <select name="sel_cp">
                            <?php foreach ($candidatos as $c): ?>
                                <option value="<?= htmlspecialchars($c['codigo_postal']) ?>">
                                    <?= htmlspecialchars($c['codigo_postal']) ?> — <?= htmlspecialchars($c['provincia']) ?> (<?= htmlspecialchars($c['municipio']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="btn_seleccionar_cp" class="btn-sira btn-primary" style="padding: 0 1rem; font-size: 0.8rem;">Elegir</button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="input-group-premium">
                <label>Provincia (*)</label>
                <input type="text" name="provincia" value="<?= htmlspecialchars($provincia) ?>" placeholder="Ej. Jaén">
            </div>

            <?php if ($geo_status_msg): ?>
                <div style="margin: 1.5rem 0; padding: 0.8rem; background: rgba(52, 211, 153, 0.1); border-radius: var(--radius-container); font-size: 0.85rem; color: #34d399; text-align: center;">
                    <?= htmlspecialchars($geo_status_msg) ?>
                </div>
            <?php endif; ?>

            <div class="form-footer-actions">
                <?php if (!empty($cp_confirmado) && $cp === $cp_confirmado): ?>
                    <button type="submit" name="btn_registrar" value="1" class="btn-sira btn-primary">
                        Registrar Localidad
                    </button>
                <?php else: ?>
                    <div style="flex: 2; background: rgba(255,255,255,0.05); color: var(--color-text-muted); padding: 0.8rem; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 600; cursor: not-allowed; border: 1px dashed rgba(255,255,255,0.2); text-align: center; line-height: 1.4;">
                        🔒 Rellene los campos obligatorios (*) <br>y valide el CP para continuar
                    </div>
                <?php endif; ?>
                
                <a href="../dashboard.php?seccion=localidades" class="btn-sira btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>

    </div>

<?php require_once '../includes/footer.php'; ?>
