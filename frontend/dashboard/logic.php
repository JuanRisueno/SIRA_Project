<?php
/**
 * logic.php - Orquestador del Dashboard (V7.1 Modular)
 * Gestiona el ruteo de vistas y preparación de datos.
 */

if (!isset($_SESSION['jwt_token'])) {
    header("Location: index.php");
    exit();
}

// 1. Inclusión de módulos
$token = $_SESSION['jwt_token'];
require_once 'api/api_infraestructura.php';
require_once 'api/api_produccion.php';
require_once 'api/api_sistema.php'; // Nueva API para configuración global

// 2. Preparación de variables de estado base
$es_admin = isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'], ['admin', 'root']);
$user_rol = $_SESSION['user_rol'] ?? 'cliente';
$cliente_id_seleccionado = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : ( (!$es_admin) ? ($_SESSION['cliente_id'] ?? null) : null );
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : null;
$vista_grid_activa = ($_SESSION['dashboard_view'] ?? 'grid') === 'grid';
$url_query_cliente = $cliente_id_seleccionado ? "&cliente_id=$cliente_id_seleccionado" : "";

// 3. Procesamiento de Acciones (Handlers) - ¡IMPORTANTE!: Después de definir $es_admin
require_once 'gestores/gestor_dashboard.php';
$titulo_seccion = null;

// [GLOBAL] Cargar Configuración de Redes Sociales
$res_social = sira_api_call($token, "/api/v1/sistema/social");
$config_social = ($res_social['code'] == 200) ? $res_social['data'] : null;
if (!$config_social) {
    // Valores por defecto si la API falla
    $config_social = [
        "twitter" => "", "instagram" => "", "facebook" => "", 
        "whatsapp" => "", "email_soporte" => "sira@sira.es"
    ];
}
$modo_edicion_social = isset($_GET['edit_social']) && $es_admin;

// [V13.0] Reseteo de visibilidad al navegar por el menú principal (Solicitado por User)
if (isset($_GET['reset_ocultos'])) {
    $_SESSION['ver_ocultos'] = false;
}

$cliente_a_confirmar = null;
$loc_detalle_target = null;
$parcelas_bloqueantes = [];
$modo_consulta_loc = false;

// 3. Modales de información (Consulta de Localidad)
if (isset($_GET['ver_detalle_loc']) && isset($_GET['cp'])) {
    $cp_target = $_GET['cp'];
    
    // Obtenemos el detalle básico de la localidad
    $res_loc = sira_api_call($token, "/api/v1/localidades/" . urlencode($cp_target));
    if ($res_loc['code'] == 200) {
        $loc_detalle_target = $res_loc['data'];
        // Obtenemos las parcelas para mostrar en el modal
        $parcelas_bloqueantes = listarParcelasPorLocalidad($token, $cp_target);
    }
}

// 3. Modales de confirmación (Peticiones GET de confirmación)

$parc_a_borrar_target = null;
$es_ultima_parcela = false;
if (isset($_GET['confirmar_borrar_parc']) && isset($_GET['id'])) {
    $parc_a_borrar_target = obtenerDetalleAsset($token, true, $_GET['id']);
    
    // Si la parcela existe, miramos cuántas parcelas hay en esa localidad para ese cliente
    if ($parc_a_borrar_target) {
        $cp_parc = $parc_a_borrar_target['codigo_postal'];
        $cli_parc = $parc_a_borrar_target['cliente_id'];
        
        $res_lista = listarParcelasPorLocalidad($token, $cp_parc); // Esta función ya la usamos en el modal de borrado de loc
    }
}

$inv_a_borrar_target = null;
if (isset($_GET['confirmar_borrar_inv']) && isset($_GET['id'])) {
    $inv_a_borrar_target = obtenerDetalleAsset($token, false, $_GET['id']);
}

$inv_a_restaurar_jerarquico = null;
if (isset($_GET['confirmar_restaurar_inv_jerarquico']) && isset($_GET['id'])) {
    $inv_a_restaurar_jerarquico = obtenerDetalleAsset($token, false, $_GET['id']);
}

// 4. Lógica de Selección de Vista y Datos
if (!$es_admin || $cliente_id_seleccionado) {
    $vista_actual = 'localidades';
    $arbol = obtenerJerarquia($token, $cliente_id_seleccionado);
} else {
    $vista_actual = 'selector_cliente';
    $todos_los_clientes = listarTodosLosClientes($token, $busqueda);
    
    // FILTRO DE SEGURIDAD: Un Admin solo ve Clientes. Solo el Root ve a otros Admins.
    if ($user_rol === 'admin') {
        $todos_los_clientes = array_filter($todos_los_clientes, function($c) {
            return $c['rol'] === 'cliente';
        });
        // Re-indexar el array para evitar huecos en el for/foreach si fuera necesario
        $todos_los_clientes = array_values($todos_los_clientes);
    }

    $arbol = ['nombre_empresa' => ($_SESSION['user_rol'] === 'root') ? 'Súper Panel (Root)' : 'Panel de Gestión (Admin)'];
    
    if (isset($_GET['confirmar_ocultar']) && isset($_GET['id'])) {
        foreach ($todos_los_clientes as $c) {
            if ($c['cliente_id'] == (int) $_GET['id']) {
                $cliente_a_confirmar = $c;
                break;
            }
        }
    }
}

