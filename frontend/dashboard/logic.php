<?php
/**
 * logic.php - Inteligencia del Dashboard
 * Maneja API, Sesiones, Datos y Acciones.
 */

if (!isset($_SESSION['jwt_token'])) {
    header("Location: index.php");
    exit();
}

$token = $_SESSION['jwt_token'];

// --- FUNCIONES DE APOYO (API) ---

function obtenerJerarquia($token, $cliente_id = null) {
    if ($cliente_id) {
        $url = SIRA_API_BASE . "/api/v1/clientes/me/jerarquia?cliente_id=" . $cliente_id;
    } else {
        $url = SIRA_API_BASE . "/api/v1/clientes/me/jerarquia";
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200) ? json_decode($response, true) : null;
}

function listarTodosLosClientes($token, $q = null) {
    $url = SIRA_API_BASE . "/api/v1/clientes/";
    if ($q) {
        $url .= "?q=" . urlencode($q);
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200) ? json_decode($response, true) : [];
}

function listarTodasLasLocalidades($token, $q = "") {
    $url = SIRA_API_BASE . "/api/v1/localidades/?limit=1000";
    if ($q) {
        $url .= "&q=" . urlencode($q);
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200) ? json_decode($response, true) : [];
}

function borrarParcela($token, $id) {
    $url = SIRA_API_BASE . "/api/v1/parcelas/$id";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code == 204);
}

function borrarLocalidad($token, $cp) {
    $url = SIRA_API_BASE . "/api/v1/localidades/" . urlencode($cp);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 204) {
        return ['success' => true];
    } else {
        $error_data = json_decode($response, true);
        $msg = $error_data['detail'] ?? "Error desconocido al borrar (Código $http_code)";
        return ['success' => false, 'error' => $msg];
    }
}

function listarParcelasPorLocalidad($token, $cp) {
    $url = SIRA_API_BASE . "/api/v1/parcelas/localidad/" . urlencode($cp);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200) ? json_decode($response, true) : [];
}

function setClienteStatus($token, $cliente_id, $activa) {
    $status_str = $activa ? "true" : "false";
    $url = SIRA_API_BASE . "/api/v1/clientes/$cliente_id/status?activa=$status_str";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 200);
}

function borrarInvernadero($token, $id) {
    $url = SIRA_API_BASE . "/api/v1/invernaderos/$id";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code == 204);
}

// --- MANEJADOR DE ACCIONES PHP ---

if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_user = (int) $_GET['id'];
    if ($_GET['accion'] === 'ocultar') {
        setClienteStatus($token, $id_user, false);
    } elseif ($_GET['accion'] === 'activar') {
        setClienteStatus($token, $id_user, true);
    }
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['accion']) && $_GET['accion'] === 'borrar_loc' && isset($_GET['cp'])) {
    $res = borrarLocalidad($token, $_GET['cp']);
    if ($res['success']) {
        header("Location: dashboard.php?seccion=localidades&msg=borrado_ok");
    } else {
        // Si hay error, volvemos a la gestión de localidades con el mensaje de error
        header("Location: dashboard.php?seccion=localidades&error=" . urlencode($res['error']));
    }
    exit();
}

if (isset($_GET['accion']) && $_GET['accion'] === 'borrar_parc' && isset($_GET['id'])) {
    if (borrarParcela($token, $_GET['id'])) {
        $redir = "dashboard.php?msg=parcela_borrada";
        if (isset($_GET['localidad_cp'])) $redir .= "&localidad_cp=" . $_GET['localidad_cp'];
        if (isset($_GET['cliente_id'])) $redir .= "&cliente_id=" . $_GET['cliente_id'];
        header("Location: $redir");
    } else {
        header("Location: dashboard.php?error=borrado_fallido");
    }
    exit();
}

if (isset($_GET['accion']) && $_GET['accion'] === 'borrar_inv' && isset($_GET['id'])) {
    if (borrarInvernadero($token, $_GET['id'])) {
        $redir = "dashboard.php?msg=invernadero_borrado";
        if (isset($_GET['localidad_cp'])) $redir .= "&localidad_cp=" . $_GET['localidad_cp'];
        if (isset($_GET['parcela_id'])) $redir .= "&parcela_id=" . $_GET['parcela_id'];
        if (isset($_GET['cliente_id'])) $redir .= "&cliente_id=" . $_GET['cliente_id'];
        header("Location: $redir");
    } else {
        header("Location: dashboard.php?error=borrado_inv_fallido");
    }
    exit();
}

