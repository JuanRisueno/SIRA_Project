<?php
/**
 * view_all_invernaderos.php - Listado maestro de invernaderos (V6.5 - IoT Center)
 */
?>

<div class="infra-grid-container inv-cards-container">
    
    <?php if (empty($todos_los_invernaderos)): ?>
        <div class="user-form-container card" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            <p>No tienes invernaderos registrados aún en tu infraestructura.</p>
        </div>
    <?php else: ?>
        <?php foreach ($todos_los_invernaderos as $inv): 
            $is_target = (isset($_GET['plant_inv_id']) && $_GET['plant_inv_id'] == $inv['invernadero_id']) || (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $inv['invernadero_id']);
        ?>
            <div id="inv-card-<?= $inv['invernadero_id'] ?>" 
                 class="inv-smart-card <?= $is_target ? 'highlight-glow' : '' ?>" 
                 style="background: var(--color-bg-card); border: 1px solid <?= $is_target ? 'var(--color-primary)' : 'var(--border-color)' ?>; border-radius: var(--radius-container); padding: 1.5rem; position: relative; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; gap: 1.2rem;">
                
                <!-- NIVEL 1: CABECERA DE CONTROL (Identidad + Live IoT) -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <?php 
                        $edit_inv_id = isset($_GET['edit_inv_id']) ? (int)$_GET['edit_inv_id'] : null;
                        if ($edit_inv_id === (int)$inv['invernadero_id']): 
                        ?>
                            <form method="POST" style="display: flex; gap: 8px; align-items: center;">
                                <input type="hidden" name="invernadero_id" value="<?= $inv['invernadero_id'] ?>">
                                <input type="text" name="nuevo_nombre" value="<?= htmlspecialchars($inv['nombre']) ?>" 
                                       style="font-size: 1.25rem; color: var(--color-primary); background: rgba(16, 185, 129, 0.1); border: 1px solid var(--color-primary); padding: 4px 12px; border-radius: var(--radius-container); font-weight: 800; width: auto;" 
                                       autofocus>
                                <button type="submit" name="btn_quick_rename_inv" value="1" style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">✅</button>
                            </form>
                        <?php else: ?>
                            <a href="dashboard.php?seccion=mis_invernaderos&edit_inv_id=<?= $inv['invernadero_id'] ?><?= $url_query_cliente ?>" 
                               class="inv-name-link" style="text-decoration: none;">
                                <h3 style="margin: 0; color: var(--color-primary); font-size: 1.5rem; letter-spacing: -0.03em; font-weight: 800;"><?= mb_convert_case($inv['nombre'], MB_CASE_TITLE, "UTF-8") ?></h3>
                            </a>
                        <?php endif; ?>
                        
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.7rem; color: var(--color-text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8;">
                            <span>#<?= $inv['invernadero_id'] ?></span>
                            <span style="opacity: 0.3;">|</span>
                            <span>📍 <?= mb_convert_case($inv['parcela']['localidad']['municipio'] ?? 'S/L', MB_CASE_TITLE, "UTF-8") ?></span>
                        </div>
                    </div>

                    <div class="status-live-container" title="Sincronización en tiempo real activa">
                        <span class="status-pulse-dot"></span>
                        <span style="font-size: 0.65rem; font-weight: 900; color: var(--color-primary); letter-spacing: 0.1em;">LIVE</span>
                    </div>
                </div>

                <!-- NIVEL 2: CORAZÓN TÉCNICO (Distribución Balanceada) -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: rgba(255,255,255,0.02); padding: 1.2rem; border-radius: var(--radius-container); border: 1px solid rgba(255,255,255,0.03);">
                    
                    <!-- BLOQUE IZQUIERDO: Identidad del Cultivo -->
                    <div style="display: flex; align-items: center; gap: 1.2rem; flex: 1; min-width: 0;">
                        <!-- Avatar Dinámico -->
                        <div style="width: 66px; height: 66px; background: rgba(16, 185, 129, 0.08); border-radius: var(--radius-container); display: flex; align-items: center; justify-content: center; font-size: 2.6rem; border: 1px solid rgba(16, 185, 129, 0.1); flex-shrink: 0; transition: transform 0.3s;" class="inv-avatar-icon">
                            <?= get_crop_icon($inv['cultivo']['nombre_cultivo'] ?? null) ?: '🏡' ?>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 2px; overflow: hidden;">
                            <span style="font-size: 0.6rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Producción Actual</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <?php if ($inv['cultivo']): ?>
                                    <strong style="font-size: 1.15rem; color: var(--color-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= mb_convert_case($inv['cultivo']['nombre_cultivo'], MB_CASE_TITLE, "UTF-8") ?></strong>
                                <?php else: ?>
                                    <strong style="font-size: 1.15rem; color: var(--color-text-muted); font-style: italic;">En Barbecho</strong>
                                <?php endif; ?>
                            </div>
                            <?php if ($inv['fecha_plantacion']): ?>
                                <span style="font-size: 0.65rem; color: var(--color-primary); font-weight: 700; opacity: 0.8;">📅 Ciclo: <?= date('d/m/Y', strtotime($inv['fecha_plantacion'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- BLOQUE DERECHO: Especificaciones Técnicas -->
                    <div style="display: flex; flex-direction: column; gap: 8px; text-align: right; flex-shrink: 0;">
                        <div style="display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span style="font-size: 0.6rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; opacity: 0.7;">Superficie</span>
                                <span style="font-size: 0.9rem; font-weight: 800; color: var(--color-text-main);"><?= $inv['largo_m'] * $inv['ancho_m'] ?> m²</span>
                            </div>
                            <span style="font-size: 1.2rem; opacity: 0.4;">📐</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span style="font-size: 0.6rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; opacity: 0.7;">Origen</span>
                                <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-muted);"><?= mb_convert_case($inv['parcela']['nombre'] ?: 'P-'.$inv['parcela_id'], MB_CASE_TITLE, "UTF-8") ?></span>
                            </div>
                            <span style="font-size: 1.2rem; opacity: 0.4;">🚜</span>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 3: ACCIONES PREMIUM (Blindadas) -->
                <div style="display: flex; gap: 8px; margin-top: auto;">
                    <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $url_query_cliente ?>" 
                       class="btn-sira btn-primary" style="flex: 2; text-decoration: none !important;">
                        <span>Ver Sensores</span>
                    </a>
                    
                    <a href="management/edit_invernadero.php?id=<?= $inv['invernadero_id'] ?>&from=lista" 
                       class="btn-sira btn-secondary" style="flex: 1; text-decoration: none !important;" title="Ajustes de infraestructura">
                        ⚙️
                    </a>
                    
                    <?php 
                        $query_params = "dashboard.php?plant_inv_id=" . $inv['invernadero_id'] . $url_query_cliente;
                        if (isset($_GET['seccion'])) $query_params .= "&seccion=" . $_GET['seccion'];
                    ?>
                    <a href="<?= $query_params ?>" 
                       class="btn-sira btn-secondary" style="flex: 1; text-decoration: none !important;" title="Cambiar o plantar cultivo">
                        🌱
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<?php // Fin de inv-cards-container ?>
