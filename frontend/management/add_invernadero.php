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
$parcela_id_seleccionada = isset($_GET['parcela_id']) ? (int)$_GET['parcela_id'] : null;
$localidad_cp_seleccionada = $_GET['localidad_cp'] ?? $_POST['localidad_cp'] ?? '';

if (!$cliente_id_seleccionado || !$parcela_id_seleccionada) {
    header("Location: ../dashboard.php");
    exit();
}

$error_msg = "";
$success_msg = "";

// 0. Obtener lista de cultivos para el desplegable
$api_cultivos_url = SIRA_API_BASE . "/api/v1/cultivos/";
$ch_c = curl_init($api_cultivos_url);
curl_setopt($ch_c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_c, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
$res_c = curl_exec($ch_c);
$cultivos_data = (curl_getinfo($ch_c, CURLINFO_HTTP_CODE) == 200) ? json_decode($res_c, true) : [];
curl_close($ch_c);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $largo_m = $_POST['largo_m'] ?? 0;
    $ancho_m = $_POST['ancho_m'] ?? 0;
    $cultivo_id = $_POST['cultivo_id'] ?? null;
    $fecha_plantacion = $_POST['fecha_plantacion'] ?? null;

    // 2. Crear el Invernadero
    $api_url = SIRA_API_BASE . "/api/v1/invernaderos/";
    $data = [
        "nombre" => $nombre,
        "largo_m" => (float)$largo_m,
        "ancho_m" => (float)$ancho_m,
        "parcela_id" => $parcela_id_seleccionada,
        "cultivo_id" => !empty($cultivo_id) ? (int)$cultivo_id : null, 
        "fecha_plantacion" => !empty($fecha_plantacion) ? $fecha_plantacion : null
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 201) {
        $success_msg = "Invernadero registrado correctamente.";
    } else {
        $res_data = json_decode($response, true);
        if ($http_code == 401) {
            $error_msg = "Sesión expirada o no autorizada (401). Intenta cerrar sesión y volver a entrar.";
        } else {
            $error_msg = "Error (" . $http_code . "): " . ($res_data['detail'] ?? "Error desconocido en la API.");
            if (is_array($error_msg)) {
                $error_msg = json_encode($error_msg);
            }
        }
    }
}

$page_title = "SIRA - Añadir Invernadero Inteligente";
$page_css   = "dashboard"; 
require_once '../includes/header.php';
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <a href="../dashboard.php?cliente_id=<?= $cliente_id_seleccionado ?>&localidad_cp=<?= urlencode($localidad_cp_seleccionada) ?>">Zonas Geográficas</a>
        <span>/</span>
        <a href="../dashboard.php?cliente_id=<?= $cliente_id_seleccionado ?>&localidad_cp=<?= urlencode($localidad_cp_seleccionada) ?>&parcela_id=<?= $parcela_id_seleccionada ?>">Parcela</a>
        <span>/</span>
        <a href="#">Añadir Invernadero</a>
    </div>

    <div class="user-form-container card" style="max-width: 800px; margin: 0 auto; background: var(--color-bg-card); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-card); backdrop-filter: blur(10px);">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">🏠 Añadir Nuevo Invernadero</h1>
            <p class="dashboard-subtitle">Define las dimensiones y características de la nueva zona de cultivo.</p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background: var(--color-error-bg); border-left: 4px solid var(--color-error); color: var(--color-error-text); padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius-sm);">
                <strong>⚠️ Error:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="confirm-overlay">
                <div class="confirm-card" style="border-color: #10b981;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🏠</div>
                    <h2 style="color: #34d399;">Invernadero Registrado</h2>
                    <p><?= htmlspecialchars($success_msg) ?></p>
                    <div class="confirm-actions" style="display: flex; flex-direction: column; gap: 10px; width: 100%;">
                        <a href="../dashboard.php?localidad_cp=<?= urlencode($localidad_cp_seleccionada) ?>&cliente_id=<?= $cliente_id_seleccionado ?>&parcela_id=<?= $parcela_id_seleccionada ?>" class="btn-sira btn-primary">Volver a la Parcela</a>
                        <a href="add_invernadero.php?cliente_id=<?= $cliente_id_seleccionado ?>&parcela_id=<?= $parcela_id_seleccionada ?>&localidad_cp=<?= urlencode($localidad_cp_seleccionada) ?>" class="btn-sira btn-secondary">Registrar otro Invernadero</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form" id="invernadero-form">
            <p style="color: var(--color-primary); font-size: 0.85rem; margin-bottom: 2rem;">(*) Campos obligatorios</p>
                        
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                
                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Nombre del Invernadero (*)</label>
                        <input type="text" name="nombre" required maxlength="50" placeholder="Ej. Sector Norte - Fase 1" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Largo (m) (*)</label>
                    <input type="number" step="0.01" name="largo_m" required placeholder="Ej. 120.50" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Ancho (m) (*)</label>
                    <input type="number" step="0.01" name="ancho_m" required placeholder="Ej. 45.00" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Cultivo Actual (Opcional)</label>
                        <select name="cultivo_id" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                            <option value="">-- Sin asignar (Barbecho) --</option>
                            <?php foreach ($cultivos_data as $c): ?>
                                <option value="<?= $c['cultivo_id'] ?>"><?= htmlspecialchars($c['nombre_cultivo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Fecha de Plantación (Opcional)</label>
                        <input type="date" name="fecha_plantacion" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                        <small style="color: var(--color-text-muted); display: block; margin-top: 0.4rem;">Si aún no has plantado nada, puedes dejar este campo vacío.</small>
                    </div>
                </div>

            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2.5rem;">
                <button type="submit" id="save-btn" class="btn-sira btn-primary" style="flex: 2;">
                    Registrar Invernadero
                </button>
                <a href="../dashboard.php?localidad_cp=<?= urlencode($localidad_cp_seleccionada) ?>&cliente_id=<?= $cliente_id_seleccionado ?>&parcela_id=<?= $parcela_id_seleccionada ?>" class="btn-sira btn-secondary" style="flex: 1;">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
