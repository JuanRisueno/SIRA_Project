<?php
/**
 * dashboard_handlers.php - Procesamiento de Acciones GET/POST del Dashboard
 */

// 1. Manejador de Status de Cliente (Admin)
if (isset($_GET['accion']) && isset($_GET['id']) && in_array($_GET['accion'], ['ocultar', 'activar'])) {
    $id_user = (int) $_GET['id'];
    $nuevo_status = ($_GET['accion'] === 'activar');
    setClienteStatus($token, $id_user, $nuevo_status);
    header("Location: dashboard.php");
    exit();
}

// 2. Manejador de Borrado de Localidad (Admin)
if (isset($_GET['accion']) && $_GET['accion'] === 'borrar_loc' && isset($_GET['cp'])) {
    $res = borrarLocalidad($token, $_GET['cp']);
    if ($res['success']) {
        header("Location: dashboard.php?seccion=localidades&msg=borrado_ok");
    } else {
        header("Location: dashboard.php?seccion=localidades&error=" . urlencode($res['error']));
    }
    exit();
}

// 3. Manejador de Status de Cultivo
if (isset($_GET['accion']) && $_GET['accion'] === 'status_cultivo' && isset($_GET['id'])) {
    $activa = ($_GET['estado'] === 'activar');
    setCultivoStatus($token, $_GET['id'], $activa);
    header("Location: dashboard.php?seccion=cultivos&highlight_id=" . $_GET['id'] . "#cultivo-card-" . $_GET['id']);
    exit();
}

// 4. Manejador de Borrado de Parcelas
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

// 5. Manejador de Borrado de Invernaderos
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

// 6. Manejador de Edición Rápida (Zero JS Rename)
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['btn_quick_rename_parc']) || isset($_POST['btn_quick_rename_inv']))) {
    $is_parc = isset($_POST['btn_quick_rename_parc']);
    $id = (int)($is_parc ? $_POST['parcela_id'] : $_POST['invernadero_id']);
    $nuevo_nombre = trim($_POST['nuevo_nombre']);

    if ($nuevo_nombre !== "") {
        $data = obtenerDetalleAsset($token, $is_parc, $id);
        if ($data) {
            $data['nombre'] = $nuevo_nombre;
            actualizarAsset($token, $is_parc, $id, $data);
        }
    }

    $retorno = $is_parc ? "mis_parcelas" : "mis_invernaderos";
    $query_cliente = isset($_GET['cliente_id']) ? "&cliente_id=" . (int)$_GET['cliente_id'] : "";
    
    // Ancla para evitar perder el foco (UX V9.0)
    $anchor = $is_parc ? "#parcela-card-$id" : "#inv-card-$id";
    header("Location: dashboard.php?seccion=$retorno&msg=nombre_actualizado&highlight_id=$id" . $query_cliente . $anchor);
    exit();
}

// 7. Manejador de Siembra Rápida (Asignación de Cultivo)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_quick_plant'])) {
    $inv_id = (int)$_POST['invernadero_id'];
    $cultivo_id = (int)$_POST['cultivo_id'];
    
    // 1. Obtener datos actuales del invernadero
    $data = obtenerDetalleAsset($token, false, $inv_id);
    if ($data) {
        // 2. Actualizar el cultivo y la fecha de plantación (hoy)
        if ($cultivo_id > 0) {
            $data['cultivo_id'] = $cultivo_id;
            $data['fecha_plantacion'] = date('Y-m-d'); // La fecha de inicio es hoy
        } else {
            $data['cultivo_id'] = null;
            $data['fecha_plantacion'] = null; // Barbecho
        }
        actualizarAsset($token, false, $inv_id, $data);
    }

    $query_cliente = isset($_GET['cliente_id']) ? "&cliente_id=" . (int)$_GET['cliente_id'] : "";
    $parc_query = isset($_GET['parcela_id']) ? "&parcela_id=" . (int)$_GET['parcela_id'] : "";
    $loc_query = isset($_GET['localidad_cp']) ? "&localidad_cp=" . urlencode($_GET['localidad_cp']) : "";
    
    $seccion = (isset($_GET['seccion']) && $_GET['seccion'] === 'mis_invernaderos') ? "mis_invernaderos" : "";
    $url = "dashboard.php?msg=siembra_actualizada";
    if ($seccion) $url .= "&seccion=$seccion";
    
    // Redirigir con Ancla y Highlight
    header("Location: " . $url . $query_cliente . $parc_query . $loc_query . "&highlight_id=$inv_id#inv-card-$inv_id");
    exit();
}

// 8. Manejadores de Estado de Interfaz (Toggles)
if (isset($_GET['toggle_view']) || isset($_GET['toggle_ocultos'])) {
    if (isset($_GET['toggle_view'])) {
        $current_view = $_SESSION['dashboard_view'] ?? 'grid';
        $_SESSION['dashboard_view'] = ($current_view === 'grid') ? 'list' : 'grid';
    }
    
    if (isset($_GET['toggle_ocultos'])) {
        $_SESSION['ver_ocultos'] = !($_SESSION['ver_ocultos'] ?? false);
    }
    
    // Limpiar los parámetros de control para la redirección
    $params = $_GET;
    unset($params['toggle_view'], $params['toggle_ocultos']);
    
    $query = !empty($params) ? "?" . http_build_query($params) : "";
    header("Location: dashboard.php" . $query);
    exit();
}
