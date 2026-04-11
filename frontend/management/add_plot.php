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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ref_catastral = $_POST['ref_catastral'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $cp = $_POST['cp'] ?? '';
    $municipio = $_POST['municipio'] ?? '';
    $provincia = $_POST['provincia'] ?? '';
    $es_nuevo_cp = isset($_POST['es_nuevo_cp']) && $_POST['es_nuevo_cp'] === '1';

    // 1. Si el CP es nuevo (o no estaba en BBDD local), registrar la localidad primero
    if ($es_nuevo_cp) {
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

    // 2. Crear la Parcela
    $api_url = SIRA_API_BASE . "/api/v1/parcelas/";
    $data = [
        "cliente_id" => $cliente_id_seleccionado,
        "codigo_postal" => $cp,
        "ref_catastral" => $ref_catastral,
        "direccion" => $direccion
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
        $success_msg = "Parcela activada y asignada correctamente.";
    } else {
        $res_data = json_decode($response, true);
        $error_msg = $res_data['detail'] ?? "Error crítico al registrar parcela.";
    }
}

$page_title = "SIRA - Añadir Parcela Inteligente";
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

    <div class="user-form-container card" style="max-width: 800px; margin: 0 auto; background: var(--color-bg-card); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-card); backdrop-filter: blur(10px);">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">➕ Añadir Nueva Parcela</h1>
            <p class="dashboard-subtitle">Gestión inteligente de infraestructura basada en localización geográfica.</p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background: var(--color-error-bg); border-left: 4px solid var(--color-error); color: var(--color-error-text); padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius-sm);">
                <strong>⚠️ Error:</strong> <?= htmlspecialchars($error_msg) ?>
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

        <form method="POST" class="sira-form" id="plot-form">
            <input type="hidden" name="es_nuevo_cp" id="es_nuevo_cp" value="0">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                
                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Referencia Catastral</label>
                        <input type="text" name="ref_catastral" required maxlength="14" minlength="14" placeholder="14 caracteres (Ej. 1234567AB1234C)" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main); font-family: monospace; letter-spacing: 1px;">
                    </div>
                </div>

                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Dirección de la Parcela</label>
                        <input type="text" name="direccion" required placeholder="Ej. Polígono 4, Parcela 12 — Camino Real" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Código Postal</label>
                    <div style="position: relative;">
                        <input type="text" name="cp" id="cp-input" required maxlength="5" minlength="5" placeholder="Ej. 04001" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                        <div id="cp-loader" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); display: none;">
                            <div class="spinner" style="width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.1); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Municipio</label>
                    <input type="text" name="municipio" id="municipio-input" required readonly placeholder="Autocompletado..." style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main); opacity: 0.7;">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Provincia</label>
                    <input type="text" name="provincia" id="provincia-input" required readonly placeholder="Autocompletado..." style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main); opacity: 0.7;">
                </div>

            </div>

            <div id="geo-status" style="margin-top: 1rem; font-size: 0.85rem; display: none;">
                <!-- Mensaje de estado de geolocalización -->
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2.5rem;">
                <button type="submit" id="save-btn" class="btn-sira btn-primary" style="flex: 2;">
                    Guardar Parcela
                </button>
                <a href="../dashboard.php?cliente_id=<?= $cliente_id_seleccionado ?>" class="btn-sira btn-secondary" style="flex: 1;">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
.spinner { box-sizing: border-box; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cpInput = document.getElementById('cp-input');
    const munInput = document.getElementById('municipio-input');
    const proInput = document.getElementById('provincia-input');
    const cpLoader = document.getElementById('cp-loader');
    const geoStatus = document.getElementById('geo-status');
    const esNuevoCp = document.getElementById('es_nuevo_cp');

    // Usamos el puerto 8000 para la API (comunicación directa desde navegador)
    const AUTH_TOKEN = "<?= $token ?>";

    cpInput.addEventListener('input', function() {
        const val = this.value.trim();
        if (val.length === 5 && !isNaN(val)) {
            checkCP(val);
        } else {
            resetFields();
        }
    });

    async function checkCP(cp) {
        cpLoader.style.display = 'block';
        geoStatus.style.display = 'block';
        geoStatus.innerHTML = '<span style="color: var(--color-text-muted);">🔍 Verificando localización...</span>';
        
        try {
            // Usamos ruta relativa para que pase por el proxy de Nginx (puerto 8085 -> 80)
            const response = await fetch(`/api/v1/geo/check-cp/${cp}`, {
                headers: { 'Authorization': `Bearer ${AUTH_TOKEN}` }
            });

            if (response.ok) {
                const data = await response.json();
                munInput.value = data.municipio;
                proInput.value = data.provincia;
                munInput.readOnly = true;
                proInput.readOnly = true;
                munInput.style.opacity = '1';
                proInput.style.opacity = '1';

                if (data.origen === 'local') {
                    geoStatus.innerHTML = '<span style="color: #10b981;">✅ Localidad encontrada en sistema.</span>';
                    esNuevoCp.value = '0';
                } else {
                    geoStatus.innerHTML = '<span style="color: #eab308;">🌍 Localidad validada vía API externa.</span>';
                    esNuevoCp.value = '1';
                }
            } else {
                throw new Error('CP no encontrado');
            }
        } catch (err) {
            console.error("Error validando CP:", err);
            geoStatus.innerHTML = '<span style="color: var(--color-error);">⚠️ Error de conexión o CP no reconocido. Puedes rellenar los datos manualmente.</span>';
            munInput.value = "";
            proInput.value = "";
            munInput.readOnly = false;
            proInput.readOnly = false;
            munInput.style.opacity = '1';
            proInput.style.opacity = '1';
            esNuevoCp.value = '1';
        } finally {
            cpLoader.style.display = 'none';
        }
    }

    function resetFields() {
        munInput.value = "";
        proInput.value = "";
        munInput.readOnly = true;
        proInput.readOnly = true;
        munInput.style.opacity = '0.7';
        proInput.style.opacity = '0.7';
        geoStatus.style.display = 'none';
        esNuevoCp.value = '0';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
