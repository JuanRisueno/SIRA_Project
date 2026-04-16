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

<!-- 4. Modal de Localidades (Borrado / Bloqueo / Consulta) -->
<?php if ($loc_a_borrar_target): ?>
    <div class="confirm-overlay">
        <?php 
            // Determinamos si debemos mostrar el panel informativo o el de borrado
            // Se muestra el panel informativo si hay parcelas O si el usuario pulsó específicamente "Explorar"
            $mostrar_informativo = $modo_consulta_loc || !empty($parcelas_bloqueantes) || ($loc_a_borrar_target['num_parcelas'] ?? 0) > 0;
        ?>
        
        <div class="confirm-card <?= $mostrar_informativo ? 'confirm-card-error' : '' ?>">
            
            <?php if ($mostrar_informativo): ?>
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">🗺️</div>
                <h2 style="color: var(--color-primary);"><?= $modo_consulta_loc ? 'Consulta de Localidad' : 'Acción Bloqueada' ?></h2>
                
                <div class="confirm-msg-box" style="text-align: left;">
                    <p style="margin-bottom: 1rem;">
                        <strong><?= htmlspecialchars($loc_a_borrar_target['municipio']) ?> (<?= htmlspecialchars($loc_a_borrar_target['codigo_postal']) ?>)</strong>
                        <?= $modo_consulta_loc ? 'tiene los siguientes activos registrados:' : 'no se puede eliminar porque existen activos vinculados:' ?>
                    </p>
                    
                    <strong style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-primary); letter-spacing: 0.05em;">Parcelas detectadas (<?= count($parcelas_bloqueantes) ?>):</strong>
                    
                    <div style="max-height: 220px; overflow-y: auto; background: rgba(0,0,0,0.2); border-radius: var(--radius-container); padding: 0.8rem; border: 1px solid rgba(255,255,255,0.1); margin-top: 0.5rem;">
                        <?php if (!empty($parcelas_bloqueantes)): ?>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach ($parcelas_bloqueantes as $p): ?>
                                    <li style="padding: 0.7rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; gap: 12px;">
                                        <div style="font-size: 1.2rem; opacity: 0.8;">🚜</div>
                                        <div style="display: flex; flex-direction: column; flex: 1;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-size: 0.7rem; background: rgba(255,255,255,0.1); color: var(--color-primary); padding: 2px 8px; border-radius: 4px; font-weight: bold; border: 1px solid rgba(255,255,255,0.1);">
                                                    ID CLI: <?= htmlspecialchars($p['cliente_id']) ?>
                                                </span>
                                                <code style="font-size: 0.85rem; opacity: 0.8; color: white;"><?= htmlspecialchars($p['ref_catastral']) ?></code>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div style="text-align: center; padding: 1.5rem; opacity: 0.6;">
                                <p>No se han podido recuperar los activos detallados.</p>
                                <small>Contacte con soporte si el error persiste.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!$modo_consulta_loc): ?>
                        <p style="margin-top: 1rem; font-size: 0.85rem; color: var(--color-text-muted);">Debes eliminar o reubicar estas parcelas antes de poder borrar la localidad.</p>
                    <?php endif; ?>
                </div>
                
                <div class="confirm-actions">
                    <a href="dashboard.php?seccion=localidades" class="btn-sira btn-primary" style="width: 100%; text-decoration: none !important;">Cerrar Consulta</a>
                </div>
            <?php else: ?>
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">🗑️</div>
                <h2>¿Eliminar Localidad?</h2>
                <p>Estás a punto de borrar permanentemente la localidad de <strong><?= htmlspecialchars($loc_a_borrar_target['municipio']) ?></strong>.<br><br>Esta acción eliminará todos los registros históricos de esta zona. <strong>¿Deseas continuar?</strong></p>
                <div class="confirm-actions">
                    <a href="dashboard.php?accion=borrar_loc&cp=<?= urlencode($loc_a_borrar_target['codigo_postal']) ?>" class="confirm-btn-yes confirm-btn-error">Sí, eliminar</a>
                    <a href="dashboard.php?seccion=localidades" class="confirm-btn-no">Cancelar</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- 5. Modal de Confirmación de Parcelas (SIRA Style) -->
