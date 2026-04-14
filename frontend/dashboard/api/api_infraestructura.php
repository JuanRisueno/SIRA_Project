<?php
/**
 * api_infraestructura.php - Métodos para Localidades, Parcelas y Clientes
 */

require_once 'api_helper.php';

function obtenerJerarquia($token, $cliente_id = null) {
    $endpoint = $cliente_id ? "/api/v1/clientes/me/jerarquia?cliente_id=$cliente_id" : "/api/v1/clientes/me/jerarquia";
    $res = sira_api_call($token, $endpoint);
    return ($res['code'] == 200) ? $res['data'] : null;
}

function listarTodosLosClientes($token, $q = null) {
    $endpoint = "/api/v1/clientes/";
    if ($q) $endpoint .= "?q=" . urlencode($q);
    $res = sira_api_call($token, $endpoint);
    return ($res['code'] == 200) ? $res['data'] : [];
}

function listarTodasLasLocalidades($token, $q = "") {
    $endpoint = "/api/v1/localidades/?limit=1000";
    if ($q) $endpoint .= "&q=" . urlencode($q);
    $res = sira_api_call($token, $endpoint);
    return ($res['code'] == 200) ? $res['data'] : [];
}

function listarParcelasPorLocalidad($token, $cp) {
    $res = sira_api_call($token, "/api/v1/parcelas/localidad/" . urlencode($cp));
    return ($res['code'] == 200) ? $res['data'] : [];
}

function listarTodasLasParcelasDelCliente($token, $cliente_id) {
    $res = sira_api_call($token, "/api/v1/parcelas/cliente/" . $cliente_id);
    return ($res['code'] == 200) ? $res['data'] : [];
}

function borrarParcela($token, $id) {
    $res = sira_api_call($token, "/api/v1/parcelas/$id", "DELETE");
    return ($res['code'] == 204);
}

function borrarLocalidad($token, $cp) {
    $res = sira_api_call($token, "/api/v1/localidades/" . urlencode($cp), 'DELETE');
    if ($res['code'] == 204) {
        return ['success' => true];
    } else {
        $error_data = is_array($res['data']) ? $res['data'] : json_decode($res['data'], true);
        $msg = $error_data['detail'] ?? "Error desconocido al borrar (Código ".$res['code'].")";
        return ['success' => false, 'error' => $msg];
    }
}

function setClienteStatus($token, $cliente_id, $activa) {
    $status_str = $activa ? "true" : "false";
    $res = sira_api_call($token, "/api/v1/clientes/$cliente_id/status?activa=$status_str", 'PATCH');
    return ($res['code'] == 200);
}
