<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: ../index.php"); exit(); }

require_once '../includes/config.php';

$token = $_SESSION['jwt_token'];
$user_rol = $_SESSION['user_rol'] ?? '';

// Solo admin y root pueden entrar aquí
if (!in_array($user_rol, ['admin', 'root'])) {
    header("Location: ../dashboard.php");
    exit();
}

$cliente_id_seleccionado = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;
if (!$cliente_id_seleccionado) {
    header("Location: ../dashboard.php");
    exit();
}

$error_msg = "";
$success_msg = "";
$geo_status_msg = "";

// Variables para mantener el estado del formulario
$nombre = $_POST['nombre'] ?? '';
$ref_catastral = $_POST['ref_catastral'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$cp = $_POST['cp'] ?? '';
$municipio = $_POST['municipio'] ?? '';
$provincia = $_POST['provincia'] ?? '';
$es_nuevo_cp = $_POST['es_nuevo_cp'] ?? '0';
$cp_confirmado = $_POST['cp_confirmado'] ?? ''; // <--- CERROJO GEO

// 1. Manejar Postback (Acciones del formulario)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // CASO A: VALIDAR CÓDIGO POSTAL (Postback parcial)
    if (isset($_POST['btn_validar_cp'])) {
        if (strlen($cp) === 5) {
            $api_geo_url = SIRA_API_BASE . "/api/v1/geo/check-cp/" . urlencode($cp);
            $ch = curl_init($api_geo_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code == 200) {
                $data = json_decode($res, true);
                $municipio = $data['municipio'];
                $provincia = $data['provincia'];
                $es_nuevo_cp = ($data['origen'] === 'local') ? '0' : '1';
                $cp_confirmado = $cp; // <--- SE ACTIVA EL CERROJO
                $geo_status_msg = ($es_nuevo_cp === '0') ? "✅ Localidad encontrada en sistema." : "🌍 CP Validado vía externa (SIRA Strict).";
            } else {
                $error_msg = "Código Postal no reconocido oficialmente. No se puede permitir el registro.";
                $municipio = ""; $provincia = ""; $es_nuevo_cp = "0"; $cp_confirmado = "";
            }
        } else {
            $error_msg = "El código postal debe tener 5 dígitos.";
        }
    }

    // CASO B: GUARDAR PARCELA (Acción final con validación forzosa)
    elseif (isset($_POST['btn_guardar'])) {
        
        // 1. Verificación de Seguridad: ¿Ha cambiado el CP tras validar?
        if ($cp !== $cp_confirmado || empty($cp_confirmado)) {
            $error_msg = "⚠️ ERROR DE SEGURIDAD: El Código Postal ha sido modificado tras la validación. Por favor, vuelva a validar.";
            $cp_confirmado = ""; // Reset de seguridad
        } else {
            // 2. Proceso de Guardado (Todo OK)
            $api_geo_url = SIRA_API_BASE . "/api/v1/geo/check-cp/" . urlencode($cp);
            $ch = curl_init($api_geo_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
            $res_final = curl_exec($ch);
            $code_final = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code_final !== 200) {
                $error_msg = "No se puede guardar: El Código Postal $cp no es válido.";
            } else {
                $geo_data = json_decode($res_final, true);

                // 2. Si el CP es nuevo para SIRA, registrar la localidad automáticamente
                if ($geo_data['origen'] === 'externo') {
                    $loc_api = SIRA_API_BASE . "/api/v1/localidades/";
                    $loc_data = [
                        "codigo_postal" => $cp, 
                        "municipio" => $geo_data['municipio'], 
                        "provincia" => $geo_data['provincia']
                    ];
                    
                    $ch = curl_init($loc_api);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loc_data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
                    curl_exec($ch);
                    curl_close($ch);
                }

                // 3. Crear la Parcela
                $api_url = SIRA_API_BASE . "/api/v1/parcelas/";
                $data = [
                    "cliente_id" => $cliente_id_seleccionado,
                    "nombre" => $nombre ?: null,
                    "codigo_postal" => $cp,
                    "ref_catastral" => $ref_catastral,
                    "direccion" => $direccion
                ];

                $ch = curl_init($api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json", "Accept: application/json"]);
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_code == 201) {
                    $success_msg = "Parcela activada y asignada correctamente.";
                } else {
                    $res_data = json_decode($response, true);
                    $error_msg = $res_data['detail'] ?? "Error crítico al registrar parcela.";
                }
            }
        }
    }
}

