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

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extracción de datos (Solución a variables indefinidas)
    $nombre_empresa   = $_POST['nombre_empresa'] ?? '';
    $cif              = $_POST['cif'] ?? '';
    $email_admin      = $_POST['email_admin'] ?? '';
    $telefono         = $_POST['telefono'] ?? '';
    $persona_contacto = $_POST['persona_contacto'] ?? '';
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol              = $_POST['rol'] ?? 'cliente';

    // Validación de coincidencia de contraseñas (PHP)
    if ($password !== $confirm_password) {
        $error_msg = "Las contraseñas no coinciden. Por favor, verifica los campos.";
    } else {
        // Llamada a la API
        $api_url = SIRA_API_BASE . "/api/v1/clientes/";
        $data = [
            "nombre_empresa" => $nombre_empresa,
            "cif" => $cif,
            "email_admin" => $email_admin,
            "telefono" => $telefono,
            "persona_contacto" => $persona_contacto,
            "password" => $password,
            "rol" => $rol
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
            $success_msg = "Usuario creado correctamente.";
        } else {
            $res_data = json_decode($response, true);
            $detail = $res_data['detail'] ?? "Error desconocido (Código: $http_code)";
            
            // Si el 'detail' es una lista de errores (FastAPI validation), lo aplanamos
            if (is_array($detail)) {
                $error_msg = json_encode($detail);
            } else {
                $error_msg = $detail;
            }
        }
    }
}

$page_title = "SIRA - Añadir Usuario";
$page_css   = "dashboard"; // Reutilizamos estilos de panel
require_once '../includes/header.php';
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel de Gestión</a>
        <span>/</span>
        <a href="add_user.php">Añadir Usuario</a>
    </div>

    <div class="user-form-container card" style="max-width: 800px; margin: 0 auto; background: var(--color-bg-card); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-card); backdrop-filter: blur(10px);">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">Añadir Nuevo Usuario</h1>
            <p class="dashboard-subtitle">Completa los datos para registrar un nuevo integrante o cliente en el sistema.</p>
        </div>

        <?php if ($error_msg): ?>
            <div style="background: var(--color-error-bg); border-left: 4px solid var(--color-error); color: var(--color-error-text); padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius-sm);">
                <strong>⚠️ Error:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <!-- PANTALLA DE ÉXITO (Zero JS) -->
            <div class="confirm-overlay">
                <div class="confirm-card" style="border-color: #10b981;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">✅</div>
                    <h2 style="color: #34d399;">¡Registro Completado!</h2>
                    <p>
                        <strong><?= htmlspecialchars($success_msg) ?></strong><br><br>
                        El usuario ha sido dado de alta correctamente en la plataforma SIRA.
                    </p>
                    <div class="confirm-actions">
                        <a href="../dashboard.php" class="btn-sira btn-primary" style="min-width: 180px;">Volver al Panel</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <p style="color: var(--color-primary); font-size: 0.85rem; margin-bottom: 2rem;">(*) Campos obligatorios</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                
                <!-- Fila 1: Nombre Empresa -->
                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Nombre de Empresa / Agricultor (*)</label>
                        <input type="text" name="nombre_empresa" required placeholder="Ej. Agrícola del Campo S.L." style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                    </div>
                </div>

                <!-- Fila 2: Identificación y Contacto -->
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">CIF / DNI (Identificador) (*)</label>
                    <input type="text" name="cif" required maxlength="9" minlength="9" placeholder="9 caracteres (Ej. B04123456)" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Persona de Contacto (*)</label>
                    <input type="text" name="persona_contacto" required placeholder="Nombre completo" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <!-- Fila 3: Comunicación -->
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Email de Administración (*)</label>
                    <input type="email" name="email_admin" required placeholder="admin@empresa.com" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Teléfono (*)</label>
                    <input type="tel" name="telefono" required maxlength="9" minlength="9" pattern="[0-9]{9}" title="Debe contener exactamente 9 números" placeholder="Ej. 600000000" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <!-- Fila 4: Seguridad -->
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Contraseña (*)</label>
                    <input type="password" name="password" id="password" required placeholder="Mín. 6 caracteres" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Repetir Contraseña (*)</label>
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Repite la contraseña" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

            </div>

            <div class="form-group" style="margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;">
                <label style="display: block; margin-bottom: 0.8rem; font-weight: 600; color: var(--color-secondary);">Tipo de Usuario (Rol)</label>
                <select name="rol" style="width: 100%; padding: 1rem; background: var(--color-bg-input); border: 1px solid var(--border-input); border-radius: 12px; color: var(--color-text-main); cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2334d399%22%20stroke-width%3D%223%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20xmlns%3D%22http%3D//www.w3.org/2000/svg%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2rem;">
                    <option value="cliente">👨‍🌾 Agricultor / Cliente (Acceso a sus propios activos)</option>
                    <option value="admin">🛡️ Administrador de Gestión (Supervisión global)</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2.5rem;">
                <button type="submit" class="btn-sira btn-primary" style="flex: 2;">
                    Registrar Nuevo Usuario
                </button>
                <a href="../dashboard.php" class="btn-sira btn-secondary" style="flex: 1;">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
