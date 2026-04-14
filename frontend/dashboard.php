<?php
/**
 * ===============================================================================================
 *                              SIRA - DASHBOARD ORQUESTADOR
 * ===============================================================================================
 * Este archivo centraliza la visualización de la infraestructura y gestión de usuarios.
 * Ha sido MODULARIZADO para facilitar el mantenimiento preventivo y correctivo (ASIR Architecture).
 * Piezas incluidas:
 * - dashboard/logic.php             -> API, Funciones, Sesión y Acciones.
 * - dashboard/header.php            -> Migas de pan, Título y Botones.
 * - dashboard/search_bar.php        -> Buscador PHP + SQL.
 * - dashboard/view_clients.php      -> Grid/Lista de agricultores.
 * - dashboard/view_infrastructure.php-> Localidades, Parcelas e Invernaderos.
 * - dashboard/view_infrastructure.php-> Localidades, Parcelas e Invernaderos.
 */

session_start();
require_once 'includes/config.php';
require_once 'includes/icons_helper.php';

// 1. Cargar Lógica, API y Preparación de Datos
require_once 'dashboard/logic.php';

// 2. Cargar Cabecera HTML Estándar (Meta, CSS, JWT)
$page_title = "SIRA - Panel de Control";
$page_css = "dashboard";
require_once 'includes/header.php';

// ── Error Crítico: Si la API no responde ──
if ($arbol === null): ?>
    <div class='container'>
        <div class='error-panel'>
            <h2>⚠️ Servicio Temporalmente Caído</h2>
            <p>No se pudo conectar con los servidores de SIRA. Por favor, inténtalo de nuevo en unos minutos.</p>
        </div>
    </div>
    <?php require_once 'includes/footer.php'; exit(); ?>
<?php endif; ?>

<!-- 3. Modal de Confirmación (Si aplica) -->
<?php if ($cliente_a_confirmar): ?>
    <div class="confirm-overlay">
        <div class="confirm-card">
            <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
            <h2>¿Estás seguro?</h2>
            <p>Vas a ocultar al agricultor:<br><strong><?= htmlspecialchars($cliente_a_confirmar['nombre_empresa']) ?></strong></p>
            <div class="confirm-actions">
                <a href="dashboard.php?accion=ocultar&id=<?= $cliente_a_confirmar['cliente_id'] ?>" class="confirm-btn-yes">Sí, ocultar</a>
                <a href="dashboard.php" class="confirm-btn-no">No, cancelar</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- 4. Modal de Localidades (Borrado / Bloqueo) -->
<?php if ($loc_a_borrar_target): ?>
    <div class="confirm-overlay">
        <div class="confirm-card" style="<?= count($parcelas_bloqueantes) > 0 ? 'border-color: var(--color-error); max-width: 500px;' : 'border-color: var(--color-primary);' ?>">
            
            <?php if (count($parcelas_bloqueantes) > 0): ?>
                <div style="font-size: 3rem; margin-bottom: 1rem;">🚫</div>
                <h2 style="color: var(--color-error);">No se puede eliminar</h2>
                <div style="text-align: left; margin: 1.5rem 0;">
                    <p style="margin-bottom: 1rem;">La localidad de <strong><?= htmlspecialchars($loc_a_borrar_target['municipio'] . " (" . $loc_a_borrar_target['codigo_postal'] . ")") ?></strong> no se puede borrar porque tiene asociadas las siguientes parcelas:</p>
                    <div style="max-height: 200px; overflow-y: auto; background: rgba(0,0,0,0.2); border-radius: var(--radius-container); padding: 1rem; border: 1px solid rgba(255,255,255,0.1);">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach ($parcelas_bloqueantes as $p): ?>
                                <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem;">
                                    📍 <strong><?= htmlspecialchars($p['nombre'] ?: $p['direccion']) ?></strong><br>
                                    <small style="color: var(--color-text-muted); padding-left: 1.5rem;">Catastro: <?= htmlspecialchars($p['ref_catastral']) ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="confirm-actions">
                    <a href="dashboard.php?seccion=localidades" class="btn-sira btn-primary" style="width: 100%;">Entendido</a>
                </div>
            <?php else: ?>
                <div style="font-size: 3rem; margin-bottom: 1rem;">🗑️</div>
                <h2>Eliminar Localidad</h2>
                <p>¿Estás seguro de que deseas eliminar permanentemente la localidad de <strong><?= htmlspecialchars($loc_a_borrar_target['municipio']) ?></strong>?<br><br>Esta acción no se puede deshacer.</p>
                <div class="confirm-actions">
                    <a href="dashboard.php?accion=borrar_loc&cp=<?= urlencode($loc_a_borrar_target['codigo_postal']) ?>" class="confirm-btn-yes" style="background: var(--color-error); color: white;">Sí, eliminar</a>
                    <a href="dashboard.php?seccion=localidades" class="confirm-btn-no">Cancelar</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 5. Modal de Confirmación de Parcelas (SIRA Style) -->
