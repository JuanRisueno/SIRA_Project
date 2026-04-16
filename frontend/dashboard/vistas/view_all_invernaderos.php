<?php
/**
 * view_all_invernaderos.php - Listado maestro de invernaderos (SIRA Standard V12.0)
 * Refactorizado para usar el sistema nativo de tarjetas premium.
 */
?>

<div class="infra-grid-container">
    
    <?php if (empty($todos_los_invernaderos)): ?>
        <div class="user-form-container card" style="text-align: center; padding: 4rem; grid-column: 1 / -1;">
            <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">🏡</div>
            <p>No tienes invernaderos registrados aún en tu infraestructura.</p>
        </div>
    <?php else: ?>
        <?php foreach ($todos_los_invernaderos as $inv): 
            $is_target = (isset($_GET['plant_inv_id']) && $_GET['plant_inv_id'] == $inv['invernadero_id']) || (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $inv['invernadero_id']);
            $puede_editar_inv = ($es_admin || $user_rol === 'cliente');
        ?>
            <div id="inv-card-<?= $inv['invernadero_id'] ?>" 
                 class="inv-smart-card <?= $is_target ? 'highlight-glow' : '' ?>">
                
                <!-- NIVEL 1: CABECERA DE CONTROL (Identidad + Live IoT) -->
                <div class="card-nivel-header">
                    <div class="card-title-group">
                        <h3 title="<?= htmlspecialchars($inv['nombre']) ?>">
                            <?= mb_convert_case($inv['nombre'], MB_CASE_TITLE, "UTF-8") ?>
                        </h3>
                        <div class="card-subtitle">
                            <span>#<?= $inv['invernadero_id'] ?></span>
                            <span style="opacity: 0.3;">|</span>
                            <span>📍 <?= mb_convert_case($inv['parcela']['localidad']['municipio'] ?? 'S/L', MB_CASE_TITLE, "UTF-8") ?></span>
                        </div>
                    </div>

                    <div class="status-live-container" title="Sincronización en tiempo real activa">
                        <span class="status-pulse-dot"></span>
                        <span class="badge-text-premium">LIVE</span>
                    </div>
                </div>

                <!-- NIVEL 2: CORAZÓN TÉCNICO -->
                <div class="card-nivel-tecnico">
                    <!-- BLOQUE IZQUIERDO: Identidad del Cultivo -->
                    <div class="tecnico-bloque-identidad">
                        <div class="tecnico-avatar-icon">
                            <?= get_crop_icon($inv['cultivo']['nombre_cultivo'] ?? null) ?: '🏡' ?>
                        </div>
                        <div class="tecnico-datos-group">
                            <span class="tecnico-label">Producción Actual</span>
                            <strong class="tecnico-valor-main"><?= $inv['cultivo'] ? mb_convert_case($inv['cultivo']['nombre_cultivo'], MB_CASE_TITLE, "UTF-8") : 'En Barbecho' ?></strong>
                        </div>
                    </div>

                    <!-- BLOQUE DERECHO: Especificaciones -->
                    <div class="tecnico-datos-derecha">
                        <div class="tecnico-item-mini">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span class="tecnico-label" style="opacity: 0.7;">Dimensiones</span>
                                <span style="font-size: 0.85rem; font-weight: 800; color: var(--color-text-main); white-space: nowrap;">
                                    <?= (float)$inv['largo_m'] ?>m × <?= (float)$inv['ancho_m'] ?>m
                                </span>
                                <small style="font-size: 0.65rem; opacity: 0.4; font-weight: bold;"><?= (int)($inv['largo_m'] * $inv['ancho_m']) ?> m² totales</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 3: ACCIONES ESTÁNDAR SIRA -->
                <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $url_query_cliente ?>" 
                   class="stretched-link"></a>

                <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 10;">
                    <div style="display: flex; gap: 8px;">
                        <?php if ($puede_editar_inv): ?>
                            <a href="management/edit_invernadero.php?id=<?= $inv['invernadero_id'] ?>&from=lista" class="btn-sira btn-secondary" style="padding: 6px 14px; font-size: 0.75rem;" title="Ajustes de infraestructura">
                                ⚙️ <span>Editar</span>
                            </a>
                        <?php endif; ?>
                        
                        <?php 
                            $query_params = "dashboard.php?plant_inv_id=" . $inv['invernadero_id'] . $url_query_cliente;
                            if (isset($_GET['seccion'])) $query_params .= "&seccion=" . $_GET['seccion'];
                        ?>
                        <a href="<?= $query_params ?>#inv-card-<?= $inv['invernadero_id'] ?>" 
                           class="btn-sira btn-secondary" style="padding: 6px 14px; font-size: 0.75rem;" title="Cambiar o plantar cultivo">
                            🌱 <span>Plantar</span>
                        </a>
                    </div>
                    
                    <div style="text-align: right;">
                        <span class="list-subtitle" style="font-size: 0.7rem; opacity: 0.5;">PANEL DE SENSORES ➜</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
