<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: ../index.php"); exit(); }

require_once '../includes/config.php';

$token = $_SESSION['jwt_token'];
$user_rol = $_SESSION['user_rol'] ?? '';
$cliente_id_session = $_SESSION['cliente_id'] ?? null;

$id_a_editar = $_GET['id'] ?? null;
if (!$id_a_editar) {
    header("Location: ../dashboard.php?seccion=cultivos");
    exit();
}

$error_msg = "";
$success_msg = "";
$cult_data = null;

// 1. Obtener datos actuales
$api_get_url = SIRA_API_BASE . "/api/v1/cultivos/$id_a_editar";
$ch = curl_init($api_get_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $cult_data = json_decode($response, true);
    
    // VERIFICACIÓN DE PERMISOS (Frontend Safety)
    $es_dueno = ($cult_data['cliente_id'] == $cliente_id_session);
    $es_admin = in_array($user_rol, ['admin', 'root']);
    
    if (!$es_dueno && !$es_admin) {
        header("Location: ../dashboard.php?seccion=cultivos&error=sin_permiso");
        exit();
    }
} else {
    header("Location: ../dashboard.php?seccion=cultivos&error=no_existe");
    exit();
}

// 2. Procesar actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_cultivo'] ?? '');
    $api_id = trim($_POST['external_api_id'] ?? '');

    $api_put_url = SIRA_API_BASE . "/api/v1/cultivos/$id_a_editar";
    
    // [NUEVO V5.1] Datos unificados
    $data = [
        "nombre_cultivo" => $nombre, 
        "parametros" => [
            "temp_optima_min" => (float)($_POST['temp_min'] ?? 0),
            "temp_optima_max" => (float)($_POST['temp_max'] ?? 0),
            "humedad_optima_min" => (float)($_POST['hum_min'] ?? 0),
            "humedad_optima_max" => (float)($_POST['hum_max'] ?? 0),
            "necesidad_hidrica" => (float)($_POST['riego'] ?? 0),
            "ph_ideal" => ($_POST['ph'] !== '') ? (float)$_POST['ph'] : null
        ]
    ];
    
    $ch = curl_init($api_put_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $success_msg = "Cultivo y parámetros actualizados correctamente.";
        $cult_data = json_decode($response, true);
    } else {
        $res_data = json_decode($response, true);
        $error_msg = $res_data['detail'] ?? "Error al actualizar (Código: $http_code)";
    }
}

$page_title = "SIRA - Editar Cultivo";
$page_css   = "dashboard";
require_once '../includes/header.php';

// Valores por defecto si no hay parámetros
$p = $cult_data['parametros'] ?? [
    "temp_optima_min" => 15, "temp_optima_max" => 30,
    "humedad_optima_min" => 60, "humedad_optima_max" => 80,
    "necesidad_hidrica" => 0, "ph_ideal" => null
];
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <a href="../dashboard.php?seccion=cultivos">Catálogo de Cultivos</a>
        <span>/</span>
        <a href="#">Editar</a>
    </div>

    <div class="user-form-container card" style="max-width: 700px; margin: 0 auto; background: var(--color-bg-card); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">✏️ Editar Cultivo</h1>
            <p class="dashboard-subtitle">Modificando ficha técnica de <strong><?= htmlspecialchars($cult_data['nombre_cultivo']) ?></strong></p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background: rgba(231, 76, 60, 0.1); border-left: 4px solid var(--color-error); color: var(--color-error); padding: 1rem; margin-bottom: 1.5rem;">
                <strong>⚠️ Error:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="confirm-overlay">
                <div class="confirm-card" style="border-color: #2ecc71;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">✅</div>
                    <h2 style="color: #2ecc71;">Cambios Guardados</h2>
                    <p><?= htmlspecialchars($success_msg) ?></p>
                    <div class="confirm-actions">
                        <a href="../dashboard.php?seccion=cultivos" class="btn-sira btn-primary">Volver al Catálogo</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <!-- Sección 1: Datos Básicos -->
            <div style="margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="color: var(--color-primary); margin-bottom: 1rem; font-size: 1rem;">1. IDENTIFICACIÓN</h3>
                <div class="form-group">
                    <label>Nombre del Cultivo (*)</label>
                    <input type="text" name="nombre_cultivo" required value="<?= htmlspecialchars($cult_data['nombre_cultivo']) ?>">
                </div>

                <div class="form-group">
                    <label>Propietario</label>
                    <input type="text" value="<?= htmlspecialchars($cult_data['nombre_cliente'] ?? 'Sistema') ?>" disabled>
                </div>
            </div>

            <!-- Sección 2: Parámetros Salud -->
            <div style="margin-bottom: 2rem;">
                <h3 style="color: var(--color-primary); margin-bottom: 1rem; font-size: 1rem;">2. PARÁMETROS ÓPTIMOS (FASE GENERAL)</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>🌡️ Temp. Mín (ºC)</label>
                        <input type="number" step="0.1" name="temp_min" value="<?= $p['temp_optima_min'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>🔥 Temp. Máx (ºC)</label>
                        <input type="number" step="0.1" name="temp_max" value="<?= $p['temp_optima_max'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>💧 Humedad Mín (%)</label>
                        <input type="number" step="0.1" name="hum_min" value="<?= $p['humedad_optima_min'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>🌫️ Humedad Máx (%)</label>
                        <input type="number" step="0.1" name="hum_max" value="<?= $p['humedad_optima_max'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>🚿 Riego Diario (L/m²)</label>
                        <input type="number" step="0.01" name="riego" value="<?= $p['necesidad_hidrica'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>🧪 pH Suelo Ideal</label>
                        <input type="number" step="0.1" name="ph" value="<?= $p['ph_ideal'] ?>">
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn-sira btn-primary" style="flex: 2;">Guardar Cambios Completos</button>
                <a href="../dashboard.php?seccion=cultivos" class="btn-sira btn-secondary" style="flex: 1;">Cancelar</a>
            </div>
        </form>

    </div>
</div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
