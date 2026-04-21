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

// [V14.0] Determinación de contexto para retorno global
$current_context = !empty($seccion_actual) ? $seccion_actual : $vista_actual;
$from_p = "&from=" . urlencode($current_context);

// Mostrar selector de vista solo donde tiene sentido (Cultivos, Localidades y Selector Global)
$secciones_con_toggle = ['selector_cliente', 'gestion_cultivos', 'gestion_localidades'];
if (in_array($vista_actual, $secciones_con_toggle)) {
    $url_toggle = "dashboard.php?toggle_view=1";
    if ($seccion_actual) $url_toggle .= "&seccion=" . $seccion_actual;
    if ($cliente_id) $url_toggle .= "&cliente_id=" . $cliente_id;
    
    $items_system[] = '<a href="'.$url_toggle.'" class="btn-sira btn-primary btn-sm view-toggle-btn">' . ($vista_grid_activa ? 'Vista Lista' : 'Vista Mosaico') . '</a>';
}

if ($es_admin && $vista_actual === 'selector_cliente') {
    $items_system[] = '<a href="formularios/formulario_usuario.php?cliente_id='.$cliente_id_seleccionado.$from_p.'" class="btn-sira btn-primary btn-sm">Añadir Usuario</a>';
    $items_system[] = '<a href="dashboard.php?seccion=localidades" class="btn-sira btn-primary btn-sm">Localidades</a>';
    
    // Añadimos Cultivos aquí para que aparezca después de Localidades en el pool de botones
    $is_active_cult = ($_GET['seccion'] ?? '') === 'cultivos';
    $items_system[] = '<a href="dashboard.php?seccion=cultivos" class="btn-sira btn-primary btn-sm '.($is_active_cult ? 'active' : '').'">Cultivos</a>';
} elseif ($es_admin && $vista_actual === 'gestion_localidades') {
    $items_actions[] = '<a href="formularios/formulario_localidad.php?cliente_id='.$cliente_id_seleccionado.$from_p.'" class="btn-sira btn-primary btn-sm">Añadir Localidad</a>';
}

// 1.1 TOGGLE OCULTOS (Global para Admin/Root)
if ($es_admin) {
    $ver_ocultos = $_SESSION['ver_ocultos'] ?? false;
    // Construir URL manteniendo el estado actual (sección, cliente_id, etc.)
    $params_toggle = $_GET;
    // [V13.1] Limpieza de parámetros volátiles para evitar bloqueos de estado
    unset($params_toggle['reset_ocultos'], $params_toggle['msg'], $params_toggle['error']);
    
    $params_toggle['toggle_ocultos'] = 1;
    $url_toggle_ocultos = "dashboard.php?" . http_build_query($params_toggle);

    // Construir Texto Dinámico según Sección
    $txt_ocultos = "Ver Ocultos";
    $txt_visibles = "Ver Activos";
    
    $sec = $_GET['seccion'] ?? '';
    if ($sec === 'mis_parcelas') {
        $txt_ocultos = "Ver Parcelas Ocultas";
        $txt_visibles = "Ver Parcelas Activas";
    } elseif ($sec === 'mis_invernaderos') {
        $txt_ocultos = "Ver Invernaderos Ocultos";
        $txt_visibles = "Ver Invernaderos Activos";
    }

    // [V13.2] Mostramos el toggle en todas las secciones excepto en Jornadas (por petición de usuario v22.4)
    if ($vista_actual !== 'jornadas_resumen') {
        $items_system[] = '<a href="'.$url_toggle_ocultos.'" class="btn-sira '.($ver_ocultos ? 'confirm-btn-yes' : 'btn-primary').' btn-sm" title="'.($ver_ocultos ? 'Ocultar elementos archivados' : 'Ver todos los elementos, incluyendo archivados').'">' . ($ver_ocultos ? $txt_visibles : $txt_ocultos) . '</a>';
    }
}

