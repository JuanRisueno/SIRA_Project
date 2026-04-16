<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: ../index.php"); exit(); }

require_once '../includes/config.php';

$token = $_SESSION['jwt_token'];
$user_rol = $_SESSION['user_rol'] ?? '';

$id_a_editar = isset($_GET['id']) ? (int)$_GET['id'] : null;

// 1. Obtener datos actuales del usuario (NECESARIO ANTES DE LAS COMPROBACIONES DE UI)
$user_data = null;
if ($id_a_editar) {
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
    }
}

$es_mi_perfil = ($user_rol === 'cliente' && $id_a_editar == ($_SESSION['cliente_id'] ?? 0));

// Permiso: Admnins/Root entran siempre. Clientes solo a su propio profile.
if (!in_array($user_rol, ['admin', 'root']) && !$es_mi_perfil) {
    header("Location: ../dashboard.php");
    exit();
}

// Si no hay ID o no se encontró el usuario, fuera
if (!$id_a_editar || !$user_data) {
    header("Location: ../dashboard.php?error=usuario_no_encontrado");
    exit();
}

// NUEVA RESTRICCIÓN: Un admin no puede editar a NADIE que sea admin o root (ni siquiera a sí mismo)
if ($user_rol === 'admin' && in_array($user_data['rol'], ['admin', 'root'])) {
    header("Location: ../dashboard.php?error=solo_root_puede_editar_admins");
    exit();
}

// Lógica de visualización
$solo_lectura = ($user_rol === 'cliente');
$titulo_pagina = $es_mi_perfil ? "Mi Cuenta" : "Editar Usuario";
$subtitulo_pagina = $es_mi_perfil ? "Gestiona tus datos personales y de contacto." : "Modifica los datos de <strong>" . htmlspecialchars($user_data['nombre_empresa'] ?? '') . "</strong>";

$error_msg = "";
$success_msg = "";

