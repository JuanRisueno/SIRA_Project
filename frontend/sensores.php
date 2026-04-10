<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: index.php"); exit(); }

$id_inv = $_GET['id'] ?? 1;
$nombre_inv = $_GET['nombre'] ?? 'Invernadero';
$token = $_SESSION['jwt_token'];

// Función para obtener sensores (Adaptada a lo que Juan construirá)
function obtenerSensores($id, $token) {
    // Nota para Juan: He usado esta ruta como estándar 1.0
    $url = "http://api:8000/api/v1/parcelas/cliente/1"; // Ajustaremos cuando Juan cree la ruta específica
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true) ?: []; 
}

// Simulamos datos para el Frontend 1.0 si la tabla está vacía
$sensores_demo = [
    ['id' => 101, 'tipo' => 'Temperatura', 'valor' => '24.5', 'unidad' => '°C', 'ubicacion' => 'Sector Norte'],
    ['id' => 102, 'tipo' => 'Humedad Aire', 'valor' => '65', 'unidad' => '%', 'ubicacion' => 'Sector Sur'],
    ['id' => 103, 'tipo' => 'Humedad Suelo', 'valor' => '12', 'unidad' => '%', 'ubicacion' => 'Sector Norte']
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SIRA - Sensores</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f0; margin: 0; }
        nav { background: #2e7d32; color: white; padding: 1rem 2rem; }
        .container { padding: 2rem; max-width: 1100px; margin: auto; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .btn-back { text-decoration: none; color: #2e7d32; font-weight: bold; }
        
        .sensor-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .sensor-card { background: white; padding: 1.5rem; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; }
        .sensor-value { font-size: 2.5rem; font-weight: bold; color: #2e7d32; margin: 10px 0; }
        .sensor-label { color: #666; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        .sensor-meta { font-size: 0.9rem; color: #999; }
    </style>
</head>
<body>

<nav><h2>SIRA 🌱 | Monitoreo en Tiempo Real</h2></nav>

<div class="container">
    <div class="header-flex">
        <div>
            <a href="dashboard.php" class="btn-back">⬅ Volver al listado</a>
            <h1><?php echo htmlspecialchars($nombre_inv); ?></h1>
        </div>
        <div style="text-align: right">
            <span style="background: #4caf50; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem;">CONECTADO</span>
        </div>
    </div>

    <div class="sensor-grid">
        <?php foreach ($sensores_demo as $s): ?>
            <div class="sensor-card">
                <div class="sensor-label"><?php echo $s['tipo']; ?></div>
                <div class="sensor-value">
                    <?php echo $s['valor']; ?><span style="font-size: 1rem;"><?php echo $s['unidad']; ?></span>
                </div>
                <div class="sensor-meta">📍 <?php echo $s['ubicacion']; ?></div>
                <div style="margin-top: 15px; height: 4px; background: #eee; border-radius: 2px;">
                    <div style="width: 70%; height: 100%; background: #4caf50; border-radius: 2px;"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 3rem; background: #fff; padding: 2rem; border-radius: 15px; color: #666; text-align: center; border: 2px dashed #ccc;">
        <h3>🚀 Espacio para Gráficas (Próximamente)</h3>
        <p>Juan, aquí la IA podrá insertar los gráficos de Chart.js basándose en este contenedor.</p>
    </div>
</div>

</body>
</html>