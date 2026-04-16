<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: ../index.php"); exit(); }

require_once '../includes/config.php';

$token = $_SESSION['jwt_token'];
$user_rol = $_SESSION['user_rol'] ?? '';

// Solo admin, root y el propio cliente pueden entrar aquí
if (!in_array($user_rol, ['admin', 'root', 'cliente'])) {
    header("Location: ../dashboard.php");
    exit();
}

$cliente_id_seleccionado = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : ( ($user_rol === 'cliente') ? ($_SESSION['cliente_id'] ?? null) : null );

// Seguridad: Un cliente solo puede añadir parcelas para sí mismo
if (!$cliente_id_seleccionado || ($user_rol === 'cliente' && $cliente_id_seleccionado !== (int)$_SESSION['cliente_id'])) {
    header("Location: ../dashboard.php?error=acceso_denegado");
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

    <div class="user-form-container">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">➕ Añadir Nueva Parcela</h1>
            <p class="dashboard-subtitle">Registro centralizado de infraestructuras (SIRA Gating System).</p>
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
            <p class="form-required-label">(*) Campos obligatorios</p>

            <input type="hidden" name="es_nuevo_cp" value="<?= htmlspecialchars($es_nuevo_cp) ?>">
            <input type="hidden" name="cp_confirmado" value="<?= htmlspecialchars($cp_confirmado) ?>">
            
            <div class="sira-gating-box">
                🔒 <strong>SIRA Gating System:</strong> Es obligatorio validar el Código Postal antes de poder guardar la finca. Cualquier cambio en los datos invalidará la sesión de guardado.
            </div>

            <div class="form-premium-grid">
                <!-- CAMPOS BASE -->
                <div class="form-col-2">
                    <div class="input-group-premium">
                        <label>Nombre de la Parcela (Alias)</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" placeholder="Ej. Finca de los Olivos, Sector Norte...">
                    </div>
                </div>

                <div class="form-col-2">
                    <div class="input-group-premium">
                        <label>Referencia Catastral (*)</label>
                        <input type="text" name="ref_catastral" required maxlength="14" minlength="14" value="<?= htmlspecialchars($ref_catastral) ?>" placeholder="Ej. 1234567AB1234C">
                    </div>
                </div>

                <div class="form-col-2">
                    <div class="input-group-premium">
                        <label>Dirección de la Parcela (*)</label>
                        <input type="text" name="direccion" required value="<?= htmlspecialchars($direccion) ?>" placeholder="Ej. Polígono 4, Parcela 12...">
                    </div>
                </div>

                <!-- SECCIÓN GEO -->
                <div class="input-group-premium">
                    <label>Código Postal (*)</label>
                    <div class="input-group-inline">
                        <input type="text" name="cp" value="<?= htmlspecialchars($cp) ?>" required maxlength="5" minlength="5" placeholder="04001">
                        <button type="submit" name="btn_validar_cp" value="1" class="btn-sira btn-secondary" style="padding: 0 1rem; font-size: 0.8rem;">Validar CP</button>
                    </div>
                </div>

                <div class="input-group-premium">
                    <label>Municipio</label>
                    <input type="text" name="municipio" value="<?= htmlspecialchars($municipio) ?>" readonly placeholder="Validación obligatoria..." class="input-readonly">
                </div>

                <div class="form-col-2">
                    <div class="input-group-premium">
                        <label>Provincia</label>
                        <input type="text" name="provincia" value="<?= htmlspecialchars($provincia) ?>" readonly placeholder="Validación obligatoria..." class="input-readonly">
                    </div>
                </div>
            </div>

            <?php if ($geo_status_msg): ?>
            <div class="geo-status-msg">
                <?= htmlspecialchars($geo_status_msg) ?>
            </div>
            <?php endif; ?>

            <div class="form-footer-actions">
                <?php if (!empty($cp_confirmado) && $cp === $cp_confirmado): ?>
                    <button type="submit" name="btn_guardar" value="1" class="btn-sira btn-primary">
                        Registrar Finca
                    </button>
                <?php else: ?>
                    <div class="gating-lock-msg">
                        🔒 Rellene los campos obligatorios (*) <br>y valide el CP para continuar
                    </div>
                <?php endif; ?>
                
                <a href="../dashboard.php?cliente_id=<?= $cliente_id_seleccionado ?>" class="btn-sira btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
