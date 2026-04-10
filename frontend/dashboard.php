<?php
session_start();

// 1. Verificación de seguridad: si no hay token, fuera
if (!isset($_SESSION['jwt_token'])) {
    header("Location: index.php");
    exit();
}

$token = $_SESSION['jwt_token'];

// 2. Función para pedir los datos a la API de Juan
function obtenerInvernaderos($token) {
    // Usamos la ruta que vimos en tu Swagger
    $url = "http://api:8000/api/v1/invernaderos/"; 
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return [];
}

$invernaderos = obtenerInvernaderos($token);

?>
<!DOCTYPE html>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SIRA - Panel de Control</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f4f0; margin: 0; padding: 0; }
        nav { background: #2e7d32; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .container { padding: 2rem; max-width: 1000px; margin: auto; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 4px solid #4caf50; }
        .card h3 { margin-top: 0; color: #2e7d32; }
        .status { display: inline-block; padding: 4px 8px; background: #e8f5e9; color: #2e7d32; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        .logout-btn { color: white; text-decoration: none; border: 1px solid white; padding: 5px 15px; border-radius: 5px; transition: 0.3s; }
        .logout-btn:hover { background: white; color: #2e7d32; }
        .no-data { text-align: center; background: white; padding: 2rem; border-radius: 12px; color: #666; }
    </style>
</head>
<body>

<nav>
    <h2>SIRA 🌱 <span style="font-weight: normal; font-size: 0.8em;">| Gestión de Cultivos</span></h2>
    <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
</nav>

<div class="container">
    <h1>Bienvenido, Antonio</h1>
    <p>Estos son tus invernaderos registrados en el sistema Almeriense:</p>

    <div class="grid">
        <?php if (!empty($invernaderos)): ?>
            <?php foreach ($invernaderos as $inv): ?>
                <div class="card">
                    <span class="status">ACTIVO</span>
                    <h3><?php echo htmlspecialchars($inv['nombre'] ?? 'Invernadero sin nombre'); ?></h3>
                    
                    <p>📍 <b>Ubicación:</b> <?php echo htmlspecialchars($inv['parcela']['direccion'] ?? 'Ubicación no especificada'); ?></p>
                    
                    <p>📏 <b>Dimensiones:</b> 
                        <?php echo htmlspecialchars($inv['largo_m'] ?? '0') . "m x " . htmlspecialchars($inv['ancho_m'] ?? '0') . "m"; ?>
                    </p>
                    
                    <hr style="border: 0; border-top: 1px solid #eee;">
                    <a href="sensores.php?id=<?php echo $inv['invernadero_id']; ?>&nombre=<?php echo urlencode($inv['nombre']); ?>" style="display: block; text-align: center; width: 100%; padding: 10px; background: #4caf50; color: white; text-decoration: none; border-radius: 5px; box-sizing: border-box; font-weight: bold;">Ver Sensores</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">
                <h3>No hay datos disponibles</h3>
                <p>Parece que todavía no hay invernaderos asociados a tu cuenta o la API no ha devuelto registros.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>