<?php if ($parc_a_borrar_target): ?>
    <div class="confirm-overlay">
        <div class="confirm-card confirm-card-error">
            <div style="font-size: 3.5rem; margin-bottom: 1rem;">⚠️</div>
            <h2 class="error-title">Confirmar Eliminación</h2>
            <div style="text-align: left; margin: 1.5rem 0;">
                <p style="margin-bottom: 0.8rem;">Estás a punto de borrar permanentemente la parcela:</p>
                <div class="confirm-msg-box">
                    <h3 style="margin: 0; color: var(--color-primary);"><?= htmlspecialchars($parc_a_borrar_target['nombre'] ?: $parc_a_borrar_target['direccion']) ?></h3>
                    <small style="color: var(--color-text-muted);"><?= htmlspecialchars($parc_a_borrar_target['direccion']) ?></small>
                </div>
                <p class="error-text">
                    ATENCIÓN: Esta acción ELIMINARÁ todos los invernaderos, sensores y datos históricos asociados a esta finca. No se puede deshacer.
                </p>
            </div>
            <div class="confirm-actions">
                <a href="dashboard.php?accion=borrar_parc&id=<?= $parc_a_borrar_target['parcela_id'] ?>&localidad_cp=<?= urlencode($parc_a_borrar_target['codigo_postal']) ?><?= $url_query_cliente ?>" class="confirm-btn-yes confirm-btn-error">Sí, eliminar finca</a>
                <a href="dashboard.php?localidad_cp=<?= urlencode($parc_a_borrar_target['codigo_postal']) ?><?= $url_query_cliente ?>" class="confirm-btn-no">Cancelar</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($inv_a_borrar_target): ?>
    <div class="confirm-overlay">
        <div class="confirm-card confirm-card-error">
            <div style="font-size: 3.5rem; margin-bottom: 1rem;">🔥</div>
            <h2 class="error-title">Eliminar Invernadero</h2>
            <div style="text-align: left; margin: 1.5rem 0;">
                <p style="margin-bottom: 0.8rem;">Vas a eliminar permanentemente la estructura:</p>
                <div class="confirm-msg-box">
                    <h3 style="margin: 0; color: var(--color-primary);"><?= htmlspecialchars($inv_a_borrar_target['nombre']) ?></h3>
                    <small style="color: var(--color-text-muted);">Ubicado en: <?= htmlspecialchars($parc_seleccionada['nombre'] ?: $parc_seleccionada['direccion']) ?></small>
                </div>
                <p class="error-text">
                    ATENCIÓN: Se perderán todos los datos históricos de sensores asociados a este invernadero. Esta acción es irrevocable.
                </p>
            </div>
            <div class="confirm-actions">
                <a href="dashboard.php?accion=borrar_inv&id=<?= $inv_a_borrar_target['invernadero_id'] ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?><?= $url_query_cliente ?>" class="confirm-btn-yes confirm-btn-error">Confirmar Eliminación</a>
                <a href="dashboard.php?parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?><?= $url_query_cliente ?>" class="confirm-btn-no">Cancelar</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- 3.5 Modal de Siembra Rápida (Plantar Cultivo) -->
<?php if ($inv_a_plantar): ?>
    <div class="confirm-overlay">
        <div class="confirm-card" style="max-width: 600px; border-color: var(--color-primary);">
            <div style="font-size: 3.5rem; margin-bottom: 1rem;">🌱</div>
            <h2 style="margin-bottom: 0.5rem;">Siembra Rápida</h2>
            <p style="margin-bottom: 1.5rem;">Selecciona el cultivo para el invernadero:<br><strong><?= htmlspecialchars($inv_a_plantar['nombre']) ?></strong></p>
            
            <?php
                // Reconstruir la URL de cancelación (eliminando plant_inv_id)
                $params_cancel = $_GET;
                unset($params_cancel['plant_inv_id']);
                $url_cancel = "dashboard.php?" . http_build_query($params_cancel);
            ?>

            <form action="dashboard.php?<?= http_build_query($_GET) ?>" method="POST">
                <input type="hidden" name="invernadero_id" value="<?= $inv_a_plantar['invernadero_id'] ?>">
                
                <div class="siembra-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 12px; max-height: 320px; overflow-y: auto; padding: 15px; background: rgba(0,0,0,0.25); border-radius: var(--radius-container); border: 1px solid rgba(255,255,255,0.05); margin-bottom: 2rem; text-align: left;">
                    
                    <!-- Opción Barbecho -->
                    <label class="siembra-option">
                        <input type="radio" name="cultivo_id" value="0" <?= !$inv_a_plantar['cultivo_id'] ? 'checked' : '' ?>>
                        <div class="siembra-opt-card">
                            <span class="siembra-icon">🚜</span>
                            <span class="siembra-name">Barbecho</span>
                        </div>
                    </label>

                    <?php foreach ($lista_cultivos_siembra as $c): ?>
                        <label class="siembra-option">
                            <input type="radio" name="cultivo_id" value="<?= $c['cultivo_id'] ?>" <?= ($inv_a_plantar['cultivo_id'] == $c['cultivo_id']) ? 'checked' : '' ?>>
                            <div class="siembra-opt-card">
                                <span class="siembra-icon"><?= get_crop_icon($c['nombre_cultivo']) ?></span>
                                <span class="siembra-name"><?= mb_convert_case($c['nombre_cultivo'], MB_CASE_TITLE, "UTF-8") ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="confirm-actions">
                    <button type="submit" name="btn_quick_plant" class="confirm-btn-yes" style="width: 100%; cursor: pointer;">Confirmar Siembra</button>
                    <a href="<?= $url_cancel ?>" class="confirm-btn-no">Cancelar</a>
                </div>
            </form>
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