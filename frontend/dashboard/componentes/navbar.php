<?php
/**
 * navbar.php - Componente Modular de Navegación del Dashboard
 * Refactorizado V6.18: Separación Nav vs Acción (Navegación a la izquierda, Acciones a la derecha)
 */

$es_admin = isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'], ['admin', 'root']);
$cliente_id_seleccionado = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : (($_SESSION['user_rol'] === 'cliente') ? ($_SESSION['cliente_id'] ?? null) : null);
$cliente_id = $cliente_id_seleccionado;

// Buffers para organizar por TIPO, no solo por entidad
$items_nav = [];     // Listados (Mis Parcelas, Mis Invernaderos, Mis Cultivos)
$items_actions = []; // Acciones de creación (Añadir..., Nuevo...)
$items_system = [];  // Administración (Usuarios, Localidades, Vistas)

// 1. SISTEMA / ADMIN / VISTAS
$vista_grid_activa = ($_SESSION['dashboard_view'] ?? 'grid') === 'grid';
$seccion_actual = $_GET['seccion'] ?? '';

// Mostrar selector de vista solo donde tiene sentido (Cultivos, Localidades y Selector Global)
$secciones_con_toggle = ['selector_cliente', 'gestion_cultivos', 'gestion_localidades'];
if (in_array($vista_actual, $secciones_con_toggle)) {
    $url_toggle = "dashboard.php?toggle_view=1";
    if ($seccion_actual) $url_toggle .= "&seccion=" . $seccion_actual;
    if ($cliente_id) $url_toggle .= "&cliente_id=" . $cliente_id;
    
    $items_system[] = '<a href="'.$url_toggle.'" class="btn-sira btn-primary btn-sm view-toggle-btn">' . ($vista_grid_activa ? 'Vista Lista' : 'Vista Mosaico') . '</a>';
}

if ($es_admin && $vista_actual === 'selector_cliente') {
    $items_system[] = '<a href="management/add_user.php" class="btn-sira btn-primary btn-sm">Añadir Usuario</a>';
    $items_system[] = '<a href="dashboard.php?seccion=localidades" class="btn-sira btn-primary btn-sm">Localidades</a>';
    
    // Añadimos Cultivos aquí para que aparezca después de Localidades en el pool de botones
    $is_active_cult = ($_GET['seccion'] ?? '') === 'cultivos';
    $items_system[] = '<a href="dashboard.php?seccion=cultivos" class="btn-sira btn-primary btn-sm '.($is_active_cult ? 'active' : '').'">Cultivos</a>';

    if ($_SESSION['user_rol'] === 'root') {
        $ver_ocultos = $_SESSION['ver_ocultos'] ?? false;
        $items_system[] = '<a href="dashboard.php?toggle_ocultos=1" class="btn-sira '.($ver_ocultos ? 'confirm-btn-yes' : 'btn-primary').' btn-sm">' . ($ver_ocultos ? 'Ocultar Inactivos' : 'Ver Ocultos') . '</a>';
    }
} elseif ($es_admin && $vista_actual === 'gestion_localidades') {
    $items_actions[] = '<a href="management/add_localidad.php" class="btn-sira btn-primary btn-sm">Añadir Localidad</a>';
}

// 2. PARCELAS
if ($cliente_id || $_SESSION['user_rol'] === 'cliente') {
        // Enlace al listado (Navegación)
        $is_active = $vista_actual === 'gestion_parcelas_total' || $seccion_actual === 'mis_parcelas';
        if ($vista_actual !== 'gestion_parcelas_total') {
            $items_nav[] = '<a href="dashboard.php?seccion=mis_parcelas' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm '.($is_active ? 'active' : '').'">Mis Parcelas</a>';
        }
        
        // Botón Añadir (Acción)
        $hide_add_parc = in_array($vista_actual, ['gestion_cultivos', 'gestion_invernaderos_total', 'gestion_cultivos_total']);
        if (($es_admin || ($_SESSION['cliente_id'] ?? null) == $cliente_id_seleccionado) && !$hide_add_parc) {
            $items_actions[] = '<a href="management/add_parcela.php?cliente_id=' . $cliente_id_seleccionado . '" class="btn-sira btn-primary btn-sm">Añadir Parcela</a>';
        }
}

