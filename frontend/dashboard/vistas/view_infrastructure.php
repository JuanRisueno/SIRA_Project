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
                <div class="inv-smart-card">
                    
                    <!-- NIVEL 1: CABECERA REGIONAL (Municipio + IoT Badge) -->
                    <div class="card-nivel-header">
                        <div class="card-title-group">
                            <h3>
                                <?= mb_convert_case($loc['municipio'], MB_CASE_TITLE, "UTF-8") ?>
                            </h3>
                            <div class="card-subtitle">
                                <span>📍 <?= mb_convert_case($loc['provincia'], MB_CASE_TITLE, "UTF-8") ?></span>
                                <span style="opacity: 0.3;">|</span>
                                <span>España</span>
                            </div>
                        </div>

                        <div class="badge-iot-live">
                            <span class="badge-text-premium">CP <?= htmlspecialchars($loc['codigo_postal']) ?></span>
                        </div>
                    </div>

                    <!-- NIVEL 2: RESUMEN OPERATIVO -->
                    <div class="card-nivel-tecnico">
                        
                        <!-- Identidad Regional -->
                        <div class="tecnico-bloque-identidad">
                            <div style="font-size: 2.2rem; opacity: 0.8; filter: drop-shadow(0 0 8px rgba(52, 211, 153, 0.15));">🗺️</div>
                            <div class="tecnico-datos-group">
                                <span class="tecnico-label">Zonificación</span>
                                <span class="tecnico-valor-main">Sede Central</span>
                            </div>
                        </div>

                        <!-- Contadores Técnicos -->
                        <div class="tecnico-datos-derecha">
                            <div class="tecnico-item-mini">
                                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-size: 1.1rem; font-weight: 800; color: var(--color-text-main);"><?= $loc['num_parcelas'] ?></span>
                                        <span style="font-size: 0.85rem; opacity: 0.5;">🚜</span>
                                    </div>
                                    <span class="tecnico-label">Parcelas</span>
                                </div>
                            </div>

                            <div class="tecnico-item-mini">
                                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);"><?= $loc['num_invernaderos_total'] ?></span>
                                        <span style="font-size: 0.85rem; opacity: 0.5;">🌱</span>
                                    </div>
                                    <span class="tecnico-label">Invernaderos</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NIVEL 3: ACCIÓN DE EXPLORACIÓN (EXPANDIDA) -->
                    <a href="dashboard.php?localidad_cp=<?= urlencode($loc['codigo_postal']) ?><?= $url_query_cliente ?>" 
                       class="stretched-link"></a>
                    
                    <div style="margin-top: auto; text-align: right;">
                        <span class="list-subtitle" style="font-size: 0.7rem; opacity: 0.5;">PULSAR PARA EXPLORAR ➜</span>
                    </div>
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
            <div id="parc-card-<?= $parc['parcela_id'] ?>" class="inv-smart-card">
                
                <!-- NIVEL 1: CABECERA DE IDENTIDAD (Nombre + ID) -->
                <div class="card-nivel-header">
                    <div class="card-title-group">
                        <h3>
                            <?= mb_convert_case($parc['nombre'] ?: 'Finca #' . $parc['parcela_id'], MB_CASE_TITLE, "UTF-8") ?>
                        </h3>
                        <div class="card-subtitle">
                            <span>ID REGISTRO #<?= $parc['parcela_id'] ?></span>
                        </div>
                    </div>

                    <div style="background: rgba(16, 185, 129, 0.05); padding: 4px 12px; border-radius: var(--radius-container); border: 1px solid rgba(16, 185, 129, 0.15);">
                        <span style="font-size: 0.65rem; font-weight: 900; color: var(--color-primary); letter-spacing: 0.1em;">ACTIVA</span>
                    </div>
                </div>

                <!-- NIVEL 2: CORAZÓN TÉCNICO -->
                <div class="card-nivel-tecnico">
                    
                    <!-- BLOQUE IZQUIERDO: Infraestructura -->
                    <div class="tecnico-bloque-identidad">
                        <div class="tecnico-avatar-icon">
                            🚜
                        </div>

                        <div class="tecnico-datos-group">
                            <span class="tecnico-label">Operativa</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong class="tecnico-valor-main"><?= $parc['num_invernaderos'] ?> Invernaderos</strong>
                                <span style="font-size: 0.65rem; background: rgba(16, 185, 129, 0.15); color: var(--color-primary); padding: 2px 8px; border-radius: 6px; font-weight: 800;">ZONA</span>
                            </div>
                        </div>
                    </div>

                    <!-- BLOQUE DERECHO: Datos Catastrales -->
                    <div class="tecnico-datos-derecha">
                        <div class="tecnico-item-mini">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span class="tecnico-label" style="opacity: 0.7;">Ref. Catastral</span>
                                <span style="font-size: 0.9rem; font-weight: 800; color: var(--color-text-main); font-family: 'Roboto Mono', monospace; letter-spacing: -0.01em;"><?= htmlspecialchars($parc['ref_catastral']) ?></span>
                            </div>
                            <span style="font-size: 1.2rem; opacity: 0.4;">📋</span>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 3: ACCIONES PREMIUM (EXPANDIDAS) -->
                <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc['parcela_id'] ?><?= $url_query_cliente ?>" 
                   class="stretched-link"></a>

                <div style="margin-top: auto; text-align: right;">
                    <span class="list-subtitle" style="font-size: 0.7rem; opacity: 0.5;">GESTIONAR INVERNADEROS ➜</span>
                </div>
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
            <div id="inv-card-<?= $inv['invernadero_id'] ?>" class="inv-smart-card">
                
                <!-- NIVEL 1: CABECERA DE CONTROL (Identidad + Live IoT) -->
                <div class="card-nivel-header">
                    <div class="card-title-group">
                        <h3>
                            <?= mb_convert_case($inv['nombre'], MB_CASE_TITLE, "UTF-8") ?>
                        </h3>
                        <div class="card-subtitle">
                            <span>#<?= $inv['invernadero_id'] ?></span>
                            <span style="opacity: 0.3;">|</span>
                            <span>📦 <?= mb_convert_case($loc_seleccionada['municipio'], MB_CASE_TITLE, "UTF-8") ?></span>
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
                            <?= get_crop_icon($inv['cultivo']) ?>
                        </div>

                        <div class="tecnico-datos-group">
                            <span class="tecnico-label">Producción Actual</span>
                            <strong class="tecnico-valor-main"><?= mb_convert_case($inv['cultivo'] ?: 'En barbecho', MB_CASE_TITLE, "UTF-8") ?></strong>
                        </div>
                    </div>

                    <!-- BLOQUE DERECHO: Especificaciones Técnicas -->
                    <div class="tecnico-datos-derecha">
                        <div class="tecnico-item-mini">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span class="tecnico-label" style="opacity: 0.7;">Superficie</span>
                                <span style="font-size: 0.9rem; font-weight: 800; color: var(--color-text-main);"><?= $inv['largo_m'] * $inv['ancho_m'] ?> m²</span>
                            </div>
                            <span style="font-size: 1.2rem; opacity: 0.4;">📐</span>
                        </div>
                        <div class="tecnico-item-mini">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span class="tecnico-label" style="opacity: 0.7;">Ref</span>
                                <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-muted); font-family: 'Roboto Mono', monospace;"><?= htmlspecialchars(substr($parc_seleccionada['ref_catastral'], -5)) ?></span>
                            </div>
                            <span style="font-size: 1.2rem; opacity: 0.4;">🚜</span>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 3: ACCIONES PREMIUM -->
                <div class="card-nivel-acciones" style="border-top: 1px solid rgba(255,255,255,0.03); padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $cliente_id_seleccionado ? '&cliente_id=' . $cliente_id_seleccionado : '' ?>"
                       class="stretched-link"></a>
                    
                    <span class="list-subtitle" style="font-size: 0.70rem; opacity: 0.5;">PANEL DE SENSORES ➜</span>
                    
                    <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&plant_inv_id=<?= $inv['invernadero_id'] ?><?= $url_query_cliente ?>#inv-card-<?= $inv['invernadero_id'] ?>" 
                       class="btn-sira btn-secondary" style="padding: 6px 14px; font-size: 0.85rem; position: relative; z-index: 10; display: inline-flex; align-items: center; gap: 6px;" title="Cambiar Cultivo">
                        🌱 <span style="font-size: 0.75rem;">Cambiar</span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>