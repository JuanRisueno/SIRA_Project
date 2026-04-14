<?php
/**
 * navbar.php - Componente Modular de Navegación del Dashboard
 * Refactorizado V6.18: Separación Nav vs Acción (Navegación a la izquierda, Acciones a la derecha)
 */

$es_admin = isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'], ['admin', 'root']);
$cliente_id = $cliente_id_seleccionado;

// Buffers para organizar por TIPO, no solo por entidad
$items_nav = [];     // Listados (Mis Parcelas, Mis Invernaderos, Mis Cultivos)
$items_actions = []; // Acciones de creación (Añadir..., Nuevo...)
$items_system = [];  // Administración (Usuarios, Localidades, Vistas)

// 1. SISTEMA / ADMIN / VISTAS
$vista_grid_activa = ($_SESSION['dashboard_view'] ?? 'grid') === 'grid';
$seccion_actual = $_GET['seccion'] ?? '';

// Mostrar selector de vista solo donde tiene sentido (Cultivos y Selector Global)
$secciones_con_toggle = ['selector_cliente', 'gestion_cultivos'];
if (in_array($vista_actual, $secciones_con_toggle)) {
    $url_toggle = "dashboard.php?toggle_view=1";
    if ($seccion_actual) $url_toggle .= "&seccion=" . $seccion_actual;
    if ($cliente_id) $url_toggle .= "&cliente_id=" . $cliente_id;
    
    $items_system[] = '<a href="'.$url_toggle.'" class="btn-sira btn-secondary btn-sm">' . ($vista_grid_activa ? 'Vista Lista' : 'Vista Mosaico') . '</a>';
}

if ($es_admin && $vista_actual === 'selector_cliente') {
    $items_system[] = '<a href="management/add_user.php" class="btn-sira btn-primary btn-sm">Añadir Usuario</a>';
    $items_system[] = '<a href="dashboard.php?seccion=localidades" class="btn-sira btn-secondary btn-sm">Localidades</a>';
    
    if ($_SESSION['user_rol'] === 'root') {
        $ver_ocultos = $_SESSION['ver_ocultos'] ?? false;
        $items_system[] = '<a href="dashboard.php?toggle_ocultos=1" class="btn-sira '.($ver_ocultos ? 'confirm-btn-yes' : 'btn-primary').' btn-sm">' . ($ver_ocultos ? 'Ocultar Inactivos' : 'Ver Ocultos') . '</a>';
    }
} elseif ($es_admin && $vista_actual === 'gestion_localidades') {
    $items_actions[] = '<a href="management/add_localidad.php" class="btn-sira btn-primary btn-sm">Añadir Localidad</a>';
}

// 2. PARCELAS
if ($cliente_id || $_SESSION['user_rol'] === 'cliente') {
    if ($vista_actual !== 'invernaderos') {
        // Enlace al listado (Navegación)
        if ($vista_actual !== 'gestion_parcelas_total') {
            $items_nav[] = '<a href="dashboard.php?seccion=mis_parcelas' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-secondary btn-sm">Mis Parcelas</a>';
        }
        
        // Botón Añadir (Acción)
        $hide_add_parc = in_array($vista_actual, ['gestion_cultivos', 'gestion_invernaderos_total', 'gestion_cultivos_total']);
        if (($es_admin || ($_SESSION['cliente_id'] ?? null) == $cliente_id) && !$hide_add_parc) {
            $items_actions[] = '<a href="management/add_parcela.php?cliente_id=' . $cliente_id . '" class="btn-sira btn-primary btn-sm">Añadir Parcela</a>';
        }
    }
}

// 3. INVERNADEROS
if ($cliente_id || $_SESSION['user_rol'] === 'cliente') {
    // Enlace al listado (Navegación)
    if ($vista_actual !== 'gestion_invernaderos_total') {
        $items_nav[] = '<a href="dashboard.php?seccion=mis_invernaderos' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-secondary btn-sm">Mis Invernaderos</a>';
    }
    
    // Botón Añadir (Acción)
    if ($vista_actual === 'invernaderos' || $vista_actual === 'gestion_invernaderos_total') {
        $url_add_inv = "management/add_invernadero.php?cliente_id=" . $cliente_id;
        if ($vista_actual === 'invernaderos') {
            $url_add_inv .= '&parcela_id=' . $parc_seleccionada['parcela_id'] . '&localidad_cp=' . urlencode($loc_seleccionada['codigo_postal']);
        }
        $items_actions[] = '<a href="' . $url_add_inv . '" class="btn-sira btn-primary btn-sm">Añadir Invernadero</a>';
    }
}

// 4. CULTIVOS
if ($cliente_id || $_SESSION['user_rol'] === 'cliente' || ($vista_actual === 'gestion_cultivos')) {
    // Enlace al listado (Navegación)
    if ($vista_actual !== 'gestion_cultivos') {
        $items_nav[] = '<a href="dashboard.php?seccion=cultivos' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-secondary btn-sm">Mis Cultivos</a>';
    }
    
    // Botón de creación (Acción)
    if ($vista_actual === 'gestion_cultivos' || $vista_actual === 'invernaderos') {
        $items_actions[] = '<a href="management/add_cultivo.php" class="btn-sira btn-primary btn-sm">Nuevo Cultivo</a>';
    }
}

// 5. MI CUENTA (Para todos) -> Se renderiza al principio del todo abajo

// --- RENDERIZADO FINAL ORGANIZADO: INICIO | NAV | ACCIONES | SYSTEM ---
$final_groups = [];

// El inicio siempre es el primero
$render_inicio = ($vista_actual !== 'selector_cliente' || !$es_admin);
$home_btn = '<a href="dashboard.php' . ($cliente_id ? '?cliente_id='.$cliente_id : '') . '" class="btn-sira btn-secondary btn-sm">🏠 Inicio</a>';

if (!empty($items_nav)) $final_groups[] = implode(' ', $items_nav);
if (!empty($items_actions)) $final_groups[] = implode(' ', $items_actions);
if (!empty($items_system)) $final_groups[] = implode(' ', $items_system);

?>

<?php if (trim($home_btn) || !empty($final_groups)): ?>
    <nav class="dashboard-navbar" id="dashboard-nav">
        <!-- Toggler para Móvil (0% JS - Checkbox Hack) -->
        <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox">
        <label for="nav-toggle" class="nav-toggle-label" title="Menú de Navegación">
            <span class="hamburger"></span>
        </label>

        <div class="nav-items-wrapper">
            <?php if ($_SESSION['user_rol'] === 'cliente' && $vista_actual === 'localidades' && !isset($_GET['seccion'])): ?>
                <a href="management/edit_user.php?id=<?= $_SESSION['cliente_id'] ?>" class="btn-sira btn-secondary btn-sm">👤 Mi Cuenta</a>
                <span class="nav-separator">|</span>
            <?php endif; ?>

            <?php if ($render_inicio): ?>
                <?= $home_btn ?>
                <?php if (!empty($final_groups)): ?>
                    <span class="nav-separator">|</span>
                <?php endif; ?>
            <?php endif; ?>

            <?= implode('<span class="nav-separator">|</span>', $final_groups) ?>
        </div>
    </nav>
<?php endif; ?>
