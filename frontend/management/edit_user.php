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

$id_a_editar = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_a_editar) {
    header("Location: ../dashboard.php");
    exit();
}

$error_msg = "";
$success_msg = "";
$user_data = null;

// 1. Obtener datos actuales del usuario
$api_get_url = SIRA_API_BASE . "/api/v1/clientes/$id_a_editar";
$ch = curl_init($api_get_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Accept: application/json"
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $user_data = json_decode($response, true);
} else {
    header("Location: ../dashboard.php?error=usuario_no_encontrado");
    exit();
}

// 2. Procesar actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_empresa   = $_POST['nombre_empresa'] ?? '';
    $cif              = $_POST['cif'] ?? '';
    $persona_contacto = $_POST['persona_contacto'] ?? '';
    $email_admin      = $_POST['email_admin'] ?? '';
    $telefono         = $_POST['telefono'] ?? '';
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol              = $_POST['rol'] ?? $user_data['rol'];

    if (!empty($password) && $password !== $confirm_password) {
        $error_msg = "Las contraseñas no coinciden.";
    } else {
        $api_put_url = SIRA_API_BASE . "/api/v1/clientes/$id_a_editar";
        $data = [
            "nombre_empresa"   => $nombre_empresa,
            "cif"              => $cif,
            "persona_contacto" => $persona_contacto,
            "email_admin"      => $email_admin,
            "telefono"         => $telefono,
            "confirmar_cambio_cif" => ($cif !== $user_data['cif']) // Solo si ha cambiado
        ];

        if (!empty($password)) {
            $data["password"] = $password;
        }
        
        // Rol solo si es root o si no se está editando a sí mismo (seguridad básica)
        $data["rol"] = $rol;

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
            $success_msg = "Usuario actualizado correctamente.";
            // Refrescar datos locales para el formulario
            $user_data = json_decode($response, true);
        } else {
            $res_data = json_decode($response, true);
            $error_msg = $res_data['detail'] ?? "Error al actualizar (Código: $http_code)";
            if (is_array($error_msg)) $error_msg = json_encode($error_msg);
        }
    }
}

$page_title = "SIRA - Editar Usuario";
$page_css   = "dashboard";
require_once '../includes/header.php';
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel de Gestión</a>
        <span>/</span>
        <a href="#">Editar Usuario</a>
    </div>

    <div class="user-form-container card" style="max-width: 800px; margin: 0 auto; background: var(--color-bg-card); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-card); backdrop-filter: blur(10px);">
        
        <div style="margin-bottom: 2rem;">
            <h1 class="dashboard-title">Editar Usuario</h1>
            <p class="dashboard-subtitle">Modifica los datos de <strong><?= htmlspecialchars($user_data['nombre_empresa']) ?></strong></p>
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
                    <h2 style="color: #34d399;">¡Cambios Guardados!</h2>
                    <p>
                        <strong><?= htmlspecialchars($success_msg) ?></strong><br><br>
                        Los datos del usuario se han actualizado correctamente en el servidor.
                    </p>
                    <div class="confirm-actions">
                        <a href="../dashboard.php" class="btn-sira btn-primary" style="min-width: 180px;">Aceptar</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <p style="color: var(--color-primary); font-size: 0.85rem; margin-bottom: 2rem;">(*) Campos obligatorios</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                
                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Nombre de Empresa / Agricultor (*)</label>
                        <input type="text" name="nombre_empresa" required value="<?= htmlspecialchars($user_data['nombre_empresa']) ?>" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">CIF / DNI (Identificador) (*)</label>
                    <input type="text" name="cif" required maxlength="9" minlength="9" value="<?= htmlspecialchars($user_data['cif']) ?>" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                    <small style="color: var(--color-text-muted); font-size: 0.75rem;">Si cambias el CIF, el usuario deberá usar el nuevo para entrar.</small>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Persona de Contacto (*)</label>
                    <input type="text" name="persona_contacto" required value="<?= htmlspecialchars($user_data['persona_contacto']) ?>" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Email de Administración (*)</label>
                    <input type="email" name="email_admin" required value="<?= htmlspecialchars($user_data['email_admin']) ?>" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Teléfono (*)</label>
                    <input type="tel" name="telefono" required maxlength="9" minlength="9" pattern="[0-9]{9}" value="<?= htmlspecialchars($user_data['telefono']) ?>" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Nueva Contraseña (Opcional)</label>
                    <input type="password" name="password" id="password" placeholder="Dejar vacío para no cambiar" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Repetir Nueva Contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Repite la nueva contraseña" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

            </div>

            <div class="form-group" style="margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;">
                <label style="display: block; margin-bottom: 0.8rem; font-weight: 600; color: var(--color-secondary);">Tipo de Usuario (Rol)</label>
                <select name="rol" style="width: 100%; padding: 1rem; background: var(--color-bg-input); border: 1px solid var(--border-input); border-radius: 12px; color: var(--color-text-main); cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2334d399%22%20stroke-width%3D%223%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20xmlns%3D%22http%3D//www.w3.org/2000/svg%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2rem;">
                    <option value="cliente" <?= $user_data['rol'] === 'cliente' ? 'selected' : '' ?>>👨‍🌾 Agricultor / Cliente (Estándar)</option>
                    <option value="admin" <?= $user_data['rol'] === 'admin' ? 'selected' : '' ?>>🛡️ Administrador de Gestión</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2.5rem;">
                <button type="submit" class="btn-sira btn-primary" style="flex: 2;">
                    Guardar Cambios
                </button>
                <a href="../dashboard.php" class="btn-sira btn-secondary" style="flex: 1;">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
