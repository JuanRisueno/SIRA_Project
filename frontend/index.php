<?php
session_start();
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    
    // LA RUTA CORRECTA SEGÚN TU SWAGGER
    $api_url = 'http://api:8000/auth/token';
    
    $post_fields = [
        'username' => $_POST['username'],
        'password' => $_POST['password']
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields)); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $resultado = json_decode($response, true);
        $_SESSION['jwt_token'] = $resultado['access_token'];
        header("Location: dashboard.php");
        exit();
    } else {
        // Si falla, te diré el código para que sepamos por qué
        $error_msg = "Acceso denegado (Código: $http_code). Revisa CIF y Contraseña.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SIRA - Acceso</title>
    <style>
        body { font-family: sans-serif; background: #e8f5e9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); width: 320px; text-align: center; border-top: 5px solid #2e7d32; }
        input { width: 100%; padding: 12px; margin: 10px 0; box-sizing: border-box; border-radius: 6px; border: 1px solid #ccc; }
        button { width: 100%; padding: 12px; background: #2e7d32; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .error { color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 6px; font-size: 0.8em; margin-bottom: 10px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>SIRA 🌱</h1>
        <?php if($error_msg) echo "<div class='error'>$error_msg</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Usuario / CIF" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>