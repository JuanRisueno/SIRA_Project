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

$id_a_editar = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_a_editar) {
    header("Location: ../dashboard.php");
    exit();
}

$error_msg = "";
$success_msg = "";
$geo_status_msg = "";
$parcela_data = null;

// 1. Obtener datos actuales de la parcela (Siempre al inicio para lectura)
$api_get_url = SIRA_API_BASE . "/api/v1/parcelas/$id_a_editar";
$ch = curl_init($api_get_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $parcela_data = json_decode($response, true);
} else {
    header("Location: ../dashboard.php?error=parcela_no_encontrada");
    exit();
}

// Variables para mantener el estado (priorizan POST si existe)
$nombre = $_POST['nombre'] ?? ($parcela_data['nombre'] ?? '');
$ref_catastral = $_POST['ref_catastral'] ?? ($parcela_data['ref_catastral'] ?? '');
$direccion = $_POST['direccion'] ?? ($parcela_data['direccion'] ?? '');
$cp = $_POST['cp'] ?? ($parcela_data['codigo_postal'] ?? '');
$municipio = $_POST['municipio'] ?? ($parcela_data['localidad']['municipio'] ?? '');
$provincia = $_POST['provincia'] ?? ($parcela_data['localidad']['provincia'] ?? '');
$es_nuevo_cp = $_POST['es_nuevo_cp'] ?? '0';

// 2. Procesar Postback
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CASO A: VALIDAR CP
    if (isset($_POST['btn_validar_cp'])) {
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
            $geo_status_msg = ($es_nuevo_cp === '0') ? "✅ Localización validada." : "🌍 Localidad externa validada.";
        } else {
            $geo_status_msg = "⚠️ CP no reconocido.";
            $es_nuevo_cp = "1";
        }
    }

    // CASO B: ACTUALIZAR
    elseif (isset($_POST['btn_guardar'])) {
        // A) Si el CP es nuevo, registrar localidad
        if ($es_nuevo_cp === '1') {
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

        // B) Actualizar Parcela
        $api_put_url = SIRA_API_BASE . "/api/v1/parcelas/$id_a_editar";
        $data = [
            "nombre" => $nombre ?: null,
            "codigo_postal" => $cp,
            "ref_catastral" => $ref_catastral,
            "direccion" => $direccion
        ];

        $ch = curl_init($api_put_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json", "Accept: application/json"]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $success_msg = "Cambios guardados correctamente en el servidor.";
            $parcela_data = json_decode($response, true);
        } else {
            $res_data = json_decode($response, true);
            $error_msg = $res_data['detail'] ?? "Error al actualizar la finca.";
        }
    }
}

$page_title = "SIRA - Editar Parcela";
$page_css   = "dashboard"; 
$es_cliente = ($user_rol === 'cliente');
$attr_readonly = $es_cliente ? 'readonly style="width: 100%; padding: 0.8rem; border-radius: 10px; background: rgba(0,0,0,0.1); border: 1px solid var(--border-input); color: var(--color-text-muted); cursor: not-allowed;"' : 'style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);"';