// 2. PARCELAS
if ($cliente_id || $_SESSION['user_rol'] === 'cliente') {
        // Enlace al listado (Navegación)
        $is_active = $vista_actual === 'gestion_parcelas_total' || $seccion_actual === 'mis_parcelas';
        if ($vista_actual !== 'gestion_parcelas_total') {
            $items_nav[] = '<a href="dashboard.php?seccion=mis_parcelas&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm '.($is_active ? 'active' : '').'">Mis Parcelas</a>';
        }
        
        // Botón Añadir (Acción)
        $hide_add_parc = in_array($vista_actual, ['gestion_cultivos', 'gestion_invernaderos_total', 'gestion_cultivos_total']);
        if (($es_admin || ($_SESSION['cliente_id'] ?? null) == $cliente_id_seleccionado) && !$hide_add_parc) {
            $items_actions[] = '<a href="formularios/formulario_parcela.php?cliente_id=' . $cliente_id_seleccionado . $from_p . '" class="btn-sira btn-primary btn-sm">Añadir Parcela</a>';
        }
}

// 3. INVERNADEROS
if ($cliente_id || $_SESSION['user_rol'] === 'cliente') {
    // Enlace al listado (Navegación)
    $is_active = $vista_actual === 'invernaderos' || $seccion_actual === 'mis_invernaderos' || $vista_actual === 'gestion_invernaderos_total';
    if ($vista_actual !== 'gestion_invernaderos_total') {
        $inv_btn_html = '<a href="dashboard.php?seccion=mis_invernaderos&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm '.($is_active ? 'active' : '').'">Mis Invernaderos</a>';
        
        // Solo añadimos a nav si NO estamos en la vista de parcelas (donde se gestiona manualmente)
        if ($vista_actual !== 'gestion_parcelas_total') {
            $items_nav[] = $inv_btn_html;
        }
    }
    
    // Botón Añadir (Acción)
    if ($vista_actual === 'invernaderos' || $vista_actual === 'gestion_invernaderos_total' || $vista_actual === 'jornadas_resumen') {
        $url_add_inv = "formularios/formulario_invernadero.php?cliente_id=" . $cliente_id;
        if ($vista_actual === 'invernaderos') {
            $url_add_inv .= '&parcela_id=' . $parc_seleccionada['parcela_id'] . '&localidad_cp=' . urlencode($loc_seleccionada['codigo_postal']);
        }
        $url_add_inv .= $from_p;
        $items_actions[] = '<a href="' . $url_add_inv . '" class="btn-sira btn-primary btn-sm">Añadir Invernadero</a>';
    }
}

// 4. CULTIVOS
if ($cliente_id || $_SESSION['user_rol'] === 'cliente' || ($vista_actual === 'gestion_cultivos')) {
    // Enlace al listado (Navegación)
    $is_active = $seccion_actual === 'cultivos' || $vista_actual === 'gestion_cultivos';
    if ($vista_actual !== 'gestion_cultivos') {
        $items_nav[] = '<a href="dashboard.php?seccion=cultivos&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm '.($is_active ? 'active' : '').'">Mis Cultivos</a>';
    }
    
    // Botón de creación (Acción)
    if ($vista_actual === 'gestion_cultivos' || $vista_actual === 'invernaderos') {
        $items_actions[] = '<a href="formularios/formulario_cultivo.php?cliente_id='.$cliente_id_seleccionado.$from_p.'" class="btn-sira btn-primary btn-sm">Añadir Cultivo</a>';
    }
}