// 3. INVERNADEROS
if ($cliente_id || $_SESSION['user_rol'] === 'cliente') {
    // Enlace al listado (Navegación)
    $is_active = $vista_actual === 'invernaderos' || $seccion_actual === 'mis_invernaderos' || $vista_actual === 'gestion_invernaderos_total';
    if ($vista_actual !== 'gestion_invernaderos_total') {
        $inv_btn_html = '<a href="dashboard.php?seccion=mis_invernaderos' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm '.($is_active ? 'active' : '').'">Mis Invernaderos</a>';
        
        // Si estamos en "Mis Parcelas", lo movemos a la derecha del todo (System/Trailing)
        if ($vista_actual === 'gestion_parcelas_total') {
            $items_system[] = $inv_btn_html;
        } else {
            $items_nav[] = $inv_btn_html;
        }
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
// 4. CULTIVOS
if ($cliente_id || $_SESSION['user_rol'] === 'cliente' || ($vista_actual === 'gestion_cultivos')) {
    // Enlace al listado (Navegación)
    $is_active = $seccion_actual === 'cultivos' || $vista_actual === 'gestion_cultivos';
    if ($vista_actual !== 'gestion_cultivos') {
        $items_nav[] = '<a href="dashboard.php?seccion=cultivos' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm '.($is_active ? 'active' : '').'">Mis Cultivos</a>';
    }
    
    // Botón de creación (Acción)
    if ($vista_actual === 'gestion_cultivos' || $vista_actual === 'invernaderos') {
        $items_actions[] = '<a href="management/add_cultivo.php" class="btn-sira btn-primary btn-sm">Añadir Cultivo</a>';
    }
}

// 5. MI CUENTA (Para todos) -> Se renderiza al principio del todo abajo

// --- RENDERIZADO FINAL ORGANIZADO: INICIO | NAV | ACCIONES | SYSTEM ---
$final_groups = [];

// El inicio siempre es el primero
$render_inicio = ($vista_actual !== 'selector_cliente' || !$es_admin);
$home_btn = '<a href="dashboard.php' . ($cliente_id ? '?cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">🏠 Inicio</a>';

if (!empty($items_nav)) $final_groups[] = implode(' ', $items_nav);
if (!empty($items_actions)) $final_groups[] = implode(' ', $items_actions);
if (!empty($items_system)) $final_groups[] = implode(' ', $items_system);

?>

<?php
// --- RENDERIZADO FINAL EQUITATIVO: POOL DE BOTONES ---
$pool_botones = [];

// Caso Especial: "Mis Parcelas" - Layout específico solicitado por el usuario
if ($vista_actual === 'gestion_parcelas_total') {
    $btn_cultivos = '<a href="'.$base_url.'/dashboard.php?seccion=cultivos' . ($cliente_id_seleccionado ? '&cliente_id='.$cliente_id_seleccionado : '') . '" class="btn-sira btn-primary btn-sm">Mis Cultivos</a>';
    $btn_invernaderos = '<a href="'.$base_url.'/dashboard.php?seccion=mis_invernaderos' . ($cliente_id_seleccionado ? '&cliente_id='.$cliente_id_seleccionado : '') . '" class="btn-sira btn-primary btn-sm">Mis Invernaderos</a>';
    $btn_add_parcela = '<a href="'.$base_url.'/management/add_parcela.php?cliente_id=' . $cliente_id_seleccionado . '" class="btn-sira btn-primary btn-sm">Añadir Parcela</a>';
    $btn_add_invernadero = '<a href="'.$base_url.'/management/add_invernadero.php?cliente_id=' . $cliente_id_seleccionado . '" class="btn-sira btn-primary btn-sm">Añadir Invernadero</a>';
    
    // Orden exacto: Mis Cultivos - Mis Invernaderos - [LOGO] - Añadir Parcela - Añadir Invernadero
    $pool_botones = [$btn_cultivos, $btn_invernaderos, $btn_add_parcela, $btn_add_invernadero];
} 
// Caso Especial: "Mis Invernaderos" - Layout específico solicitado por el usuario
elseif ($vista_actual === 'gestion_invernaderos_total') {
    $btn_cultivos = '<a href="'.$base_url.'/dashboard.php?seccion=cultivos' . ($cliente_id_seleccionado ? '&cliente_id='.$cliente_id_seleccionado : '') . '" class="btn-sira btn-primary btn-sm">Mis Cultivos</a>';
    $btn_parcelas = '<a href="'.$base_url.'/dashboard.php?seccion=mis_parcelas' . ($cliente_id_seleccionado ? '&cliente_id='.$cliente_id_seleccionado : '') . '" class="btn-sira btn-primary btn-sm">Mis Parcelas</a>';
    $btn_add_invernadero = '<a href="'.$base_url.'/management/add_invernadero.php?cliente_id=' . $cliente_id_seleccionado . '" class="btn-sira btn-primary btn-sm">Añadir Invernadero</a>';
    $btn_add_parcela = '<a href="'.$base_url.'/management/add_parcela.php?cliente_id=' . $cliente_id_seleccionado . '" class="btn-sira btn-primary btn-sm">Añadir Parcela</a>';
    
    // Orden exacto: Mis Cultivos - Mis Parcelas - [LOGO] - Añadir Invernadero - Añadir Parcela
    $pool_botones = [$btn_cultivos, $btn_parcelas, $btn_add_invernadero, $btn_add_parcela];
}
else {
    // 1. Mi Cuenta (RELOCATED: Now in dashboard/componentes/header.php breadcrumbs)
    
    // 2. Inicio (ELIMINADO: Ahora el logo central es el botón de inicio)
    
    // 3. Navegación, Acciones y Sistema
    $pool_botones = array_merge($pool_botones, $items_nav, $items_actions, $items_system);
}

// Dividir el pool en dos mitades: favorecemos la derecha si es impar para el Panel Global (2-center-3)
$total_botones = count($pool_botones);
$mitad = floor($total_botones / 2);

$botones_izq = array_slice($pool_botones, 0, $mitad);
$botones_der = array_slice($pool_botones, $mitad);
?>

<?php if ($total_botones > 0 || true): ?>
    <nav class="dashboard-navbar" id="dashboard-nav">
        <!-- Toggler para Móvil (Oculto en Desktop) -->
        <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox">

        <!-- CABECERA MÓVIL: Centrado Premium (Spacer Izq | Logo Centro | Menú Der) -->
        <div class="nav-mobile-header">
            <div class="nav-mobile-header-spacer"></div>

            <a href="<?= $base_url ?>/dashboard.php" class="nav-mobile-logo" title="Inicio">
                <img src="<?= $base_url ?>/assets/img/favicon.svg" alt="SIRA" class="nav-symbol-mini">
            </a>

            <label for="nav-toggle" class="nav-toggle-label" title="Menú de Navegación">
                <span class="hamburger"></span>
            </label>
        </div>

        <div class="nav-items-wrapper">
            <!-- GRUPO IZQUIERDO -->
            <div class="nav-group nav-group-left">
                <?= implode('', $botones_izq) ?>
            </div>

            <!-- CENTRO: Símbolo SIRA (Inicio) -->
            <div class="nav-group nav-group-center">
                <a href="<?= $base_url ?>/dashboard.php" class="nav-symbol-anchor" title="Volver al Inicio">
                    <img src="<?= $base_url ?>/assets/img/favicon.svg" alt="SIRA" class="nav-symbol-mini">
                </a>
            </div>

            <!-- GRUPO DERECHO -->
            <div class="nav-group nav-group-right">
                <?= implode('', $botones_der) ?>
            </div>
        </div>
    </nav>
<?php endif; ?>
