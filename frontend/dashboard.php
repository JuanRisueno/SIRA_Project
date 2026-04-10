<?php
session_start();
if (!isset($_SESSION['jwt_token'])) { header("Location: index.php"); exit(); }

$token = $_SESSION['jwt_token'];

function obtenerJerarquia($token, $cliente_id = null) {
    if ($cliente_id) {
        $url = "http://api:8000/api/v1/clientes/me/jerarquia?cliente_id=" . $cliente_id;
    } else {
        $url = "http://api:8000/api/v1/clientes/me/jerarquia";
    }
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200) ? json_decode($response, true) : null;
}

function listarTodosLosClientes($token) {
    $url = "http://api:8000/api/v1/clientes/";
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200) ? json_decode($response, true) : [];
}

function setClienteStatus($token, $cliente_id, $activa) {
    $status_str = $activa ? "true" : "false";
    $url = "http://api:8000/api/v1/clientes/$cliente_id/status?activa=$status_str";
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200);
}

// --- MANEJADOR DE ACCIONES PHP ---
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_user = (int)$_GET['id'];
    if ($_GET['accion'] === 'ocultar') {
        setClienteStatus($token, $id_user, false);
    } elseif ($_GET['accion'] === 'activar') {
        setClienteStatus($token, $id_user, true);
    }
    header("Location: dashboard.php");
    exit();
}

// --- MANEJADOR DE VISTA ROOT (OCULTOS) ---
if (isset($_GET['toggle_ocultos'])) {
    $_SESSION['ver_ocultos'] = !($_SESSION['ver_ocultos'] ?? false);
    header("Location: dashboard.php");
    exit();
}

$es_admin = isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'], ['admin', 'root']);
$cliente_id_seleccionado = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;
$cliente_a_confirmar = null;

if ($es_admin && !$cliente_id_seleccionado) {
    // Si es admin y no ha seleccionado cliente, mostramos el selector
    $todos_los_clientes = listarTodosLosClientes($token);
    
    // Si estamos en proceso de confirmar ocultación, buscamos el nombre
    $cliente_a_confirmar = null;
    if (isset($_GET['confirmar_ocultar']) && isset($_GET['id'])) {
        foreach ($todos_los_clientes as $c) {
            if ($c['cliente_id'] == (int)$_GET['id']) {
                $cliente_a_confirmar = $c;
                break;
            }
        }
    }
    
    $nombre_panel = ($_SESSION['user_rol'] === 'root') ? 'Súper Panel (Root)' : 'Panel de Gestión (Admin)';
    $arbol = ['nombre_empresa' => $nombre_panel];
    
    $vista_actual = 'selector_cliente';
} else {
    // Si es cliente, o es admin y seleccionó un cliente
    $arbol = obtenerJerarquia($token, $cliente_id_seleccionado);
}

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
if (!isset($vista_actual)) {
    $vista_actual = 'localidades';
}
$localidades_data = $arbol['localidades'] ?? [];
$parcelas_data    = [];
$invernaderos_data = [];
$loc_seleccionada  = null;
$parc_seleccionada = null;

