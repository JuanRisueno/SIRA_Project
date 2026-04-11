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

$cp_a_editar = $_GET['cp'] ?? null;
if (!$cp_a_editar) {
    header("Location: ../dashboard.php?seccion=localidades");
    exit();
}

$error_msg = "";
$success_msg = "";
$loc_data = null;

// 1. Obtener datos actuales
$api_get_url = SIRA_API_BASE . "/api/v1/localidades/$cp_a_editar";
$ch = curl_init($api_get_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $loc_data = json_decode($response, true);
} else {
    header("Location: ../dashboard.php?seccion=localidades&error=no_existe");
    exit();
}

// 2. Procesar actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $municipio = $_POST['municipio'] ?? '';
    $provincia = $_POST['provincia'] ?? '';

    $api_put_url = SIRA_API_BASE . "/api/v1/localidades/$cp_a_editar";
    $data = ["municipio" => $municipio, "provincia" => $provincia];
    
    $ch = curl_init($api_put_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $success_msg = "Localidad actualizada correctamente.";
        $loc_data = json_decode($response, true);
    } else {
        $res_data = json_decode($response, true);
        $error_msg = $res_data['detail'] ?? "Error al actualizar (Código: $http_code)";
    }
}

$page_title = "SIRA - Editar Localidad";
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
        <a href="#">Editar</a>
    </div>

    <div class="user-form-container card" style="max-width: 600px; margin: 0 auto; background: var(--color-bg-card); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-card); backdrop-filter: blur(10px);">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">✏️ Editar Localidad</h1>
            <p class="dashboard-subtitle">Modificando datos para el CP <strong><?= htmlspecialchars($cp_a_editar) ?></strong></p>
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
                        <a href="../dashboard.php?seccion=localidades" class="btn-sira btn-primary" style="min-width: 180px;">Volver a la Lista</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <p style="color: var(--color-primary); font-size: 0.85rem; margin-bottom: 2rem;">(*) Campos obligatorios</p>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-text-muted);">Código Postal (No editable)</label>
                <input type="text" value="<?= htmlspecialchars($cp_a_editar) ?>" disabled style="width: 100%; padding: 0.8rem; border-radius: 10px; background: rgba(0,0,0,0.2); border: 1px solid var(--border-input); color: var(--color-text-muted); cursor: not-allowed;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Municipio (*)</label>
                <input type="text" name="municipio" required value="<?= htmlspecialchars($loc_data['municipio']) ?>" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Provincia (*)</label>
                <input type="text" name="provincia" required value="<?= htmlspecialchars($loc_data['provincia']) ?>" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2.5rem;">
                <button type="submit" class="btn-sira btn-primary" style="flex: 2;">
                    Guardar Cambios
                </button>
                <a href="../dashboard.php?seccion=localidades" class="btn-sira btn-secondary" style="flex: 1;">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
