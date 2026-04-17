<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: ../index.php"); exit(); }

require_once '../includes/config.php';

$token = $_SESSION['jwt_token'];
$user_rol = $_SESSION['user_rol'] ?? '';

// Solo admin, root y cliente pueden entrar aquí
if (!in_array($user_rol, ['admin', 'root', 'cliente'])) {
    header("Location: ../dashboard.php");
    exit();
}

$id_a_gestionar = isset($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit = ($id_a_gestionar !== null);

$error_msg = "";
$success_msg = "";
$geo_status_msg = "";
$parcela_data = null;

// 1. Obtener datos si es edición
if ($is_edit) {
    $api_get_url = SIRA_API_BASE . "/api/v1/parcelas/$id_a_gestionar";
    $ch = curl_init($api_get_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $parcela_data = json_decode($response, true);
        // Seguridad: Un cliente solo puede editar sus propias parcelas
        if ($user_rol === 'cliente' && $parcela_data['cliente_id'] !== (int)$_SESSION['cliente_id']) {
            header("Location: ../dashboard.php?error=acceso_denegado");
            exit();
        }
    } else {
        header("Location: ../dashboard.php?error=parcela_no_encontrada");
        exit();
    }
}

// Determinar cliente_id objetivo
$cliente_id_obj = $is_edit 
    ? $parcela_data['cliente_id'] 
    : (isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : (($user_rol === 'cliente') ? ($_SESSION['cliente_id'] ?? null) : null));

if (!$cliente_id_obj) {
    header("Location: ../dashboard.php?error=cliente_no_especificado");
    exit();
}

// Variables de estado (priorizan POST)
$nombre = $_POST['nombre'] ?? ($is_edit ? ($parcela_data['nombre'] ?? '') : '');
$ref_catastral = $_POST['ref_catastral'] ?? ($is_edit ? ($parcela_data['ref_catastral'] ?? '') : '');
$direccion = $_POST['direccion'] ?? ($is_edit ? ($parcela_data['direccion'] ?? '') : '');
$cp = $_POST['cp'] ?? ($is_edit ? ($parcela_data['codigo_postal'] ?? '') : '');
$municipio = $_POST['municipio'] ?? ($is_edit ? ($parcela_data['localidad']['municipio'] ?? '') : '');
$provincia = $_POST['provincia'] ?? ($is_edit ? ($parcela_data['localidad']['provincia'] ?? '') : '');
$es_nuevo_cp = $_POST['es_nuevo_cp'] ?? '0';
$cp_confirmado = $_POST['cp_confirmado'] ?? ($is_edit ? $cp : '');

$es_cliente = ($user_rol === 'cliente');

// 2. Procesar Acciones
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // CASO A: VALIDAR CP
    if (isset($_POST['btn_validar_cp']) && (!$es_cliente || !$is_edit)) {
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
                $cp_confirmado = $cp;
                $geo_status_msg = ($es_nuevo_cp === '0') ? "✅ Localización validada." : "🌍 CP Externo validado.";
            } else {
                $error_msg = "Código Postal no reconocido.";
                $cp_confirmado = "";
            }
        }
    }

    // CASO B: GUARDAR / ACTUALIZAR
    elseif (isset($_POST['btn_guardar'])) {
        if (!$es_cliente && ($cp !== $cp_confirmado || empty($cp_confirmado))) {
            $error_msg = "⚠️ ERROR: CP no validado.";
        } else {
            // Si el CP es nuevo (y somos admin/root), registrar localidad
            if ($es_nuevo_cp === '1' && !$es_cliente) {
                $loc_api = SIRA_API_BASE . "/api/v1/localidades/";
                $loc_data = ["codigo_postal" => $cp, "municipio" => $municipio, "provincia" => $provincia];
                $ch = curl_init($loc_api);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loc_data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
                curl_exec($ch);
                curl_close($ch);
            }

            $api_url = $is_edit ? SIRA_API_BASE . "/api/v1/parcelas/$id_a_gestionar" : SIRA_API_BASE . "/api/v1/parcelas/";
            $method = $is_edit ? "PUT" : "POST";
            
            $data = [
                "nombre" => $nombre ?: null,
                "codigo_postal" => $cp,
                "ref_catastral" => $ref_catastral,
                "direccion" => $direccion
            ];
            if (!$is_edit) $data["cliente_id"] = $cliente_id_obj;

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json", "Accept: application/json"]);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($is_edit && $http_code == 200) || (!$is_edit && $http_code == 201)) {
                $success_msg = $is_edit ? "Parcela actualizada correctamente." : "Parcela registrada correctamente.";
                $auto_redirect = $url_retorno;
                if ($is_edit) $parcela_data = json_decode($response, true);
            } else {
                $res_data = json_decode($response, true);
                $error_msg = $res_data['detail'] ?? "Error en la operación de guardado.";
            }
        }
    }
}

$page_title = "SIRA - " . ($is_edit ? "Editar Parcela" : "Añadir Parcela");
$page_css   = "dashboard";
$from = $_GET['from'] ?? '';
$url_retorno = ($from === 'lista') ? "../dashboard.php?seccion=mis_parcelas" : "../dashboard.php?cliente_id=$cliente_id_obj&localidad_cp=" . urlencode($cp);