// Salto inteligente 1: solo 1 localidad → ir directo a parcelas
if ($vista_actual === 'localidades' && count($localidades_data) === 1 && !isset($_GET['localidad_cp'])) {
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

<?php if ($cliente_a_confirmar): ?>
    <!-- CUADRO DE CONFIRMACIÓN PHP (SIN JS) -->
    <div class="confirm-overlay">
        <div class="confirm-card">
            <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
            <h2>¿Estás seguro?</h2>
            <p>
                Vas a ocultar al agricultor:<br>
                <strong><?= htmlspecialchars($cliente_a_confirmar['nombre_empresa']) ?></strong> (CIF: <?= htmlspecialchars($cliente_a_confirmar['cif']) ?>)<br><br>
                Dejará de ser visible en el panel de gestión para todos los administradores.
            </p>
            <div class="confirm-actions">
                <a href="dashboard.php?accion=ocultar&id=<?= $cliente_a_confirmar['cliente_id'] ?>" class="confirm-btn-yes">Sí, ocultar</a>
                <a href="dashboard.php" class="confirm-btn-no">No, cancelar</a>
            </div>
        </div>
    </div>
<?php endif; ?>

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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 class="dashboard-title" style="margin-bottom: 0.5rem;">
                <?php
                    if ($vista_actual === 'selector_cliente') {
                        echo ($_SESSION['user_rol'] === 'root') ? "Control Global de Infraestructura" : "Lista de Agricultores";
                    }
                    elseif ($vista_actual === 'localidades')  echo "Tus Zonas Geográficas";
                    elseif ($vista_actual === 'parcelas')   echo "Parcelas en " . htmlspecialchars($loc_seleccionada['municipio']);
                    else {
                        $dir_limpia = explode(' - ', $parc_seleccionada['direccion'])[0];
                        echo "Invernaderos en " . htmlspecialchars($loc_seleccionada['municipio']) . " — " . htmlspecialchars($dir_limpia);
                    }
                ?>
            </h1>
            <p class="dashboard-subtitle">
                <?php
                    if ($vista_actual === 'selector_cliente') {
                        echo ($_SESSION['user_rol'] === 'root') ? "Acceso total a clientes y administradores del sistema." : "Selecciona un cliente para supervisar su actividad.";
                    }
                    else echo "Selecciona un elemento para navegar por tu infraestructura.";
                ?>
            </p>
        </div>

        <?php if ($vista_actual === 'selector_cliente' && $_SESSION['user_rol'] === 'root'): ?>
            <?php $ver_ocultos = $_SESSION['ver_ocultos'] ?? false; ?>
            <a href="dashboard.php?toggle_ocultos=1" class="card-btn" style="margin-top: 0; width: auto; padding: 0.5rem 1rem; font-size: 0.8rem; background: <?= $ver_ocultos ? '#ef4444' : 'var(--color-primary)' ?>; color: <?= $ver_ocultos ? '#fff' : '#000' ?>; border: <?= $ver_ocultos ? '1px solid rgba(255,255,255,0.4)' : 'none' ?>; display: flex; align-items: center; gap: 8px;">
                <?php if ($ver_ocultos): ?>
                    <!-- Icono de Ocultar (Equis/Cerrar) -->
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Ocultar Inactivos
                <?php else: ?>
                    <!-- Icono de Ojo -->
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    Ver Ocultos
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- Grid de tarjetas -->
    <div class="grid">

        <?php if ($vista_actual === 'selector_cliente'): ?>
            <?php 
                $ver_ocultos_actual = $_SESSION['ver_ocultos'] ?? false;
                foreach ($todos_los_clientes as $cli): 
                    // Filtrar: Si está oculto y no tenemos activado 'ver_ocultos', saltar.
                    if (!$cli['activa'] && !$ver_ocultos_actual) continue;
                    
                    // Si el que llama es Admin, ya filtramos en API que no vea otros admins.
                    // Pero por seguridad extra, evitamos que un Admin oculte al Root aunque lo viera.
                    if ($cli['rol'] === 'root') continue;
            ?>
                <div class="card" style="<?= !$cli['activa'] ? 'opacity: 0.5; border-style: dashed;' : '' ?>">
                    <!-- Menú de opciones -->
                    <div class="card-options">
                        <button class="options-btn" title="Opciones">⋮</button>
                        <div class="options-menu">
                            <button onclick="alert('Editar cliente')">📝 Editar</button>
                            <?php if ($cli['activa']): ?>
                                <a href="dashboard.php?confirmar_ocultar=1&id=<?= $cli['cliente_id'] ?>" class="delete-opt">👁️‍🗨️ Ocultar</a>
                            <?php else: ?>
                                <a href="dashboard.php?accion=activar&id=<?= $cli['cliente_id'] ?>" style="color: var(--color-primary);">👁️ Activar</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <span class="status">ID <?= $cli['cliente_id'] ?> | <?= htmlspecialchars(strtoupper($cli['rol'])) ?></span>
                    <?php if (!$cli['activa']): ?>
                        <span class="status" style="right: 170px; background: #ef4444; color: white;">INACTIVO</span>
                    <?php endif; ?>
                    
                    <h3><?= htmlspecialchars($cli['nombre_empresa']) ?></h3>
                    <div class="meta">🏢 CIF: <?= htmlspecialchars($cli['cif']) ?></div>
                    <div class="meta">👤 <?= htmlspecialchars($cli['persona_contacto']) ?></div>
                    <a href="dashboard.php?cliente_id=<?= $cli['cliente_id'] ?>" class="card-btn">Ver Entorno →</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($vista_actual === 'localidades'): ?>
            <?php foreach ($localidades_data as $loc): ?>
                <div class="card">
                    <!-- Menú de opciones -->
                    <div class="card-options">
                        <button class="options-btn" title="Opciones">⋮</button>
                        <div class="options-menu">
                            <button onclick="alert('Editar localidad')">📝 Editar</button>
                            <button class="delete-opt" onclick="alert('Desactivar localidad')">👁️‍🗨️ Ocultar</button>
                        </div>
                    </div>

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
                    <!-- Menú de opciones -->
                    <div class="card-options">
                        <button class="options-btn" title="Opciones">⋮</button>
                        <div class="options-menu">
                            <button onclick="alert('Editar parcela')">📝 Editar</button>
                            <button class="delete-opt" onclick="alert('Desactivar parcela')">👁️‍🗨️ Ocultar</button>
                        </div>
                    </div>

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
                    <!-- Menú de opciones -->
                    <div class="card-options">
                        <button class="options-btn" title="Opciones">⋮</button>
                        <div class="options-menu">
                            <button onclick="alert('Editar invernadero')">📝 Editar</button>
                            <button class="delete-opt" onclick="alert('Desactivar invernadero')">👁️‍🗨️ Ocultar</button>
                        </div>
                    </div>

                    <span class="status">ACTIVO</span>
                    <h3><?= htmlspecialchars($inv['nombre']) ?></h3>
                    <div class="meta">📏 <?= htmlspecialchars($inv['largo_m']) ?>m × <?= htmlspecialchars($inv['ancho_m']) ?>m</div>
                    <div class="meta">🌾 Cultivo: <?= htmlspecialchars($inv['cultivo'] ?? 'Sin asignar') ?></div>
                    <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $cliente_id_seleccionado ? '&cliente_id=' . $cliente_id_seleccionado : '' ?>" class="card-btn">Panel IoT →</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Gestionar clics en los botones de tres puntos
    const optionBtns = document.querySelectorAll('.options-btn');
    
    optionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar que el clic llegue a la tarjeta
            const menu = this.nextElementSibling;
            
            // Cerrar otros menús abiertos
            document.querySelectorAll('.options-menu.show').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            
            // Alternar el menú actual
            menu.classList.toggle('show');
        });
    });

    // 2. Cerrar menús al hacer clic fuera
    document.addEventListener('click', function() {
        document.querySelectorAll('.options-menu.show').forEach(m => {
            m.classList.remove('show');
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>