// 5. JORNADAS LABORALES (Cliente y Admin con cliente seleccionado)
if ($cliente_id || $_SESSION['user_rol'] === 'cliente') {
    $is_active = $vista_actual === 'jornadas_resumen';
    $items_nav[] = '<a href="dashboard.php?seccion=jornadas_resumen' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm ' . ($is_active ? 'active' : '') . '">🕒 Jornadas</a>';
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
    $btn_cultivos = '<a href="'.$base_url.'/dashboard.php?seccion=cultivos&reset_ocultos=1' . ($cliente_id_seleccionado ? '&cliente_id='.$cliente_id_seleccionado : '') . '" class="btn-sira btn-primary btn-sm">Mis Cultivos</a>';
    $btn_invernaderos = '<a href="'.$base_url.'/dashboard.php?seccion=mis_invernaderos&reset_ocultos=1' . ($cliente_id_seleccionado ? '&cliente_id='.$cliente_id_seleccionado : '') . '" class="btn-sira btn-primary btn-sm">Mis Invernaderos</a>';
    $btn_add_parcela = '<a href="'.$base_url.'/formularios/formulario_parcela.php?cliente_id=' . $cliente_id_seleccionado . $from_p . '" class="btn-sira btn-primary btn-sm">Añadir Parcela</a>';
    $btn_add_invernadero = '<a href="'.$base_url.'/formularios/formulario_invernadero.php?cliente_id=' . $cliente_id_seleccionado . $from_p . '" class="btn-sira btn-primary btn-sm">Añadir Invernadero</a>';
    
    // Orden exacto: Mis Cultivos - Mis Invernaderos - [LOGO] - Añadir Parcela - Añadir Invernadero
    $pool_botones = array_merge([$btn_cultivos, $btn_invernaderos, $btn_add_parcela, $btn_add_invernadero], $items_system);
    $total_botones = count($pool_botones);
} 
// Caso Especial: "Mis Invernaderos" - Layout específico solicitado por el usuario
elseif ($vista_actual === 'gestion_invernaderos_total') {
    $btn_cultivos = '<a href="'.$base_url.'/dashboard.php?seccion=cultivos&reset_ocultos=1' . ($cliente_id_seleccionado ? '&cliente_id='.$cliente_id_seleccionado : '') . '" class="btn-sira btn-primary btn-sm">Mis Cultivos</a>';
    $btn_parcelas = '<a href="'.$base_url.'/dashboard.php?seccion=mis_parcelas&reset_ocultos=1' . ($cliente_id_seleccionado ? '&cliente_id='.$cliente_id_seleccionado : '') . '" class="btn-sira btn-primary btn-sm">Mis Parcelas</a>';
    $btn_add_invernadero = '<a href="'.$base_url.'/formularios/formulario_invernadero.php?cliente_id=' . $cliente_id_seleccionado . $from_p . '" class="btn-sira btn-primary btn-sm">Añadir Invernadero</a>';
    $btn_add_parcela = '<a href="'.$base_url.'/formularios/formulario_parcela.php?cliente_id=' . $cliente_id_seleccionado . $from_p . '" class="btn-sira btn-primary btn-sm">Añadir Parcela</a>';
    
    // Orden exacto: Mis Cultivos - Mis Parcelas - [LOGO] - Añadir Invernadero - Añadir Parcela
    $pool_botones = array_merge([$btn_cultivos, $btn_parcelas, $btn_add_invernadero, $btn_add_parcela], $items_system);
    $total_botones = count($pool_botones);
}
// Caso Especial: "Tus Zonas Geográficas" (Localidades del Cliente)
elseif ($vista_actual === 'localidades') {
    $btn_parcelas = '<a href="dashboard.php?seccion=mis_parcelas&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">Mis Parcelas</a>';
    $btn_invernaderos = '<a href="dashboard.php?seccion=mis_invernaderos&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">Mis Invernaderos</a>';
    
    $btn_cultivos = '<a href="dashboard.php?seccion=cultivos&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">Mis Cultivos</a>';
    $btn_jornadas = '<a href="dashboard.php?seccion=jornadas_resumen' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">🕒 Jornadas</a>';
    $btn_add_parcela = '<a href="formularios/formulario_parcela.php?cliente_id=' . $cliente_id_seleccionado . $from_p . '" class="btn-sira btn-primary btn-sm">Añadir Parcela</a>';
    $btn_add_invernadero = '<a href="formularios/formulario_invernadero.php?cliente_id=' . $cliente_id . $from_p . '" class="btn-sira btn-primary btn-sm">Añadir Invernadero</a>';

    // Izquierda (3): Parcelas, Invernaderos, Cultivos | Derecha (3+): Jornadas, Add P, Add I + System
    $botones_izq = [$btn_parcelas, $btn_invernaderos, $btn_cultivos];
    $botones_der = array_merge([$btn_jornadas, $btn_add_parcela, $btn_add_invernadero], $items_system);
    $total_botones = count($botones_izq) + count($botones_der);
}
// Caso Especial: "Parcelas" (Vista dentro de una Localidad)
elseif ($vista_actual === 'parcelas') {
    $btn_parcelas = '<a href="dashboard.php?seccion=mis_parcelas&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">Mis Parcelas</a>';
    $btn_invernaderos = '<a href="dashboard.php?seccion=mis_invernaderos&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">Mis Invernaderos</a>';
    $btn_cultivos = '<a href="dashboard.php?seccion=cultivos&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">Mis Cultivos</a>';
    
    $btn_add_parcela = '<a href="formularios/formulario_parcela.php?cliente_id=' . $cliente_id_seleccionado . $from_p . '" class="btn-sira btn-primary btn-sm">Añadir Parcela</a>';
    $btn_jornadas = '<a href="dashboard.php?seccion=jornadas_resumen' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">🕒 Jornadas</a>';

    $botones_izq = [$btn_parcelas, $btn_invernaderos, $btn_cultivos];
    $botones_der = array_merge([$btn_add_parcela, $btn_jornadas], $items_system);
    $total_botones = count($botones_izq) + count($botones_der);
}
// Caso Especial: "Invernaderos" (Vista de Estructuras dentro de una Parcela)
elseif ($vista_actual === 'invernaderos') {
    $btn_parcelas = '<a href="dashboard.php?seccion=mis_parcelas&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">Mis Parcelas</a>';
    $btn_invernaderos = '<a href="dashboard.php?seccion=mis_invernaderos&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">Mis Invernaderos</a>';
    $btn_cultivos = '<a href="dashboard.php?seccion=cultivos&reset_ocultos=1' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">Mis Cultivos</a>';

    $url_add_inv = "formularios/formulario_invernadero.php?cliente_id=" . $cliente_id;
    if (isset($parc_seleccionada['parcela_id'])) {
        $url_add_inv .= '&parcela_id=' . $parc_seleccionada['parcela_id'] . '&localidad_cp=' . urlencode($loc_seleccionada['codigo_postal']);
    }
    $url_add_inv .= $from_p;
    
    $btn_add_invernadero = '<a href="' . $url_add_inv . '" class="btn-sira btn-primary btn-sm">Añadir Invernadero</a>';
    $btn_add_cultivo = '<a href="formularios/formulario_cultivo.php?cliente_id='.$cliente_id_seleccionado.$from_p.'" class="btn-sira btn-primary btn-sm">Añadir Cultivo</a>';
    $btn_jornadas = '<a href="dashboard.php?seccion=jornadas_resumen' . ($cliente_id ? '&cliente_id='.$cliente_id : '') . '" class="btn-sira btn-primary btn-sm">🕒 Jornadas</a>';

    // Izquierda (3): Parcelas, Invernaderos, Cultivos | Derecha (3+): Add I, Add C, Jornadas + System
    $botones_izq = [$btn_parcelas, $btn_invernaderos, $btn_cultivos];
    $botones_der = array_merge([$btn_add_invernadero, $btn_add_cultivo, $btn_jornadas], $items_system);
    $total_botones = count($botones_izq) + count($botones_der);
}
else {
    // 1. Mi Cuenta (RELOCATED: Now in dashboard/componentes/header.php breadcrumbs)
    
    // 2. Inicio (ELIMINADO: Ahora el logo central es el botón de inicio)
    
    // 3. Navegación, Acciones y Sistema
    $pool_botones = array_merge($pool_botones, $items_nav, $items_actions, $items_system);
}

// Dividir el pool en dos mitades solo si no se ha definido manualmente un layout especial
if (empty($botones_izq)) {
    $total_botones = count($pool_botones);
    $mitad = floor($total_botones / 2);

    $botones_izq = array_slice($pool_botones, 0, $mitad);
    $botones_der = array_slice($pool_botones, $mitad);
}
?>

<?php if ($total_botones > 0 || true): ?>
    <nav class="dashboard-navbar" id="dashboard-nav">
        <!-- Toggler para Móvil (Oculto en Desktop) -->
        <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox">

        <!-- CABECERA MÓVIL: Centrado Premium (Spacer Izq | Logo Centro | Menú Der) -->
        <div class="nav-mobile-header">
            <div class="nav-mobile-header-spacer"></div>

            <a href="<?= $base_url ?>/dashboard.php?reset_ocultos=1" class="nav-mobile-logo" title="Inicio">
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
                <a href="<?= $base_url ?>/dashboard.php?reset_ocultos=1" class="nav-symbol-anchor" title="Volver al Inicio">
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