// 2. Procesar actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_empresa   = $_POST['nombre_empresa'] ?? '';
    $cif              = $_POST['cif'] ?? '';
    $persona_contacto = $_POST['persona_contacto'] ?? '';
    $email_admin      = $_POST['email_admin'] ?? '';
    $telefono         = $_POST['telefono'] ?? '';
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $confirm_email_admin = $_POST['confirm_email_admin'] ?? '';
    $confirm_telefono    = $_POST['confirm_telefono'] ?? '';
    $rol                 = $_POST['rol'] ?? $user_data['rol'];

    // Validaciones de confirmación condicionales (Solo si cambian respecto a la base de datos)
    if ($solo_lectura) {
        $cambio_email = ($email_admin !== $user_data['email_admin']);
        $cambio_tlf   = ($telefono !== $user_data['telefono']);

        if ($cambio_email) {
            if (empty($confirm_email_admin)) {
                $error_msg = "Has cambiado tu email. Por favor, confírmalo en el campo 'Repetir Email'.";
            } elseif ($email_admin !== $confirm_email_admin) {
                $error_msg = "El nuevo Email y su confirmación no coinciden.";
            }
        }

        if (!$error_msg && $cambio_tlf) {
            if (empty($confirm_telefono)) {
                $error_msg = "Has cambiado tu teléfono. Por favor, confírmalo en el campo 'Repetir Teléfono'.";
            } elseif ($telefono !== $confirm_telefono) {
                $error_msg = "El nuevo Teléfono y su confirmación no coinciden.";
            }
        }
    }

    if (!$error_msg && !empty($password) && $password !== $confirm_password) {
        $error_msg = "Las contraseñas no coinciden.";
    }

    if (!$error_msg) {
        $api_put_url = SIRA_API_BASE . "/api/v1/clientes/$id_a_editar";
        $data = [
            "nombre_empresa"   => $nombre_empresa,
            "persona_contacto" => $persona_contacto,
            "email_admin"      => $email_admin,
            "telefono"         => $telefono
        ];

        // Solo enviamos CIF y Rol si el usuario tiene permiso (Admin/Root)
        if (!$solo_lectura) {
            $data["cif"] = $cif;
            $data["confirmar_cambio_cif"] = ($cif !== $user_data['cif']);
            $data["rol"] = $rol;
        }

        if (!empty($password)) {
            $data["password"] = $password;
        }

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

$page_title = "SIRA - " . $titulo_pagina;
$page_css   = "dashboard";
require_once '../includes/header.php';
?>

<?php
$es_admin_target = in_array($user_data['rol'], ['admin', 'root']);
$label_nombre = $es_admin_target ? "Nombre Completo (Personal de Gestión)" : "Nombre de Empresa / Agricultor (*)";
$label_id     = $es_admin_target ? "DNI / Identificador Personal (*)" : "CIF / DNI (Identificador) (*)";
$label_contacto = $es_admin_target ? "Departamento / Cargo" : "Persona de Contacto (*)";
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <a href="#"><?= $es_admin_target ? "Gestión de Personal" : "Entorno de Explotación" ?></a>
        <span>/</span>
        <a href="#"><?= $titulo_pagina ?></a>
    </div>

    <div class="user-form-container">
        
        <div style="margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem;">
                <h1 class="dashboard-title" style="margin:0;"><?= $titulo_pagina ?></h1>
                <?php if ($es_admin_target): ?>
                    <span style="background: var(--color-secondary-glow); color: var(--color-secondary); padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; border: 1px solid var(--color-secondary-border);">
                        🛡️ PERSONAL SIRA
                    </span>
                <?php endif; ?>
            </div>
            <p class="dashboard-subtitle"><?= $subtitulo_pagina ?></p>
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
            <?php if ($solo_lectura): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--color-primary); color: var(--color-text-main); padding: 1.2rem; margin-bottom: 2rem; border-radius: 10px; font-size: 0.9rem; line-height: 1.5;">
                    💡 <strong>Nota de Seguridad:</strong> Como personal de gestión de SIRA, puedes actualizar tus datos de contacto y clave. Por integridad de la infraestructura, tu identificador y rol son gestionados por el Administrador de Sistemas.
                    <br><br>
                    Para cambios en tu estatus administrativo, contacta con <a href="mailto:sira@sira.es" style="color: var(--color-primary); font-weight: 600;">sira@sira.es</a>.
                </div>
            <?php endif; ?>
            
            <p style="color: var(--color-primary); font-size: 0.85rem; margin-bottom: 2rem;">(*) Campos obligatorios</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                
                <div style="grid-column: span 2;">
                    <div class="input-group-premium">
                        <label><?= $label_nombre ?></label>
                        <input type="text" name="nombre_empresa" required value="<?= htmlspecialchars($user_data['nombre_empresa']) ?>">
                    </div>
                </div>

                <div class="input-group-premium">
                    <label><?= $label_id ?></label>
                    <input type="text" name="cif" required maxlength="9" minlength="9" value="<?= htmlspecialchars($user_data['cif']) ?>" <?= $solo_lectura ? 'readonly style="opacity: 0.6; cursor: not-allowed;"' : '' ?>>
                </div>

                <div class="input-group-premium">
                    <label><?= $label_contacto ?></label>
                    <input type="text" name="persona_contacto" required value="<?= htmlspecialchars($user_data['persona_contacto']) ?>">
                </div>

                <!-- Email + Confirmación -->
                <div class="input-group-premium">
                    <label>Email de Administración (*)</label>
                    <input type="email" name="email_admin" required value="<?= htmlspecialchars($user_data['email_admin']) ?>">
                </div>

                <?php if ($solo_lectura): ?>
                <div class="input-group-premium">
                    <label>Repetir Email (*)</label>
                    <input type="email" name="confirm_email_admin" placeholder="Confirma si has cambiado el email">
                </div>
                <?php endif; ?>

                <!-- Teléfono + Confirmación -->
                <div class="input-group-premium">
                    <label>Teléfono (*)</label>
                    <input type="tel" name="telefono" required maxlength="9" minlength="9" pattern="[0-9]{9}" value="<?= htmlspecialchars($user_data['telefono']) ?>">
                </div>

                <?php if ($solo_lectura): ?>
                <div class="input-group-premium">
                    <label>Repetir Teléfono (*)</label>
                    <input type="tel" name="confirm_telefono" maxlength="9" minlength="9" pattern="[0-9]{9}" placeholder="Confirma si has cambiado el teléfono">
                </div>
                <?php endif; ?>

                <div class="input-group-premium">
                    <label>Nueva Contraseña (Opcional)</label>
                    <input type="password" name="password" id="password" placeholder="Dejar vacío para no cambiar">
                </div>

                <div class="input-group-premium">
                    <label>Repetir Nueva Contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Repite la nueva contraseña">
                </div>

            </div>

            <?php if (!$solo_lectura): ?>
            <div class="form-group" style="margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;">
                <label style="display: block; margin-bottom: 0.8rem; font-weight: 600; color: var(--color-secondary);">Tipo de Usuario (Rol)</label>
                <select name="rol" style="width: 100%; padding: 1rem; background: var(--color-bg-input); border: 1px solid var(--border-input); border-radius: 10px; color: var(--color-text-main); cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2334d399%22%20stroke-width%3D%223%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20xmlns%3D%22http%3D//www.w3.org/2000/svg%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2rem;">
                    <option value="cliente" <?= $user_data['rol'] === 'cliente' ? 'selected' : '' ?>>👨‍🌾 Agricultor / Cliente (Estándar)</option>
                    <option value="admin" <?= $user_data['rol'] === 'admin' ? 'selected' : '' ?>>🛡️ Administrador de Gestión</option>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="rol" value="<?= htmlspecialchars($user_data['rol']) ?>">
            <?php endif; ?>

            <div class="form-footer-actions">
                <button type="submit" class="btn-sira btn-primary">
                    Guardar Cambios
                </button>
                <a href="../dashboard.php" class="btn-sira btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
