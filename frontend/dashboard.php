<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: index.php"); exit(); }

$token = $_SESSION['jwt_token'];

function obtenerJerarquia($token) {
    $url = "http://api:8000/api/v1/clientes/me/jerarquia";
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200) ? json_decode($response, true) : null;
}

$arbol = obtenerJerarquia($token);

$page_title = "SIRA - Panel de Control";
$page_css   = "dashboard";   // <- Carga /css/dashboard.css
require_once 'includes/header.php';

// ── Error: API caída ──
if ($arbol === null) {
    echo "<div class='container'><div class='error-panel'>";
    echo "<h2>⚠️ Servicio Temporalmente Caído</h2>";
    echo "<p>No se pudo conectar con los servidores de SIRA. Por favor, inténtalo de nuevo en unos minutos.</p>";
    echo "</div></div>";
    require_once 'includes/footer.php';
    exit();
}

// ── Lógica de navegación y saltos inteligentes ──
$vista_actual     = 'localidades';
$localidades_data = $arbol['localidades'] ?? [];
$parcelas_data    = [];
$invernaderos_data = [];
$loc_seleccionada  = null;
$parc_seleccionada = null;

// Salto inteligente 1: solo 1 localidad → ir directo a parcelas
if (count($localidades_data) === 1 && !isset($_GET['localidad_cp'])) {
    $_GET['localidad_cp'] = $localidades_data[0]['codigo_postal'];
}

if (isset($_GET['localidad_cp'])) {
    $cp = $_GET['localidad_cp'];
    foreach ($localidades_data as $loc) {
        if ($loc['codigo_postal'] === $cp) {
            $loc_seleccionada = $loc;
            $parcelas_data    = $loc['parcelas'];
            $vista_actual     = 'parcelas';
            break;
        }
    }
}

// Salto inteligente 2: solo 1 parcela → ir directo a invernaderos
if ($vista_actual === 'parcelas' && count($parcelas_data) === 1 && !isset($_GET['parcela_id'])) {
    $_GET['parcela_id'] = $parcelas_data[0]['parcela_id'];
}

if (isset($_GET['parcela_id'])) {
    $p_id = (int)$_GET['parcela_id'];
    foreach ($parcelas_data as $parc) {
        if ($parc['parcela_id'] === $p_id) {
            $parc_seleccionada  = $parc;
            $invernaderos_data  = $parc['invernaderos'];
            $vista_actual       = 'invernaderos';
            break;
        }
    }
}
?>

<div class="container">

    <!-- Migas de pan -->
    <div class="breadcrumbs">
        <span>📍 Tú estás aquí:</span>
        <a href="dashboard.php">💼 <?= htmlspecialchars($arbol['nombre_empresa']) ?></a>
        <?php if ($loc_seleccionada): ?>
            <span>/</span>
            <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>">
                <?= htmlspecialchars($loc_seleccionada['municipio']) ?>
            </a>
        <?php endif; ?>
        <?php if ($parc_seleccionada): ?>
            <span>/</span>
            <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?>">
                Parcela <?= htmlspecialchars($parc_seleccionada['ref_catastral']) ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- Título -->
    <h1 class="dashboard-title">
        <?php
            if ($vista_actual === 'localidades') echo "Tus Zonas Geográficas";
            elseif ($vista_actual === 'parcelas')   echo "Parcelas en " . htmlspecialchars($loc_seleccionada['municipio']);
            else                                    echo "Invernaderos — " . htmlspecialchars($parc_seleccionada['ref_catastral']);
        ?>
    </h1>
    <p class="dashboard-subtitle">Selecciona un elemento para navegar por tu infraestructura.</p>

    <!-- Grid de tarjetas -->
    <div class="grid">

        <?php if ($vista_actual === 'localidades'): ?>
            <?php foreach ($localidades_data as $loc): ?>
                <div class="card">
                    <span class="status">CP <?= htmlspecialchars($loc['codigo_postal']) ?></span>
                    <h3><?= htmlspecialchars($loc['municipio']) ?></h3>
                    <div class="meta">📌 Provincia: <?= htmlspecialchars($loc['provincia']) ?></div>
                    <div class="meta">🚜 <?= $loc['num_parcelas'] ?> Parcelas</div>
                    <div class="meta">🌱 <?= $loc['num_invernaderos_total'] ?> Invernaderos</div>
                    <a href="dashboard.php?localidad_cp=<?= urlencode($loc['codigo_postal']) ?>" class="card-btn">Ver Parcelas →</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($vista_actual === 'parcelas'): ?>
            <?php if (empty($parcelas_data)): ?>
                <div class="card empty-state"><p>No hay parcelas registradas en esta localidad.</p></div>
            <?php endif; ?>
            <?php foreach ($parcelas_data as $parc): ?>
                <div class="card">
                    <span class="status">ID <?= $parc['parcela_id'] ?></span>
                    <h3><?= htmlspecialchars($parc['direccion']) ?></h3>
                    <div class="meta">📋 Ref. Catastral: <?= htmlspecialchars($parc['ref_catastral']) ?></div>
                    <div class="meta">🌱 <?= $parc['num_invernaderos'] ?> Invernaderos</div>
                    <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc['parcela_id'] ?>" class="card-btn">Ver Invernaderos →</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($vista_actual === 'invernaderos'): ?>
            <?php if (empty($invernaderos_data)): ?>
                <div class="card empty-state"><p>No hay invernaderos en esta parcela.</p></div>
            <?php endif; ?>
            <?php foreach ($invernaderos_data as $inv): ?>
                <div class="card">
                    <span class="status">ACTIVO</span>
                    <h3><?= htmlspecialchars($inv['nombre']) ?></h3>
                    <div class="meta">📏 <?= htmlspecialchars($inv['largo_m']) ?>m × <?= htmlspecialchars($inv['ancho_m']) ?>m</div>
                    <div class="meta">🌾 Cultivo: <?= htmlspecialchars($inv['cultivo'] ?? 'Sin asignar') ?></div>
                    <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?>" class="card-btn">Panel IoT →</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>