<?php if ($parc_a_borrar_target): ?>
    <div class="confirm-overlay">
        <div class="confirm-card" style="border-color: var(--color-error); max-width: 550px;">
            <div style="font-size: 3.5rem; margin-bottom: 1rem;">⚠️</div>
            <h2 style="color: var(--color-error);">Confirmar Eliminación</h2>
            <div style="text-align: left; margin: 1.5rem 0;">
                <p style="margin-bottom: 0.8rem;">Estás a punto de borrar permanentemente la parcela:</p>
                <div style="background: rgba(0,0,0,0.3); border-radius: var(--radius-container); padding: 1.2rem; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 1rem;">
                    <h3 style="margin: 0; color: var(--color-primary);"><?= htmlspecialchars($parc_a_borrar_target['nombre'] ?: $parc_a_borrar_target['direccion']) ?></h3>
                    <small style="color: var(--color-text-muted);"><?= htmlspecialchars($parc_a_borrar_target['direccion']) ?></small>
                </div>
                <p style="color: #ef4444; font-weight: 600; font-size: 0.95rem; line-height: 1.4;">
                    ATENCIÓN: Esta acción ELIMINARÁ todos los invernaderos, sensores y datos históricos asociados a esta finca. No se puede deshacer.
                </p>
            </div>
            <div class="confirm-actions">
                <a href="dashboard.php?accion=borrar_parc&id=<?= $parc_a_borrar_target['parcela_id'] ?>&localidad_cp=<?= urlencode($parc_a_borrar_target['codigo_postal']) ?><?= $url_query_cliente ?>" class="confirm-btn-yes" style="background: var(--color-error); color: white;">Sí, eliminar finca</a>
                <a href="dashboard.php?localidad_cp=<?= urlencode($parc_a_borrar_target['codigo_postal']) ?><?= $url_query_cliente ?>" class="confirm-btn-no">Cancelar</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($inv_a_borrar_target): ?>
    <div class="confirm-overlay">
        <div class="confirm-card" style="border-color: var(--color-error); max-width: 550px;">
            <div style="font-size: 3.5rem; margin-bottom: 1rem;">🔥</div>
            <h2 style="color: var(--color-error);">Eliminar Invernadero</h2>
            <div style="text-align: left; margin: 1.5rem 0;">
                <p style="margin-bottom: 0.8rem;">Vas a eliminar permanentemente la estructura:</p>
                <div style="background: rgba(0,0,0,0.3); border-radius: var(--radius-container); padding: 1.2rem; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 1rem;">
                    <h3 style="margin: 0; color: var(--color-primary);"><?= htmlspecialchars($inv_a_borrar_target['nombre']) ?></h3>
                    <small style="color: var(--color-text-muted);">Ubicado en: <?= htmlspecialchars($parc_seleccionada['nombre'] ?: $parc_seleccionada['direccion']) ?></small>
                </div>
                <p style="color: #ef4444; font-weight: 600; font-size: 0.95rem; line-height: 1.4;">
                    ATENCIÓN: Se perderán todos los datos históricos de sensores asociados a este invernadero. Esta acción es irrevocable.
                </p>
            </div>
            <div class="confirm-actions">
                <a href="dashboard.php?accion=borrar_inv&id=<?= $inv_a_borrar_target['invernadero_id'] ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?><?= $url_query_cliente ?>" class="confirm-btn-yes" style="background: var(--color-error); color: white;">Confirmar Eliminación</a>
                <a href="dashboard.php?parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?><?= $url_query_cliente ?>" class="confirm-btn-no">Cancelar</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- 4. Contenedor Principal de la Interfaz -->
<div class="container">
    <?php 
        // Renderizar Cabecera de Contenido (Breadcrumbs + Título + Botones)
        require_once 'dashboard/componentes/header.php';
        
        // Renderizar Buscador (Sólo si estamos en vista de agricultores)
        require_once 'dashboard/componentes/search_bar.php';
        
        // Renderizar el contenido según la vista activa
        if ($vista_actual === 'selector_cliente') {
            require_once 'dashboard/vistas/view_clients.php';
        } elseif ($vista_actual === 'gestion_localidades') {
            require_once 'dashboard/vistas/view_localidades.php';
        } elseif ($vista_actual === 'gestion_cultivos') {
            require_once 'dashboard/vistas/view_cultivos.php';
        } elseif ($vista_actual === 'gestion_parcelas_total') {
            require_once 'dashboard/vistas/view_all_parcelas.php';
        } elseif ($vista_actual === 'gestion_invernaderos_total') {
            require_once 'dashboard/vistas/view_all_invernaderos.php';
        } else {
            require_once 'dashboard/vistas/view_infrastructure.php';
        }
    ?>
</div>

<!-- 5. Pie de página -->
<?php require_once 'includes/footer.php'; ?>