if (isset($_GET['toggle_view'])) {
    $current_view = $_SESSION['dashboard_view'] ?? 'grid';
    $_SESSION['dashboard_view'] = ($current_view === 'grid') ? 'list' : 'grid';
    $redirect_url = 'dashboard.php';
    if (isset($_GET['cliente_id'])) $redirect_url .= '?cliente_id=' . $_GET['cliente_id'];
    header("Location: " . $redirect_url);
    exit();
}

if (isset($_GET['toggle_ocultos'])) {
    $_SESSION['ver_ocultos'] = !($_SESSION['ver_ocultos'] ?? false);
    header("Location: dashboard.php");
    exit();
}

// --- PREPARACIÓN DE DATOS ---

$es_admin = isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'], ['admin', 'root']);
$user_rol = $_SESSION['user_rol'] ?? 'cliente';
$cliente_id_seleccionado = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : null;
$vista_grid_activa = ($_SESSION['dashboard_view'] ?? 'grid') === 'grid';
$url_query_cliente = $cliente_id_seleccionado ? "&cliente_id=$cliente_id_seleccionado" : "";
$cliente_a_confirmar = null;
$loc_a_borrar_target = null;
$parcelas_bloqueantes = [];

// [NUEVO] Lógica para detectar confirmación de borrado de localidad
if (isset($_GET['confirmar_borrar_loc']) && isset($_GET['cp'])) {
    $cp_target = $_GET['cp'];
    
    // 1. Obtener datos de la localidad (municipio/provincia)
    $url_loc = SIRA_API_BASE . "/api/v1/localidades/" . urlencode($cp_target);
    $ch = curl_init($url_loc);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $res_loc = curl_exec($ch);
    $code_loc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code_loc == 200) {
        $loc_a_borrar_target = json_decode($res_loc, true);
        // 2. Obtener parcelas asociadas
        $parcelas_bloqueantes = listarParcelasPorLocalidad($token, $cp_target);
    }
}

// [NUEVO] Lógica para detectar confirmación de borrado de parcela
$parc_a_borrar_target = null;
if (isset($_GET['confirmar_borrar_parc']) && isset($_GET['id'])) {
    $parc_id_target = (int)$_GET['id'];
    $url_parc = SIRA_API_BASE . "/api/v1/parcelas/" . $parc_id_target;
    $ch = curl_init($url_parc);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $res_parc = curl_exec($ch);
    $code_parc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code_parc == 200) {
        $parc_a_borrar_target = json_decode($res_parc, true);
    }
}

// [NUEVO] Lógica para detectar confirmación de borrado de invernadero
$inv_a_borrar_target = null;
if (isset($_GET['confirmar_borrar_inv']) && isset($_GET['id'])) {
    $inv_id_target = (int)$_GET['id'];
    $url_inv = SIRA_API_BASE . "/api/v1/invernaderos/" . $inv_id_target;
    $ch = curl_init($url_inv);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    $res_inv = curl_exec($ch);
    $code_inv = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code_inv == 200) {
        $inv_a_borrar_target = json_decode($res_inv, true);
    }
}

// [CORRECCIÓN] Si no es admin, entramos directo a localidades. Si es admin, depende de si eligió cliente.
if (!$es_admin || $cliente_id_seleccionado) {
    $vista_actual = 'localidades';
    $arbol = obtenerJerarquia($token, $cliente_id_seleccionado);
} else {
    $vista_actual = 'selector_cliente';
    $todos_los_clientes = listarTodosLosClientes($token, $busqueda);
    $arbol = ['nombre_empresa' => ($_SESSION['user_rol'] === 'root') ? 'Súper Panel (Root)' : 'Panel de Gestión (Admin)'];
    
    // Si estamos en proceso de confirmar ocultación, buscamos el nombre
    if (isset($_GET['confirmar_ocultar']) && isset($_GET['id'])) {
        foreach ($todos_los_clientes as $c) {
            if ($c['cliente_id'] == (int) $_GET['id']) {
                $cliente_a_confirmar = $c;
                break;
            }
        }
    }

    // [NUEVO] Sección de Gestión de Localidades
    if (isset($_GET['seccion']) && $_GET['seccion'] === 'localidades') {
        $vista_actual = 'gestion_localidades';
        $todas_las_localidades = listarTodasLasLocalidades($token, $busqueda);
        $arbol = ['nombre_empresa' => 'Gestión de Localidades'];
    }
}

// ── Lógica de navegación y saltos inteligentes ──
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

// if ($vista_actual === 'parcelas' && count($parcelas_data) === 1 && !isset($_GET['parcela_id'])) {
//     $_GET['parcela_id'] = $parcelas_data[0]['parcela_id'];
// }

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
