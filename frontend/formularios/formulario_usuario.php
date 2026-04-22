<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: ../index.php"); exit(); }

require_once '../includes/config.php';

$token = $_SESSION['jwt_token'];
$user_rol = $_SESSION['user_rol'] ?? '';
$id_a_gestionar = isset($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit = ($id_a_gestionar !== null);

// 1. Obtener datos si es edición
$user_data = null;
if ($is_edit) {
    $api_get_url = SIRA_API_BASE . "/api/v1/clientes/$id_a_gestionar";
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
}

// 2. Control de Acceso y Permisos
$es_mi_perfil = ($is_edit && $user_rol === 'cliente' && $id_a_gestionar == ($_SESSION['cliente_id'] ?? 0));

if ($is_edit) {
    // Permiso Edición: Admins/Root o usuario editando su propio perfil
    if (!in_array($user_rol, ['admin', 'root']) && !$es_mi_perfil) {
        header("Location: ../dashboard.php");
        exit();
    }
    // Restricción Admin: No puede editar a otros admins/root
    if ($user_rol === 'admin' && in_array($user_data['rol'], ['admin', 'root']) && !$es_mi_perfil) {
        header("Location: ../dashboard.php?error=solo_root_puede_editar_admins");
        exit();
    }
} else {
    // Permiso Creación: Solo Admin/Root
    if (!in_array($user_rol, ['admin', 'root'])) {
        header("Location: ../dashboard.php");
        exit();
    }
}

// 3. Configuración de UI
$solo_lectura = ($is_edit && $user_rol === 'cliente');
$titulo_pagina = $is_edit ? ($es_mi_perfil ? "Mi Cuenta" : "Editar Usuario") : "Añadir Nuevo Usuario";
$subtitulo_pagina = $is_edit 
    ? ($es_mi_perfil ? "Gestiona tus datos personales y de contacto." : "Modifica los datos de <strong>" . htmlspecialchars($user_data['nombre_empresa'] ?? '') . "</strong>")
    : "Completa los datos para registrar un nuevo integrante o cliente en el sistema.";

$error_msg = "";
$success_msg = "";

$cliente_id_seleccionado = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;
// [V14.1] Lógica de Retorno Dinámica (Backflow)
$from = $_GET['from'] ?? '';
if (!empty($from)) {
    $url_retorno = "../dashboard.php?seccion=" . urlencode($from) . ($cliente_id_seleccionado ? "&cliente_id=$cliente_id_seleccionado" : "");
} else {
    $url_retorno = "../dashboard.php" . ($cliente_id_seleccionado ? "?cliente_id=$cliente_id_seleccionado" : "");
}



// 4. Procesar Formulario (POST/PUT)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_empresa   = $_POST['nombre_empresa'] ?? '';
    $cif              = $_POST['cif'] ?? '';
    $persona_contacto = $_POST['persona_contacto'] ?? '';
    $email_admin      = $_POST['email_admin'] ?? '';
    $telefono         = $_POST['telefono'] ?? '';
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol              = $_POST['rol'] ?? ($is_edit ? $user_data['rol'] : 'cliente');

    // Validaciones específicas de Edición de Cliente
    if ($solo_lectura) {
        $confirm_email = $_POST['confirm_email_admin'] ?? '';
        $confirm_tlf   = $_POST['confirm_telefono'] ?? '';
        
        if ($email_admin !== $user_data['email_admin'] && $email_admin !== $confirm_email) {
            $error_msg = "El nuevo Email y su confirmación no coinciden.";
        }
        if (!$error_msg && $telefono !== $user_data['telefono'] && $telefono !== $confirm_tlf) {
            $error_msg = "El nuevo Teléfono y su confirmación no coinciden.";
        }
    }

    // Validación de Contraseña
    if (!$error_msg) {
        if (!$is_edit && empty($password)) {
            $error_msg = "La contraseña es obligatoria para nuevos usuarios.";
        } elseif (!empty($password) && $password !== $confirm_password) {
            $error_msg = "Las contraseñas no coinciden.";
        }
    }

    if (!$error_msg) {
        $api_url = $is_edit ? SIRA_API_BASE . "/api/v1/clientes/$id_a_gestionar" : SIRA_API_BASE . "/api/v1/clientes/";
        $method  = $is_edit ? "PUT" : "POST";
        
        $data = [
            "nombre_empresa"   => $nombre_empresa,
            "persona_contacto" => $persona_contacto,
            "email_admin"      => $email_admin,
            "telefono"         => $telefono
        ];

        if (!$solo_lectura) {
            $data["cif"] = $cif;
            $data["rol"] = $rol;
            if ($is_edit) $data["confirmar_cambio_cif"] = ($cif !== $user_data['cif']);
        }

        if (!empty($password)) {
            $data["password"] = $password;
        }

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (($is_edit && $http_code == 200) || (!$is_edit && $http_code == 201)) {
            $success_msg = $is_edit ? "Usuario actualizado correctamente." : "Usuario creado correctamente.";
            $auto_redirect = $url_retorno;
            if ($is_edit) $user_data = json_decode($response, true);
        } else {
            $res_data = json_decode($response, true);
            $detail = $res_data['detail'] ?? "Error en la operación (Código: $http_code)";
            $error_msg = is_array($detail) ? json_encode($detail) : $detail;
        }
    }
}


