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
                <div class="card" style="padding: 1.5rem; justify-content: center;">
                    <span class="status">CP <?= htmlspecialchars($loc['codigo_postal']) ?></span>
                    
                    <!-- Cabecera Compacta -->
                    <div style="text-align: center; margin-bottom: 1.2rem;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 4px;">
                            <span style="font-size: 2rem;">📍</span>
                            <h3 style="margin: 0; padding: 0; min-height: auto; font-size: 1.5rem;"><?= htmlspecialchars($loc['municipio']) ?></h3>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--color-text-muted); font-weight: 500;">
                            <?= htmlspecialchars($loc['provincia']) ?>, España
                        </div>
                    </div>

                    <!-- Estadísticas Agrupadas -->
                    <div class="inv-specs-container" style="justify-content: center; gap: 2rem; background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                        <div class="inv-spec-item">
                            <span style="font-size: 1.2rem;">🚜</span>
                            <div class="inv-spec-info">
                                <span class="inv-spec-label">Parcelas</span>
                                <span class="inv-spec-value"><?= $loc['num_parcelas'] ?></span>
                            </div>
                        </div>
                        <div class="inv-spec-item">
                            <span style="font-size: 1.2rem;">🌱</span>
                            <div class="inv-spec-info">
                                <span class="inv-spec-label">Invernaderos</span>
                                <span class="inv-spec-value"><?= $loc['num_invernaderos_total'] ?></span>
                            </div>
                        </div>
                    </div>

                    <a href="dashboard.php?localidad_cp=<?= urlencode($loc['codigo_postal']) ?><?= $url_query_cliente ?>"
                        class="card-btn" style="margin-top: 1.2rem;">Ver Parcelas →</a>
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
            <div class="card" style="padding: 2rem;">
                <span class="status">ID #<?= $parc['parcela_id'] ?></span>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <span style="font-size: 2.5rem;">📋</span>
                    <h3 style="margin: 0; padding: 0; min-height: auto;"><?= htmlspecialchars($parc['nombre'] ?: $parc['direccion']) ?></h3>
                </div>
                
                <div class="inv-location-container">
                    <div class="inv-location-line">
                        <span class="loc-city"><?= htmlspecialchars($parc['direccion']) ?></span>
                    </div>
                </div>

                <div class="inv-specs-container" style="margin-top: 1.5rem;">
                    <div class="inv-spec-item">
                        <div class="inv-spec-icon">📄</div>
                        <div class="inv-spec-info">
                            <span class="inv-spec-label">Ref. Catastral</span>
                            <span class="inv-spec-value" style="font-size: 0.8rem;"><?= htmlspecialchars($parc['ref_catastral']) ?></span>
                        </div>
                    </div>
                    <div class="inv-spec-item inv-spec-crop">
                        <div class="inv-spec-icon">🏡</div>
                        <div class="inv-spec-info">
                            <span class="inv-spec-label">Invernaderos</span>
                            <span class="inv-spec-value"><?= $parc['num_invernaderos'] ?></span>
                        </div>
                    </div>
                </div>

                <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc['parcela_id'] ?><?= $url_query_cliente ?>"
                    class="card-btn" style="margin-top: 1.5rem;">Gestión Invernaderos →</a>
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
            <div id="inv-card-<?= $inv['invernadero_id'] ?>" class="card" style="padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; width: 100%;">
                    
                    <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 200px;">
                        <span style="font-size: 2.5rem;">🏡</span>
                        <div style="display: flex; flex-direction: column;">
                            <strong style="font-size: 1.35rem; color: var(--color-primary);"><?= htmlspecialchars($inv['nombre']) ?></strong>
                            <span style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600;">ID: #<?= $inv['invernadero_id'] ?></span>
                        </div>
                    </div>

                    <div class="inv-iot-box">
                        <div class="inv-iot-label">Seguimiento IoT</div>
                        <div class="inv-iot-status">
                            <span style="color: #34d399; font-size: 0.6rem;">●</span>
                            <span style="font-size: 0.85rem; font-weight: 700; color: #34d399;">Sincronizado</span>
                        </div>
                    </div>
                </div>

                <div class="inv-location-container">
                    <div class="inv-location-line">
                        <span class="loc-label">Parcela:</span>
                        <span class="loc-parcel-name"><?= htmlspecialchars($parc_seleccionada['ref_catastral']) ?></span>
                        <span class="loc-separator">|</span>
                        <span class="loc-city"><?= htmlspecialchars($loc_seleccionada['municipio']) ?></span>
                        <span class="loc-cp"><?= htmlspecialchars($loc_seleccionada['codigo_postal']) ?></span>
                    </div>
                </div>

                <div class="inv-specs-container">
                    <div class="inv-spec-item">
                        <div class="inv-spec-icon">📐</div>
                        <div class="inv-spec-info">
                            <span class="inv-spec-label">Superficie</span>
                            <span class="inv-spec-value"><?= $inv['largo_m'] * $inv['ancho_m'] ?> m²</span>
                        </div>
                    </div>
                    <div class="inv-spec-item inv-spec-crop">
                        <div class="inv-spec-icon">🌱</div>
                        <div class="inv-spec-info">
                            <span class="inv-spec-label">Estado de Producción</span>
                            <span class="inv-spec-value <?= $inv['cultivo'] ? '' : 'text-muted' ?>" style="color: <?= $inv['cultivo'] ? '#34d399' : 'inherit' ?>;">
                                <?= htmlspecialchars($inv['cultivo'] ?? 'En barbecho') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-actions-row">
                    <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&plant_inv_id=<?= $inv['invernadero_id'] ?><?= $url_query_cliente ?>#inv-card-<?= $inv['invernadero_id'] ?>" 
                       class="btn-sira btn-secondary btn-sm">🌱 Cambiar Cultivo</a>
                    <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $cliente_id_seleccionado ? '&cliente_id=' . $cliente_id_seleccionado : '' ?>"
                       class="btn-sira btn-primary btn-sm">Ver Sensores →</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
