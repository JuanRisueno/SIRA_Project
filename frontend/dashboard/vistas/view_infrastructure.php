<?php
/**
 * view_infrastructure.php - Visualización de Activos (Versión Modular V11.5)
 * Soporta navegación fluida por Localidades, Parcelas e Invernaderos.
 */
?>

<div class="infra-grid-container">
    <?php if ($vista_actual === 'localidades'): ?>
        <?php if (empty($localidades_data)): ?>
            <div class="card empty-state" style="grid-column: 1 / -1;">
                <div style="font-size: 3.5rem; margin-bottom: 1.5rem;">🚜</div>
                <h3>No hay infraestructura registrada</h3>
                <p>Para comenzar a monitorizar, necesitas añadir tu primera parcela.</p>
                <?php if ($es_admin): ?>
                    <a href="management/add_user.php" class="btn-sira btn-primary" style="width: auto; padding: 0.8rem 2rem;">Añadir Agricultor</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($localidades_data as $loc): ?>
                <div class="inv-smart-card" 
                     style="background: var(--color-bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-container); padding: 1.5rem; position: relative; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; gap: 1.2rem;">
                    
                    <!-- NIVEL 1: CABECERA REGIONAL (Municipio + IoT Badge) -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <h3 style="margin: 0; color: var(--color-primary); font-size: 1.5rem; letter-spacing: -0.03em; font-weight: 800;">
                                <?= mb_convert_case($loc['municipio'], MB_CASE_TITLE, "UTF-8") ?>
                            </h3>
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.75rem; color: var(--color-text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8;">
                                <span>📍 <?= mb_convert_case($loc['provincia'], MB_CASE_TITLE, "UTF-8") ?></span>
                                <span style="opacity: 0.3;">|</span>
                                <span>España</span>
                            </div>
                        </div>

                        <div style="background: rgba(52, 211, 153, 0.1); padding: 4px 12px; border-radius: var(--radius-container); border: 1px solid var(--color-primary-border);">
                            <span style="font-size: 0.7rem; font-weight: 900; color: var(--color-primary); letter-spacing: 0.1em;">CP <?= htmlspecialchars($loc['codigo_postal']) ?></span>
                        </div>
                    </div>

                    <!-- NIVEL 2: RESUMEN OPERATIVO (Distribución Balanceada) -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: rgba(255,255,255,0.02); padding: 1.2rem; border-radius: var(--radius-container); border: 1px solid rgba(255,255,255,0.03);">
                        
                        <!-- Identidad Regional -->
                        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                            <div style="font-size: 2.2rem; opacity: 0.8; filter: drop-shadow(0 0 8px rgba(52, 211, 153, 0.15));">🗺️</div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 0.6rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800;">Zonificación</span>
                                <span style="font-size: 0.9rem; font-weight: 700; color: var(--color-text-main);">Sede Central</span>
                            </div>
                        </div>

                        <!-- Contadores Técnicos -->
                        <div style="display: flex; gap: 1.2rem; align-items: center; text-align: right;">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 1.1rem; font-weight: 800; color: var(--color-text-main);"><?= $loc['num_parcelas'] ?></span>
                                    <span style="font-size: 0.85rem; opacity: 0.5;">🚜</span>
                                </div>
                                <span style="font-size: 0.55rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Parcelas</span>
                            </div>

                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);"><?= $loc['num_invernaderos_total'] ?></span>
                                    <span style="font-size: 0.85rem; opacity: 0.5;">🌱</span>
                                </div>
                                <span style="font-size: 0.55rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Invernaderos</span>
                            </div>
                        </div>
                    </div>

                    <!-- NIVEL 3: ACCIÓN DE EXPLORACIÓN (Blindada) -->
                    <a href="dashboard.php?localidad_cp=<?= urlencode($loc['codigo_postal']) ?><?= $url_query_cliente ?>" 
                       class="btn-sira btn-primary" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; font-size: 0.95rem; text-decoration: none !important;">
                        <span>Ver Parcelas de la Zona</span>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($vista_actual === 'parcelas'): ?>
        <?php if (empty($parcelas_data)): ?>
            <div class="card empty-state" style="grid-column: 1 / -1;">
                <p>No hay parcelas registradas en esta localidad.</p>
            </div>
        <?php endif; ?>
        <?php foreach ($parcelas_data as $parc): ?>
            <div id="parc-card-<?= $parc['parcela_id'] ?>" 
                 class="inv-smart-card" 
                 style="background: var(--color-bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-container); padding: 1.5rem; position: relative; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; gap: 1.2rem;">
                
                <!-- NIVEL 1: CABECERA DE IDENTIDAD (Nombre + ID) -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <h3 style="margin: 0; color: var(--color-primary); font-size: 1.5rem; letter-spacing: -0.03em; font-weight: 800;">
                            <?= mb_convert_case($parc['nombre'] ?: 'Finca #' . $parc['parcela_id'], MB_CASE_TITLE, "UTF-8") ?>
                        </h3>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.7rem; color: var(--color-text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8;">
                            <span>ID REGISTRO #<?= $parc['parcela_id'] ?></span>
                        </div>
                    </div>

                    <div style="background: rgba(16, 185, 129, 0.05); padding: 4px 12px; border-radius: var(--radius-container); border: 1px solid rgba(16, 185, 129, 0.15);">
                        <span style="font-size: 0.65rem; font-weight: 900; color: var(--color-primary); letter-spacing: 0.1em;">ACTIVA</span>
                    </div>
                </div>

                <!-- NIVEL 2: CORAZÓN TÉCNICO (Distribución Balanceada) -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: rgba(255,255,255,0.02); padding: 1.2rem; border-radius: var(--radius-container); border: 1px solid rgba(255,255,255,0.03);">
                    
                    <!-- BLOQUE IZQUIERDO: Infraestructura -->
                    <div style="display: flex; align-items: center; gap: 1.2rem; flex: 1; min-width: 0;">
                        <div style="width: 66px; height: 66px; background: rgba(16, 185, 129, 0.08); border-radius: var(--radius-container); display: flex; align-items: center; justify-content: center; font-size: 2.6rem; border: 1px solid rgba(16, 185, 129, 0.1); flex-shrink: 0; transition: transform 0.3s;" class="inv-avatar-icon">
                            🚜
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 2px; overflow: hidden;">
                            <span style="font-size: 0.6rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Operativa</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong style="font-size: 1.15rem; color: var(--color-text-main);"><?= $parc['num_invernaderos'] ?> Invernaderos</strong>
                                <span style="font-size: 0.65rem; background: rgba(16, 185, 129, 0.15); color: var(--color-primary); padding: 2px 8px; border-radius: 6px; font-weight: 800;">ZONA</span>
                            </div>
                        </div>
                    </div>

                    <!-- BLOQUE DERECHO: Datos Catastrales -->
                    <div style="display: flex; flex-direction: column; gap: 8px; text-align: right; flex-shrink: 0;">
                        <div style="display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span style="font-size: 0.6rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; opacity: 0.7;">Ref. Catastral</span>
                                <span style="font-size: 0.9rem; font-weight: 800; color: var(--color-text-main); font-family: 'Roboto Mono', monospace; letter-spacing: -0.01em;"><?= htmlspecialchars($parc['ref_catastral']) ?></span>
                            </div>
                            <span style="font-size: 1.2rem; opacity: 0.4;">📋</span>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 3: ACCIONES PREMIUM -->
                <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc['parcela_id'] ?><?= $url_query_cliente ?>" 
                   class="btn-sira btn-primary" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; font-size: 0.95rem; text-decoration: none;">
                    <span>Gestión de Invernaderos</span>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($vista_actual === 'invernaderos'): ?>
        <?php if (empty($invernaderos_data)): ?>
            <div class="card empty-state" style="grid-column: 1 / -1;">
                <p>No hay invernaderos en esta parcela.</p>
            </div>
        <?php endif; ?>
        <?php foreach ($invernaderos_data as $inv): ?>
            <div id="inv-card-<?= $inv['invernadero_id'] ?>" 
                 class="inv-smart-card" 
                 style="background: var(--color-bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-container); padding: 1.5rem; position: relative; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; gap: 1.2rem;">
                
                <!-- NIVEL 1: CABECERA DE CONTROL (Identidad + Live IoT) -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <h3 style="margin: 0; color: var(--color-primary); font-size: 1.5rem; letter-spacing: -0.03em; font-weight: 800;">
                            <?= mb_convert_case($inv['nombre'], MB_CASE_TITLE, "UTF-8") ?>
                        </h3>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.7rem; color: var(--color-text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8;">
                            <span>#<?= $inv['invernadero_id'] ?></span>
                            <span style="opacity: 0.3;">|</span>
                            <span>📦 <?= mb_convert_case($loc_seleccionada['municipio'], MB_CASE_TITLE, "UTF-8") ?></span>
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
                            <?= get_crop_icon($inv['cultivo']) ?>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 2px; overflow: hidden;">
                            <span style="font-size: 0.6rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Producción Actual</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong style="font-size: 1.15rem; color: var(--color-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= mb_convert_case($inv['cultivo'] ?: 'En barbecho', MB_CASE_TITLE, "UTF-8") ?></strong>
                            </div>
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
                                <span style="font-size: 0.6rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; opacity: 0.7;">Ref</span>
                                <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-muted); font-family: 'Roboto Mono', monospace;"><?= htmlspecialchars(substr($parc_seleccionada['ref_catastral'], -5)) ?></span>
                            </div>
                            <span style="font-size: 1.2rem; opacity: 0.4;">🚜</span>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 3: ACCIONES PREMIUM (Blindadas) -->
                <div style="display: flex; gap: 8px; margin-top: auto;">
                    <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $cliente_id_seleccionado ? '&cliente_id=' . $cliente_id_seleccionado : '' ?>"
                       class="btn-sira btn-primary" style="flex: 2; text-decoration: none !important;">
                        <span>Ver Sensores</span>
                    </a>
                    
                    <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&plant_inv_id=<?= $inv['invernadero_id'] ?><?= $url_query_cliente ?>#inv-card-<?= $inv['invernadero_id'] ?>" 
                       class="btn-sira btn-secondary" style="flex: 1; text-decoration: none !important;" title="Cambiar Cultivo">
                        🌱
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>