$page_title = "SIRA - " . ($is_edit ? $titulo_pagina : "Añadir Usuario");
$page_css   = "dashboard";
require_once '../includes/header.php';

// Etiquetas dinámicas para Personal de Gestión vs Agricultores
$es_admin_target = ($is_edit && in_array($user_data['rol'], ['admin', 'root']));
$label_nombre   = $es_admin_target ? "Nombre Completo (Personal de Gestión)" : "Nombre de Empresa / Agricultor (*)";
$label_id       = $es_admin_target ? "DNI / Identificador Personal (*)" : "CIF / DNI (Identificador) (*)";
$label_contacto = $es_admin_target ? "Departamento / Cargo" : "Persona de Contacto (*)";
?>

<div class="container">
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="../dashboard.php">Panel</a>
        <span>/</span>
        <?php if ($is_edit): ?>
            <a href="#"><?= ($es_admin_target) ? "Gestión de Personal" : "Entorno de Explotación" ?></a>
            <span>/</span>
        <?php endif; ?>
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
                    <h2 style="color: #34d399;"><?= $is_edit ? "¡Cambios Guardados!" : "¡Registro Completado!" ?></h2>
                    <p style="margin-bottom: 0.5rem;">
                        <strong><?= htmlspecialchars($success_msg) ?></strong><br><br>
                        <?= $is_edit ? "Los datos se han actualizado correctamente." : "El usuario ha sido dado de alta correctamente en SIRA." ?>
                    </p>
                    <div class="sira-countdown-text">
                        Volviendo al panel en 
                        <div class="sira-countdown-number">
                            <span class="n-3">3</span>
                            <span class="n-2">2</span>
                            <span class="n-1">1</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <?php if ($solo_lectura): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--color-primary); color: var(--color-text-main); padding: 1.2rem; margin-bottom: 2rem; border-radius: 10px; font-size: 0.9rem; line-height: 1.5;">
                    💡 <strong>Nota de Seguridad:</strong> Como personal de gestión de SIRA, puedes actualizar tus datos de contacto y clave. Tu identificador y rol son gestionados por el Administrador de Sistemas.
                </div>
            <?php endif; ?>
            
            <p style="color: var(--color-primary); font-size: 0.85rem; margin-bottom: 2rem;">(*) Campos obligatorios</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                
                <div style="grid-column: span 2;">
                    <div class="input-group-premium">
                        <label><?= $label_nombre ?></label>
                        <input type="text" name="nombre_empresa" required placeholder="Ej. Agrícola del Campo S.L." value="<?= $is_edit ? htmlspecialchars($user_data['nombre_empresa']) : '' ?>">
                    </div>
                </div>

                <div class="input-group-premium">
                    <label><?= $label_id ?></label>
                    <input type="text" name="cif" required maxlength="9" minlength="9" placeholder="Ej. B04123456" value="<?= $is_edit ? htmlspecialchars($user_data['cif']) : '' ?>" <?= $solo_lectura ? 'readonly style="opacity: 0.6; cursor: not-allowed;"' : '' ?>>
                </div>

                <div class="input-group-premium">
                    <label><?= $label_contacto ?></label>
                    <input type="text" name="persona_contacto" required placeholder="Nombre completo" value="<?= $is_edit ? htmlspecialchars($user_data['persona_contacto']) : '' ?>">
                </div>

                <div class="input-group-premium">
                    <label>Email de Administración (*)</label>
                    <input type="email" name="email_admin" required placeholder="admin@empresa.com" value="<?= $is_edit ? htmlspecialchars($user_data['email_admin']) : '' ?>">
                </div>

                <?php if ($solo_lectura): ?>
                <div class="input-group-premium">
                    <label>Repetir Email (*)</label>
                    <input type="email" name="confirm_email_admin" placeholder="Confirma si has cambiado el email">
                </div>
                <?php endif; ?>

                <div class="input-group-premium">
                    <label>Teléfono (*)</label>
                    <input type="tel" name="telefono" required maxlength="9" minlength="9" pattern="[0-9]{9}" placeholder="Ej. 600000000" value="<?= $is_edit ? htmlspecialchars($user_data['telefono']) : '' ?>">
                </div>

                <?php if ($solo_lectura): ?>
                <div class="input-group-premium">
                    <label>Repetir Teléfono (*)</label>
                    <input type="tel" name="confirm_telefono" maxlength="9" minlength="9" pattern="[0-9]{9}" placeholder="Confirma si has cambiado el teléfono">
                </div>
                <?php endif; ?>

                <div class="input-group-premium">
                    <label><?= $is_edit ? "Nueva Contraseña (Opcional)" : "Contraseña (*)" ?></label>
                    <input type="password" name="password" id="password" <?= !$is_edit ? 'required' : '' ?> placeholder="<?= !$is_edit ? 'Mín. 6 caracteres' : 'Dejar vacío para no cambiar' ?>">
                </div>

                <div class="input-group-premium">
                    <label>Repetir Contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" <?= !$is_edit ? 'required' : '' ?> placeholder="Repite la contraseña">
                </div>

            </div>

            <?php if (!$solo_lectura): ?>
            <div class="form-group" style="margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;">
                <label style="display: block; margin-bottom: 0.8rem; font-weight: 600; color: var(--color-secondary);">Tipo de Usuario (Rol)</label>
                <select name="rol" style="width: 100%; padding: 1rem; background: var(--color-bg-input); border: 1px solid var(--border-input); border-radius: 10px; color: var(--color-text-main); cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2334d399%22%20stroke-width%3D%223%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20xmlns%3D%22http%3D//www.w3.org/2000/svg%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2rem;">
                    <option value="cliente" <?= ($is_edit && $user_data['rol'] === 'cliente') ? 'selected' : '' ?>>👨‍🌾 Agricultor / Cliente</option>
                    <option value="admin" <?= ($is_edit && $user_data['rol'] === 'admin') ? 'selected' : '' ?>>🛡️ Administrador de Gestión</option>
                </select>
            </div>
            <?php elseif ($is_edit): ?>
                <input type="hidden" name="rol" value="<?= htmlspecialchars($user_data['rol']) ?>">
            <?php endif; ?>

            <div class="form-footer-actions">
                <button type="submit" class="btn-sira btn-primary">
                    <?= $is_edit ? "Guardar Cambios" : "Registrar Nuevo Usuario" ?>
                </button>
                <a href="<?= $url_retorno ?>" class="btn-sira btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
