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
$inv_data = null;

// 1. Obtener datos actuales del invernadero
$api_get_url = SIRA_API_BASE . "/api/v1/invernaderos/$id_a_editar";
$ch = curl_init($api_get_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
$res = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $inv_data = json_decode($res, true);
} else {
    header("Location: ../dashboard.php?error=invernadero_no_encontrado");
    exit();
}

// 2. Obtener catálogo de cultivos
$api_cultivos_url = SIRA_API_BASE . "/api/v1/cultivos/";
$ch_c = curl_init($api_cultivos_url);
curl_setopt($ch_c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_c, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
$res_c = curl_exec($ch_c);
$cultivos_data = (curl_getinfo($ch_c, CURLINFO_HTTP_CODE) == 200) ? json_decode($res_c, true) : [];
curl_close($ch_c);

// 3. Procesar Postback
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $cultivo_id = $_POST['cultivo_id'] ?? null;
    
    // Estos campos solo son procesables si es admin/root
    $largo_m = $_POST['largo_m'] ?? $inv_data['largo_m'];
    $ancho_m = $_POST['ancho_m'] ?? $inv_data['ancho_m'];
    $fecha_plantacion = $_POST['fecha_plantacion'] ?? $inv_data['fecha_plantacion'];

    $data_update = [
        "nombre" => $nombre,
        "cultivo_id" => !empty($cultivo_id) ? (int)$cultivo_id : null,
    ];

    // Solo añadimos dimensiones y fecha si el usuario tiene permiso
    if (in_array($user_rol, ['admin', 'root'])) {
        $data_update["largo_m"] = (float)$largo_m;
        $data_update["ancho_m"] = (float)$ancho_m;
        $data_update["fecha_plantacion"] = !empty($fecha_plantacion) ? $fecha_plantacion : null;
    }

    $api_put_url = SIRA_API_BASE . "/api/v1/invernaderos/$id_a_editar";
    $ch_u = curl_init($api_put_url);
    curl_setopt($ch_u, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_u, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch_u, CURLOPT_POSTFIELDS, json_encode($data_update));
    curl_setopt($ch_u, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);

    $response = curl_exec($ch_u);
    $http_code_u = curl_getinfo($ch_u, CURLINFO_HTTP_CODE);
    curl_close($ch_u);

    if ($http_code_u == 200) {
        $success_msg = "Los datos del invernadero han sido actualizados.";
        $inv_data = json_decode($response, true);
    } else {
        $res_err = json_decode($response, true);
        $error_msg = $res_err['detail'] ?? "Error crítico al actualizar el invernadero.";
    }
}

$page_title = "SIRA - Editar Invernadero";
$page_css   = "dashboard"; 
$es_admin_full = in_array($user_rol, ['admin', 'root']);
$attr_readonly = !$es_admin_full ? 'readonly' : '';
$bg_readonly   = !$es_admin_full ? 'rgba(0,0,0,0.1)' : 'var(--color-bg-input)';
$color_readonly = !$es_admin_full ? 'var(--color-text-muted)' : 'var(--color-text-main)';
$cursor_readonly = !$es_admin_full ? 'not-allowed' : 'text';
$from = $_GET['from'] ?? '';
$url_retorno = ($from === 'lista') ? "../dashboard.php?seccion=mis_invernaderos" : "../dashboard.php?parcela_id={$inv_data['parcela_id']}&localidad_cp=".urlencode($inv_data['parcela']['codigo_postal']).($inv_data['parcela']['cliente_id'] ? '&cliente_id='.$inv_data['parcela']['cliente_id'] : '');

require_once '../includes/header.php';
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <a href="../dashboard.php?cliente_id=<?= $inv_data['parcela']['cliente_id'] ?>">Fincas</a>
        <span>/</span>
        <a href="#">Editar Invernadero</a>
    </div>

    <div class="user-form-container card" style="max-width: 800px; margin: 0 auto; background: var(--color-bg-card); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-card); backdrop-filter: blur(10px);">
        
        <div style="margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem;">
                <h1 class="dashboard-title" style="margin:0;">📝 Editar Invernadero</h1>
                <span style="background: var(--color-primary-glow); color: var(--color-primary); padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; border: 1px solid var(--color-primary-border);">
                    ID #<?= $id_a_editar ?>
                </span>
            </div>
            <p class="dashboard-subtitle">Ajuste de parámetros y asignación de cultivos.</p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background: var(--color-error-bg); border-left: 4px solid var(--color-error); color: var(--color-error-text); padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius-sm);">
                <strong>⚠️ Error:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="confirm-overlay">
                <div class="confirm-card" style="border-color: #10b981;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">✨</div>
                    <h2 style="color: #34d399;">Cambios Aplicados</h2>
                    <p><?= htmlspecialchars($success_msg) ?></p>
                    <div class="confirm-actions">
                        <a href="<?= $url_retorno ?>" class="btn-sira btn-primary" style="min-width: 200px;">Volver al Listado</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <p style="color: var(--color-primary); font-size: 0.85rem; margin-bottom: 2rem;">(*) Campos obligatorios</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                
                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Alias del Invernadero (*)</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($inv_data['nombre']) ?>" required maxlength="50" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Largo (m) (*)</label>
                    <input type="number" step="0.01" name="largo_m" value="<?= (float)$inv_data['largo_m'] ?>" required <?= $attr_readonly ?> 
                           style="width: 100%; padding: 0.8rem; border-radius: 10px; background: <?= $bg_readonly ?>; border: 1px solid var(--border-input); color: <?= $color_readonly ?>; cursor: <?= $cursor_readonly ?>;">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Ancho (m) (*)</label>
                    <input type="number" step="0.01" name="ancho_m" value="<?= (float)$inv_data['ancho_m'] ?>" required <?= $attr_readonly ?> 
                           style="width: 100%; padding: 0.8rem; border-radius: 10px; background: <?= $bg_readonly ?>; border: 1px solid var(--border-input); color: <?= $color_readonly ?>; cursor: <?= $cursor_readonly ?>;">
                </div>

                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Cultivo Actual</label>
                        <select name="cultivo_id" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                            <option value="">-- Sin asignar (Barbecho) --</option>
                            <?php foreach ($cultivos_data as $c): ?>
                                <option value="<?= $c['cultivo_id'] ?>" <?= ($inv_data['cultivo_id'] == $c['cultivo_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre_cultivo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Fecha de Inicio de Campaña</label>
                        <input type="date" name="fecha_plantacion" value="<?= $inv_data['fecha_plantacion'] ?>" <?= $attr_readonly ?> 
                               style="width: 100%; padding: 0.8rem; border-radius: 10px; background: <?= $bg_readonly ?>; border: 1px solid var(--border-input); color: <?= $color_readonly ?>; cursor: <?= $cursor_readonly ?>;">
                        <?php if (!$es_admin_full): ?>
                            <small style="color: var(--color-text-muted); display: block; margin-top: 0.4rem;">💡 Las dimensiones y fechas solo pueden ser alteradas por Soporte Técnico.</small>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2.5rem;">
                <button type="submit" class="btn-sira btn-primary" style="flex: 2;">
                    Guardar Cambios
                </button>
                <a href="<?= $url_retorno ?>" class="btn-sira btn-secondary" style="flex: 1;">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
