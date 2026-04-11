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
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol = $_POST['rol'] ?? 'cliente';

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
            $error_msg = $res_data['detail'] ?? "Error al crear el usuario (Código: $http_code)";
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
            <!-- PANTALLA DE ÉXITO CON REDIRECCIÓN -->
            <div class="confirm-overlay">
                <div class="confirm-card" style="border-color: #10b981;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">✅</div>
                    <h2 style="color: #34d399;">¡Registro Completado!</h2>
                    <p>
                        <strong><?= htmlspecialchars($success_msg) ?></strong><br><br>
                        Serás redirigido al panel principal en <span id="countdown">5</span> segundos...
                    </p>
                    <div class="confirm-actions">
                        <a href="../dashboard.php" class="btn-sira btn-primary" style="min-width: 180px;">Aceptar</a>
                    </div>
                </div>
            </div>

            <script>
            let seconds = 5;
            const countdownEl = document.getElementById('countdown');
            const timer = setInterval(() => {
                seconds--;
                if (countdownEl) countdownEl.innerText = seconds;
                if (seconds <= 0) {
                    clearInterval(timer);
                    window.location.href = '../dashboard.php';
                }
            }, 1000);
            </script>
        <?php endif; ?>

        <form method="POST" class="sira-form">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                
                <!-- Fila 1: Nombre Empresa (Ancho completo para equilibrio visual) -->
                <div style="grid-column: span 2;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Nombre de Empresa / Agricultor</label>
                        <input type="text" name="nombre_empresa" required placeholder="Ej. Agrícola del Campo S.L." style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                    </div>
                </div>

                <!-- Fila 2: Identificación y Contacto -->
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">CIF / DNI (Identificador)</label>
                    <input type="text" name="cif" required maxlength="9" minlength="9" placeholder="9 caracteres (Ej. B04123456)" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Persona de Contacto</label>
                    <input type="text" name="persona_contacto" required placeholder="Nombre completo" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <!-- Fila 3: Comunicación -->
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Email de Administración</label>
                    <input type="email" name="email_admin" required placeholder="admin@empresa.com" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Teléfono</label>
                    <input type="tel" name="telefono" required maxlength="9" minlength="9" pattern="[0-9]{9}" title="Debe contener exactamente 9 números" placeholder="Ej. 600000000" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <!-- Fila 4: Seguridad -->
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Contraseña</label>
                    <input type="password" name="password" id="password" required placeholder="Mín. 6 caracteres" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-primary);">Repetir Contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Repite la contraseña" style="width: 100%; padding: 0.8rem; border-radius: 10px; background: var(--color-bg-input); border: 1px solid var(--border-input); color: var(--color-text-main);">
                    <small id="password-error" style="color: var(--color-error); display: none; margin-top: 0.5rem; font-weight: 600; position: absolute;">⚠️ Las contraseñas no coinciden</small>
                </div>

            </div>

            <div class="form-group" style="margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;">
                <label style="display: block; margin-bottom: 0.8rem; font-weight: 600; color: var(--color-secondary);">Tipo de Usuario (Rol)</label>
                
                <!-- CUSTOM SELECT (SIRA PREMIUM) -->
                <div class="custom-select-wrapper" id="rol-custom-select">
                    <input type="hidden" name="rol" id="rol-hidden-input" value="cliente">
                    <div class="custom-select-trigger">
                        <span id="rol-selected-text">Agricultor / Cliente (Estándar)</span>
                        <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="custom-options">
                        <div class="custom-option selected" data-value="cliente">
                            <span class="option-icon">👨‍🌾</span>
                            <div class="option-content">
                                <strong>Agricultor / Cliente (Estándar)</strong>
                                <small>Acceso a su propia infraestructura y sensores.</small>
                            </div>
                        </div>
                        <div class="custom-option" data-value="admin">
                            <span class="option-icon">🛡️</span>
                            <div class="option-content">
                                <strong>Administrador de Gestión</strong>
                                <small>Supervisión de clientes y control administrativo.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    /* Estilos para el selector premium */
                    .custom-select-wrapper {
                        position: relative;
                        user-select: none;
                    }

                    .custom-select-trigger {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 1rem;
                        background: var(--color-bg-input);
                        border: 1px solid var(--border-input);
                        border-radius: 12px;
                        color: var(--color-text-main);
                        cursor: pointer;
                        transition: all 0.3s ease;
                    }

                    .custom-select-trigger:hover {
                        border-color: var(--color-primary-border);
                        background: rgba(255, 255, 255, 0.05);
                    }

                    html[data-theme="light"] .custom-select-trigger:hover {
                        background: rgba(0, 0, 0, 0.02);
                    }

                    .custom-select-wrapper.open .custom-select-trigger {
                        border-color: var(--color-primary);
                        border-bottom-left-radius: 0;
                        border-bottom-right-radius: 0;
                        background: var(--color-bg-card);
                    }

                    .custom-select-trigger .chevron {
                        transition: transform 0.3s ease;
                        color: var(--color-primary);
                    }

                    .custom-select-wrapper.open .chevron {
                        transform: rotate(180deg);
                    }

                    .custom-options {
                        position: absolute;
                        top: 100%;
                        left: 0;
                        right: 0;
                        background: var(--color-bg-card);
                        border: 1px solid var(--color-primary);
                        border-top: none;
                        border-bottom-left-radius: 12px;
                        border-bottom-right-radius: 12px;
                        overflow: hidden;
                        display: none;
                        z-index: 100;
                        box-shadow: var(--shadow-card-hover);
                        backdrop-filter: blur(15px);
                    }

                    .custom-select-wrapper.open .custom-options {
                        display: block;
                        animation: slideDown 0.2s ease-out;
                    }

                    @keyframes slideDown {
                        from { opacity: 0; transform: translateY(-10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }

                    .custom-option {
                        padding: 12px 16px;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        cursor: pointer;
                        transition: background 0.2s;
                        border-bottom: 1px solid var(--border-color);
                    }

                    .custom-option:hover {
                        background: var(--color-primary-glow);
                    }

                    .custom-option.selected {
                        background: var(--color-primary-glow);
                        border-left: 4px solid var(--color-primary);
                    }

                    .option-icon {
                        font-size: 1.2rem;
                    }

                    .option-content strong {
                        display: block;
                        font-size: 0.95rem;
                        color: var(--color-text-main);
                    }

                    .option-content small {
                        display: block;
                        font-size: 0.75rem;
                        color: #94a3b8;
                    }
                </style>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const wrapper = document.getElementById('rol-custom-select');
                        const trigger = wrapper.querySelector('.custom-select-trigger');
                        const options = wrapper.querySelectorAll('.custom-option');
                        const hiddenInput = document.getElementById('rol-hidden-input');
                        const selectedText = document.getElementById('rol-selected-text');

                        trigger.addEventListener('click', function() {
                            wrapper.classList.toggle('open');
                        });

                        options.forEach(option => {
                            option.addEventListener('click', function() {
                                const value = this.getAttribute('data-value');
                                const text = this.querySelector('strong').innerText;

                                hiddenInput.value = value;
                                selectedText.innerText = text;

                                options.forEach(opt => opt.classList.remove('selected'));
                                this.classList.add('selected');

                                wrapper.classList.remove('open');
                            });
                        });

                        // Cerrar al hacer clic fuera
                        document.addEventListener('click', function(e) {
                            if (!wrapper.contains(e.target)) {
                                wrapper.classList.remove('open');
                            }
                        });

                        // Validación de contraseñas en tiempo real
                        const pwd = document.getElementById('password');
                        const pwdConfirm = document.getElementById('confirm_password');
                        const errorMsg = document.getElementById('password-error');
                        const submitBtn = document.querySelector('button[type="submit"]');

                        function validatePasswords() {
                            if (pwdConfirm.value && pwd.value !== pwdConfirm.value) {
                                errorMsg.style.display = 'block';
                                pwdConfirm.style.borderColor = 'var(--color-error)';
                            } else {
                                errorMsg.style.display = 'none';
                                pwdConfirm.style.borderColor = 'var(--border-input)';
                            }
                        }

                        pwd.addEventListener('input', validatePasswords);
                        pwdConfirm.addEventListener('input', validatePasswords);

                        // Evitar envío si no coinciden
                        document.querySelector('.sira-form').addEventListener('submit', function(e) {
                            if (pwd.value !== pwdConfirm.value) {
                                e.preventDefault();
                                alert("Las contraseñas no coinciden. Por favor, corrígelas.");
                            }
                        });
                    });
                </script>
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