// Secciones Especiales (Master Lists / Gestión)
if (isset($_GET['seccion'])) {
    switch ($_GET['seccion']) {
        case 'cultivos':
            $vista_actual = 'gestion_cultivos';
            $todos_los_cultivos = listarTodosLosCultivos($token, $busqueda, $es_admin);
            $titulo_seccion = 'Catálogo de Cultivos';
            break;
        case 'mis_parcelas':
            $vista_actual = 'gestion_parcelas_total';
            $parc_raw = listarTodasLasParcelasDelCliente($token, $cliente_id_seleccionado);
            
            // FILTRADO EXCLUSIVO (V12.5)
            $ver_ocultos = $_SESSION['ver_ocultos'] ?? false;
            $todas_las_parcelas = array_filter($parc_raw, function($p) use ($es_admin, $ver_ocultos) {
                $is_active = (bool)($p['activa'] ?? true);
                if ($es_admin && $ver_ocultos) return !$is_active; // Modo papelera: solo las archivadas
                return $is_active; // Modo normal: solo las activas
            });
            $titulo_seccion = 'Listado Maestro de Parcelas';
            break;
        case 'mis_invernaderos':
            $vista_actual = 'gestion_invernaderos_total';
            $inv_raw = listarTodosLosInvernaderosDelCliente($token, $cliente_id_seleccionado);
            
            // FILTRADO JERÁRQUICO EXCLUSIVO (V12.5)
            $ver_ocultos = $_SESSION['ver_ocultos'] ?? false;
            $todos_los_invernaderos = array_filter($inv_raw, function($inv) use ($es_admin, $ver_ocultos) {
                $inv_ok = (bool)($inv['activa'] ?? true);
                $parc_ok = (bool)($inv['parcela']['activa'] ?? true);
                $is_fully_active = ($inv_ok && $parc_ok);

                if ($es_admin && $ver_ocultos) return !$is_fully_active; // Modo papelera: mostrar si el invernadero O la parcela están archivados
                return $is_fully_active; // Modo normal: solo si ambos están activos
            });
            $titulo_seccion = 'Listado Maestro de Invernaderos';
            break;
        case 'localidades':
            if ($es_admin) {
                $vista_actual = 'gestion_localidades';
                $todas_las_localidades = listarTodasLasLocalidades($token, $busqueda);
                $titulo_seccion = 'Gestión de Localidades';
            }
            break;
        case 'jornadas_resumen':
            $vista_actual = 'jornadas_resumen';
            $res_jornadas = sira_api_call($token, "/api/v1/config/jornada/cliente/" . $cliente_id_seleccionado . "/resumen");
            $resumen_jornadas = ($res_jornadas['code'] == 200) ? $res_jornadas['data'] : [];
            $titulo_seccion = 'Resumen de Jornadas Laborales';
            break;
    }
}

// 5. Lógica de navegación y saltos inteligentes
$localidades_data = $arbol['localidades'] ?? [];
$parcelas_data = [];
$invernaderos_data = [];
$loc_seleccionada = null;
$parc_seleccionada = null;

if ($vista_actual === 'localidades' && count($localidades_data) === 1 && !isset($_GET['localidad_cp'])) {
    $_GET['localidad_cp'] = $localidades_data[0]['codigo_postal'];
}

if (isset($_GET['localidad_cp'])) {
    $cp = $_GET['localidad_cp'];
    foreach ($localidades_data as $loc) {
        if ($loc['codigo_postal'] === $cp) {
            $loc_seleccionada = $loc;
            $parcelas_data = $loc['parcelas'];
            $vista_actual = 'parcelas';
            break;
        }
    }
}

// 6. Carga Extra: Lista de cultivos para el selector de 'Siembra'
$inv_a_plantar = null;
$lista_cultivos_siembra = [];
if (isset($_GET['plant_inv_id'])) {
    $inv_a_plantar = obtenerDetalleAsset($token, false, $_GET['plant_inv_id']);
    $lista_cultivos_siembra = listarTodosLosCultivos($token, null, false);
}

if (isset($_GET['parcela_id'])) {
    $p_id = (int) $_GET['parcela_id'];
    foreach ($parcelas_data as $parc) {
        if ($parc['parcela_id'] === $p_id) {
            $parc_seleccionada = $parc;
            $invernaderos_data = $parc['invernaderos'];
            $vista_actual = 'invernaderos';
            break;
        }
    }
}

// [NUEVO] Precarga de mapa de jornadas para iconos dinámicos
$jornadas_map = [];
if (in_array($vista_actual, ['invernaderos', 'gestion_invernaderos_total', 'jornadas_resumen']) && $cliente_id_seleccionado) {
    $res_jornadas_api = sira_api_call($token, "/api/v1/config/jornada/cliente/" . $cliente_id_seleccionado . "/resumen");
    if ($res_jornadas_api['code'] == 200) {
        foreach ($res_jornadas_api['data'] as $j) {
            $jornadas_map[$j['invernadero_id']] = $j;
        }
    }
}

/**
 * restaurarInvernaderosEnCascada - Activa todos los invernaderos de una parcela vía API.
 */
function restaurarInvernaderosEnCascada($token, $parcela_id) {
    try {
        $url = API_BASE_URL . "/infraestructura/parcelas/$parcela_id/restaurar_invernaderos";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($status === 204 || $status === 200);
    } catch (Exception $e) {
        return false;
    }
}
