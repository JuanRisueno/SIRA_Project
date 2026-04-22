<?php
session_start();
if (isset($_SESSION['jwt_token'])) { header("Location: dashboard.php"); exit(); }

require_once 'includes/config.php';

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $api_url = SIRA_API_BASE . '/api/auth/token';
    $post_fields = ['username' => $_POST['username'], 'password' => $_POST['password']];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $resultado = json_decode($response, true);
        $_SESSION['jwt_token'] = $resultado['access_token'];

        // Guardamos el rol e ID en sesión decodificando el payload del JWT
        $token_parts = explode('.', $resultado['access_token']);
        if (count($token_parts) == 3) {
            $payload = json_decode(base64_decode($token_parts[1]), true);
            if (isset($payload['rol'])) {
                $_SESSION['user_rol'] = $payload['rol'];
            }
            if (isset($payload['id'])) {
                $_SESSION['cliente_id'] = $payload['id'];
            }
        }

        header("Location: dashboard.php");
        exit();
    } else {
        $error_msg = "CIF o contraseña incorrectos. Inténtalo de nuevo.";
    }
}

// Variables para el header
$page_title = "SIRA - Acceso al Sistema";
$page_css   = "index";   // <- Carga /css/index.css automáticamente

// El header de login NO muestra el <nav> porque no hay sesión activa
require_once 'includes/header.php';
?>

<div class="login-page-content">
<div class="login-wrapper">

    <!-- Panel izquierdo: Branding -->
    <div class="brand-panel">
        <div>
            <div class="brand-logo">
                <img src="<?= $base_url ?>/assets/img/logo-full.svg" alt="SiRA Logo" style="height: 60px; width: auto; margin-left: -5px;">
            </div>
            <div class="brand-tagline">Sistema Integral de Riego Automático</div>
        </div>

        <ul class="brand-features">
            <li>
                <span class="feature-icon">📊</span>
                <div>
                    <span class="feature-title">Dashboard Jerárquico</span>
                    Gestiona tus parcelas e invernaderos organizados por zona.
                </div>
            </li>
            <li>
                <span class="feature-icon">🌡️</span>
                <div>
                    <span class="feature-title">Monitoreo IoT</span>
                    Sensores de temperatura, humedad y luz en tiempo real.
                </div>
            </li>
            <li>
                <span class="feature-icon">🔒</span>
                <div>
                    <span class="feature-title">Acceso Seguro</span>
                    Autenticación JWT con cifrado de credenciales.
                </div>
            </li>
        </ul>

        <div class="brand-footer">
            © <?= date('Y') ?> Proyecto SIRA — TFG ASIR — Linares, España
        </div>
    </div>

    <!-- Panel derecho: Formulario -->
    <div class="form-panel">
        <h1>Bienvenid@</h1>
        <p class="subtitle">Introduce tu CIF y contraseña para acceder a tu panel de control.</p>

        <?php if ($error_msg): ?>
            <div class="login-error">
                <span>⚠️</span>
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="login-form">
            <div class="form-group">
                <label for="username">CIF / Identificador</label>
                <input type="text" id="username" name="username" placeholder="Ej. B04123456" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-toggle-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="password-toggle-btn" onclick="togglePassword('password', this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>
            <br>
            <button type="submit" class="submit-btn">Acceder al Sistema</button>
        </form>
    </div><!-- .form-panel -->
</div><!-- .login-wrapper -->
</div><!-- .login-page-content -->

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    
    if (isPassword) {
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" y1="2" x2="22" y2="22"></line></svg>';
    } else {
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>