$page_title = "SIRA - Añadir Parcela";
$page_css   = "dashboard"; 
require_once '../includes/header.php';
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <a href="../dashboard.php?cliente_id=<?= $cliente_id_seleccionado ?>">Entorno Cliente</a>
        <span>/</span>
        <a href="#">Añadir Parcela</a>
    </div>

    <div class="user-form-container card">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">➕ Añadir Nueva Parcela</h1>
            <p class="dashboard-subtitle">Gating System (Seguridad Geo-Secuencial 100% PHP).</p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background: var(--color-error-bg); border-left: 4px solid var(--color-error); color: var(--color-error-text); padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius-sm);">
                <strong>⚠️ Alerta de Seguridad:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="confirm-overlay">
                <div class="confirm-card" style="border-color: #10b981;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🍃</div>
                    <h2 style="color: #34d399;">Parcela Registrada</h2>
                    <p><?= htmlspecialchars($success_msg) ?></p>
                    <div class="confirm-actions">
                        <a href="../dashboard.php?cliente_id=<?= $cliente_id_seleccionado ?>" class="btn-sira btn-primary" style="min-width: 180px;">Volver al Entorno</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <p style="color: var(--color-primary); font-size: 0.85rem; margin-bottom: 2rem;">(*) Campos obligatorios</p>

            <input type="hidden" name="es_nuevo_cp" value="<?= htmlspecialchars($es_nuevo_cp) ?>">
            <input type="hidden" name="cp_confirmado" value="<?= htmlspecialchars($cp_confirmado) ?>">
            
            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--color-primary); border-radius: 10px; padding: 1.2rem; margin-bottom: 2rem; font-size: 0.85rem; color: var(--color-text-main);">
                🔒 <strong>SIRA Gating System:</strong> Es obligatorio validar el Código Postal antes de poder guardar la finca. Cualquier cambio en los datos invalidará la sesión de guardado.
            </div>

            <div class="form-premium-grid">
                <!-- CAMPOS BASE -->
                <div style="grid-column: span 2;">
                    <div class="input-group-premium">
                        <label>Nombre de la Parcela (Alias)</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" placeholder="Ej. Finca de los Olivos...">
                    </div>
                </div>
                <div style="grid-column: span 2;">
                    <div class="input-group-premium">
                        <label>Referencia Catastral (*)</label>
                        <input type="text" name="ref_catastral" required maxlength="14" minlength="14" value="<?= htmlspecialchars($ref_catastral) ?>" placeholder="14 caracteres (Ej. 1234567AB1234C)" style="font-family: monospace; letter-spacing: 1px;">
                    </div>
                </div>
                <div style="grid-column: span 2;">
                    <div class="input-group-premium">
                        <label>Dirección de la Parcela (*)</label>
                        <input type="text" name="direccion" required value="<?= htmlspecialchars($direccion) ?>" placeholder="Ej. Polígono 4, Parcela 12...">
                    </div>
                </div>

                <!-- SECCIÓN GEO (BLINDADA) -->
                <div class="input-group-premium">
                    <label>Código Postal (*)</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" name="cp" value="<?= htmlspecialchars($cp) ?>" required maxlength="5" minlength="5" placeholder="04001" style="flex: 1;">
                        <button type="submit" name="btn_validar_cp" value="1" class="btn-sira btn-secondary" style="padding: 0 1rem; font-size: 0.8rem; white-space: nowrap;">⚡ Validar CP</button>
                    </div>
                </div>
                <div class="input-group-premium">
                    <label style="color: var(--color-text-muted);">Municipio (Auto)</label>
                    <input type="text" name="municipio" value="<?= htmlspecialchars($municipio) ?>" required readonly placeholder="Validación obligatoria..." style="background: rgba(0,0,0,0.3); color: var(--color-text-muted); opacity: 0.6; cursor: not-allowed;">
                </div>
                <div class="input-group-premium" style="grid-column: span 2;">
                    <label style="color: var(--color-text-muted);">Provincia (Auto)</label>
                    <input type="text" name="provincia" value="<?= htmlspecialchars($provincia) ?>" required readonly placeholder="Validación obligatoria..." style="background: rgba(0,0,0,0.3); color: var(--color-text-muted); opacity: 0.6; cursor: not-allowed;">
                </div>
            </div>

            <?php if ($geo_status_msg): ?>
            <div style="margin-top: 1.5rem; padding: 0.8rem; background: rgba(52, 211, 153, 0.1); border-radius: var(--radius-container); font-size: 0.85rem; color: #34d399; text-align: center;">
                <?= htmlspecialchars($geo_status_msg) ?>
            </div>
            <?php endif; ?>

            <div class="form-footer-actions">
                <!-- EL BOTÓN SOLO SE MUESTRA SI CP === CP_CONFIRMADO -->
                <?php if (!empty($cp_confirmado) && $cp === $cp_confirmado): ?>
                    <button type="submit" name="btn_guardar" value="1" class="btn-sira btn-primary form-btn-full">
                        Guardar Finca Permanentemente
                    </button>
                <?php else: ?>
                    <div style="flex: 2; background: rgba(255,255,255,0.05); color: var(--color-text-muted); padding: 0.8rem; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 600; cursor: not-allowed; border: 1px dashed rgba(255,255,255,0.2); text-align: center; line-height: 1.4;">
                        🔒 Rellene los campos obligatorios (*) <br>y valide el CP para continuar
                    </div>
                <?php endif; ?>
                
                <a href="../dashboard.php?cliente_id=<?= $cliente_id_seleccionado ?>" class="btn-sira btn-secondary" style="flex: 1;">
                    Cancelar
                </a>
            </div>
        </form>

    </div>

<?php require_once '../includes/footer.php'; ?>
