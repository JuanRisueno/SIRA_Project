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
require_once 'gestores/gestor_dashboard.php';

// 2. Preparación de variables de estado base
$es_admin = isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'], ['admin', 'root']);
$user_rol = $_SESSION['user_rol'] ?? 'cliente';
$cliente_id_seleccionado = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : ( (!$es_admin) ? ($_SESSION['cliente_id'] ?? null) : null );
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : null;
$vista_grid_activa = ($_SESSION['dashboard_view'] ?? 'grid') === 'grid';
$url_query_cliente = $cliente_id_seleccionado ? "&cliente_id=$cliente_id_seleccionado" : "";

$cliente_a_confirmar = null;
$loc_a_borrar_target = null;
$parcelas_bloqueantes = [];

// 3. Modales de confirmación (Peticiones GET de confirmación)
if (isset($_GET['confirmar_borrar_loc']) && isset($_GET['cp'])) {
    $cp_target = $_GET['cp'];
    $url_loc = SIRA_API_BASE . "/api/v1/localidades/" . urlencode($cp_target);
    $ch = curl_init($url_loc);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $res_loc = curl_exec($ch);
    $code_loc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code_loc == 200) {
        $loc_a_borrar_target = json_decode($res_loc, true);
        $parcelas_bloqueantes = listarParcelasPorLocalidad($token, $cp_target);
    }
}

$parc_a_borrar_target = null;
if (isset($_GET['confirmar_borrar_parc']) && isset($_GET['id'])) {
    $parc_a_borrar_target = obtenerDetalleAsset($token, true, $_GET['id']);
}

$inv_a_borrar_target = null;
if (isset($_GET['confirmar_borrar_inv']) && isset($_GET['id'])) {
    $inv_a_borrar_target = obtenerDetalleAsset($token, false, $_GET['id']);
}

// 4. Lógica de Selección de Vista y Datos
if (!$es_admin || $cliente_id_seleccionado) {
    $vista_actual = 'localidades';
    $arbol = obtenerJerarquia($token, $cliente_id_seleccionado);
} else {
    $vista_actual = 'selector_cliente';
    $todos_los_clientes = listarTodosLosClientes($token, $busqueda);
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
            $arbol = ['nombre_empresa' => 'Catálogo de Cultivos'];
            break;
        case 'mis_parcelas':
            $vista_actual = 'gestion_parcelas_total';
            $todas_las_parcelas = listarTodasLasParcelasDelCliente($token, $cliente_id_seleccionado);
            $arbol = ['nombre_empresa' => 'Listado Maestro de Parcelas'];
            break;
        case 'mis_invernaderos':
            $vista_actual = 'gestion_invernaderos_total';
            $todos_los_invernaderos = listarTodosLosInvernaderosDelCliente($token, $cliente_id_seleccionado);
            $arbol = ['nombre_empresa' => 'Listado Maestro de Invernaderos'];
            break;
        case 'localidades':
            if ($es_admin) {
                $vista_actual = 'gestion_localidades';
                $todas_las_localidades = listarTodasLasLocalidades($token, $busqueda);
                $arbol = ['nombre_empresa' => 'Gestión de Localidades'];
            }
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
$lista_cultivos_siembra = [];
if (isset($_GET['plant_inv_id'])) {
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
