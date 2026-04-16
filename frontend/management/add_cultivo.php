<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: ../index.php"); exit(); }

require_once '../includes/config.php';

$token = $_SESSION['jwt_token'];
$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_cultivo'] ?? '');
    $api_id = trim($_POST['external_api_id'] ?? '');

    if (empty($nombre)) {
        $error_msg = "El nombre del cultivo es obligatorio.";
    } else {
        $api_url = SIRA_API_BASE . "/api/v1/cultivos/";
        
        // [NUEVO V5.1] Estructura anidada
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
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 201) {
            $success_msg = "Cultivo y parámetros de salud registrados correctamente.";
        } else {
            $res_data = json_decode($response, true);
            $error_msg = $res_data['detail'] ?? "Error al registrar el cultivo.";
        }
    }
}

$page_title = "SIRA - Añadir Cultivo";
$page_css   = "dashboard";
require_once '../includes/header.php';
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <a href="../dashboard.php?seccion=cultivos">Catálogo de Cultivos</a>
        <span>/</span>
        <a href="#">Nuevo Cultivo</a>
    </div>

    <div class="user-form-container card">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">🌱 Nuevo Cultivo</h1>
            <p class="dashboard-subtitle">Define el nombre y los parámetros óptimos de salud.</p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background: rgba(231, 76, 60, 0.1); border-left: 4px solid var(--color-error); color: var(--color-error); padding: 1rem; margin-bottom: 1.5rem;">
                <strong>⚠️ Error:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="confirm-overlay">
                <div class="confirm-card" style="border-color: #2ecc71;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🍃</div>
                    <h2 style="color: #2ecc71;">Cultivo Registrado</h2>
                    <p><?= htmlspecialchars($success_msg) ?></p>
                    <div class="confirm-actions">
                        <a href="../dashboard.php?seccion=cultivos" class="btn-sira btn-primary">Volver al Catálogo</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <!-- Sección 1: Datos Básicos -->
            <div class="form-section-premium">
                <h3 class="section-title-premium">1. IDENTIFICACIÓN</h3>
                <div class="input-group-premium">
                    <label>Nombre del Cultivo (*)</label>
                    <input type="text" name="nombre_cultivo" placeholder="Ej. Tomate Cherry" required>
                </div>
            </div>

            <!-- Sección 2: Parámetros Salud -->
            <div class="form-section-premium" style="border-bottom: none;">
                <h3 class="section-title-premium">2. PARÁMETROS ÓPTIMOS (FASE GENERAL)</h3>
                
                <div class="form-premium-grid">
                    <div class="input-group-premium">
                        <label>🌡️ Temp. Mín (ºC)</label>
                        <input type="number" step="0.1" name="temp_min" value="15.0" required>
                    </div>
                    <div class="input-group-premium">
                        <label>🔥 Temp. Máx (ºC)</label>
                        <input type="number" step="0.1" name="temp_max" value="30.0" required>
                    </div>
                    <div class="input-group-premium">
                        <label>💧 Humedad Mín (%)</label>
                        <input type="number" step="0.1" name="hum_min" value="60.0" required>
                    </div>
                    <div class="input-group-premium">
                        <label>🌫️ Humedad Máx (%)</label>
                        <input type="number" step="0.1" name="hum_max" value="80.0" required>
                    </div>
                    <div class="input-group-premium">
                        <label>🚿 Riego Diario (L/m²)</label>
                        <input type="number" step="0.01" name="riego" value="4.50" required>
                    </div>
                    <div class="input-group-premium">
                        <label>🧪 pH Suelo Ideal</label>
                        <input type="number" step="0.1" name="ph" value="6.5">
                    </div>
                </div>
            </div>

            <div class="form-footer-actions">
                <button type="submit" class="btn-sira btn-primary form-btn-full">Registrar Cultivo Completo</button>
                <a href="../dashboard.php?seccion=cultivos" class="btn-sira btn-secondary" style="flex: 1;">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