require_once '../includes/header.php';
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <a href="../dashboard.php?cliente_id=<?= $cliente_id_obj ?>">Entorno Cliente</a>
        <span>/</span>
        <a href="#"><?= $is_edit ? "Editar Parcela" : "Añadir Parcela" ?></a>
    </div>

    <div class="user-form-container">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title"><?= $is_edit ? "✏️ Editar Parcela" : "➕ Añadir Nueva Parcela" ?></h1>
            <p class="dashboard-subtitle"><?= $is_edit ? "Ajustes de infraestructura para ID #$id_a_gestionar" : "Registro de infraestructuras (SIRA Gating System)." ?></p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background: var(--color-error-bg); border-left: 4px solid var(--color-error); color: var(--color-error-text); padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius-sm);">
                <strong>⚠️ Alerta:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="confirm-overlay">
                <div class="confirm-card" style="border-color: #10b981;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🍃</div>
                    <h2 style="color: #34d399;"><?= $is_edit ? "Cambios Guardados" : "Parcela Registrada" ?></h2>
                    <p style="margin-bottom: 0.5rem;"><?= htmlspecialchars($success_msg) ?></p>
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

        <form method="POST" class="sira-form">
            <?php if ($es_cliente && $is_edit): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--color-primary); color: var(--color-text-main); padding: 1.2rem; margin-bottom: 2rem; border-radius: 10px; font-size: 0.9rem; line-height: 1.5;">
                    💡 <strong>Nota:</strong> Como cliente, puedes personalizar el nombre (alias). La ubicación legal está bloqueada.
                </div>
            <?php endif; ?>

            <p class="form-required-label">(*) Campos obligatorios</p>
            <input type="hidden" name="es_nuevo_cp" value="<?= htmlspecialchars($es_nuevo_cp) ?>">
            <input type="hidden" name="cp_confirmado" value="<?= htmlspecialchars($cp_confirmado) ?>">
            
            <?php if (!$es_cliente): ?>
            <div class="sira-gating-box">
                🔒 <strong>SIRA Gating System:</strong> Es obligatorio validar el Código Postal antes de guardar.
            </div>
            <?php endif; ?>

            <div class="form-premium-grid">
                
                <?php if ($is_edit): ?>
                <div class="form-col-2">
                    <div class="input-group-premium">
                        <label style="color: var(--color-text-muted);">Propietario (No editable)</label>
                        <input type="text" value="<?= htmlspecialchars($parcela_data['cliente']['nombre_empresa'] ?? 'Cliente #'.$cliente_id_obj) ?>" disabled class="input-readonly">
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-col-2">
                    <div class="input-group-premium">
                        <label>Nombre de la Parcela (Alias)</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" placeholder="Ej. Finca de los Olivos...">
                    </div>
                </div>

                <div class="form-col-2">
                    <div class="input-group-premium">
                        <label>Referencia Catastral (*)</label>
                        <input type="text" name="ref_catastral" required maxlength="14" minlength="14" value="<?= htmlspecialchars($ref_catastral) ?>" placeholder="Ej. 1234567AB1234C" <?= ($es_cliente && $is_edit) ? 'readonly class="input-readonly"' : '' ?>>
                    </div>
                </div>

                <div class="form-col-2">
                    <div class="input-group-premium">
                        <label>Dirección de la Parcela (*)</label>
                        <input type="text" name="direccion" required value="<?= htmlspecialchars($direccion) ?>" placeholder="Ej. Polígono 4, Parcela 12..." <?= ($es_cliente && $is_edit) ? 'readonly class="input-readonly"' : '' ?>>
                    </div>
                </div>

                <div class="input-group-premium">
                    <label>Código Postal (*)</label>
                    <div class="input-group-inline">
                        <input type="text" name="cp" value="<?= htmlspecialchars($cp) ?>" required maxlength="5" minlength="5" placeholder="04001" <?= ($es_cliente && $is_edit) ? 'readonly class="input-readonly"' : '' ?>>
                        <?php if (!$es_cliente): ?>
                            <button type="submit" name="btn_validar_cp" value="1" class="btn-sira btn-secondary" style="padding: 0 1rem; font-size: 0.8rem;">Validar CP</button>
                        <?php endif; ?>
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
            <div class="geo-status-msg" style="margin-top: 1rem; font-size: 0.85rem; color: #34d399;">
                <?= htmlspecialchars($geo_status_msg) ?>
            </div>
            <?php endif; ?>

            <div class="form-footer-actions">
                <?php if ($es_cliente || ($is_edit && $cp === $cp_confirmado) || (!empty($cp_confirmado) && $cp === $cp_confirmado)): ?>
                    <button type="submit" name="btn_guardar" value="1" class="btn-sira btn-primary">
                        <?= $is_edit ? 'Guardar Cambios' : 'Registrar Finca' ?>
                    </button>
                <?php else: ?>
                    <div class="gating-lock-msg" style="flex: 2; background: rgba(255,255,255,0.05); color: var(--color-text-muted); padding: 0.8rem; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 600; cursor: not-allowed; border: 1px dashed rgba(255,255,255,0.2); text-align: center;">
                        🔒 Valide el CP para continuar
                    </div>
                <?php endif; ?>
                
                <a href="<?= $url_retorno ?>" class="btn-sira btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