require_once '../includes/header.php';
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <a href="../dashboard.php?cliente_id=<?= $parcela_data['cliente_id'] ?>">Entorno Cliente</a>
        <span>/</span>
        <a href="#">Editar Parcela</a>
    </div>

    <div class="user-form-container card" style="max-width: 800px; margin: 0 auto; background: var(--color-bg-card); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-card); backdrop-filter: blur(10px);">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">✏️ Editar Datos Técnicos</h1>
            <p class="dashboard-subtitle">Ajustes de infraestructura para Parcela ID #<?= $id_a_editar ?> (Zero-JS Mode).</p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background: var(--color-error-bg); border-left: 4px solid var(--color-error); color: var(--color-error-text); padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius-sm);">
                <strong>⚠️ Error:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="confirm-overlay">
                <div class="confirm-card" style="border-color: #10b981;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">✅</div>
                    <h2 style="color: #34d399;">Cambios Guardados</h2>
                    <p><?= htmlspecialchars($success_msg) ?></p>
                    <div class="confirm-actions">
                        <a href="../dashboard.php?cliente_id=<?= $parcela_data['cliente_id'] ?>" class="btn-sira btn-primary" style="min-width: 180px;">Volver al Entorno</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <p style="color: var(--color-primary); font-size: 0.85rem; margin-bottom: 2rem;">(*) Campos obligatorios</p>
            <input type="hidden" name="es_nuevo_cp" value="<?= htmlspecialchars($es_nuevo_cp) ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                
                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-text-muted);">Propietario (No editable)</label>
                        <input type="text" value="<?= htmlspecialchars($parcela_data['cliente']['nombre_empresa'] ?? 'Cliente #'.$parcela_data['cliente_id']) ?>" disabled style="width: 100%; padding: 0.8rem; border-radius: 10px; background: rgba(0,0,0,0.2); border: 1px solid var(--border-input); color: var(--color-text-muted); cursor: not-allowed;">
                    </div>
                </div>

                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Nombre de la Parcela (Alias)</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" placeholder="Ej. Finca de los Olivos, Parcela Norte..." style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                    </div>
                </div>

                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: <?= $es_cliente ? 'var(--color-text-muted)' : 'var(--color-primary)' ?>;">Referencia Catastral (*) <?= $es_cliente ? '(Solo lectura)' : '' ?></label>
                        <input type="text" name="ref_catastral" required maxlength="14" minlength="14" value="<?= htmlspecialchars($ref_catastral) ?>" <?= $es_cliente ? 'readonly' : '' ?> <?= $attr_readonly ?> >
                    </div>
                </div>

                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: <?= $es_cliente ? 'var(--color-text-muted)' : 'var(--color-primary)' ?>;">Dirección de la Parcela (*) <?= $es_cliente ? '(Solo lectura)' : '' ?></label>
                        <input type="text" name="direccion" required value="<?= htmlspecialchars($direccion) ?>" <?= $es_cliente ? 'readonly' : '' ?> <?= $attr_readonly ?>>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: <?= $es_cliente ? 'var(--color-text-muted)' : 'var(--color-primary)' ?>;">Código Postal (*)</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" name="cp" value="<?= htmlspecialchars($cp) ?>" required maxlength="5" minlength="5" <?= $es_cliente ? 'readonly' : '' ?> <?= $attr_readonly ?>>
                        <?php if (!$es_cliente): ?>
                            <button type="submit" name="btn_validar_cp" value="1" class="btn-sira btn-secondary" style="padding: 0 1rem; font-size: 0.8rem;">Validar CP</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-text-muted);">Municipio</label>
                    <input type="text" name="municipio" value="<?= htmlspecialchars($municipio) ?>" required <?= ($es_nuevo_cp === '0' || $es_cliente) ? 'readonly' : '' ?> <?= $attr_readonly ?>>
                </div>
 
                <div class="form-group" style="grid-column: span 2;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-text-muted);">Provincia</label>
                    <input type="text" name="provincia" value="<?= htmlspecialchars($provincia) ?>" required <?= ($es_nuevo_cp === '0' || $es_cliente) ? 'readonly' : '' ?> <?= $attr_readonly ?>>
                </div>

            </div>

            <?php if ($geo_status_msg): ?>
            <div style="margin-top: 1rem; font-size: 0.85rem; color: #34d399;">
                <?= htmlspecialchars($geo_status_msg) ?>
            </div>
            <?php endif; ?>

            <div style="display: flex; gap: 1rem; margin-top: 2.5rem;">
                <button type="submit" name="btn_guardar" value="1" class="btn-sira btn-primary" style="flex: 2;">
                    Guardar Cambios
                </button>
                <a href="../dashboard.php?cliente_id=<?= $parcela_data['cliente_id'] ?>" class="btn-sira btn-secondary" style="flex: 1;">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
