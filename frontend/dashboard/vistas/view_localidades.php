<?php
/**
 * view_localidades.php - Gestión Administrativa de Municipios y Provincias
 * Refactorizado V11.5: Soporte para vista dual (Lista/Mosaico) con responsividad forzada.
 */

$modo_lista = (($_SESSION['dashboard_view'] ?? 'grid') === 'list');
?>

<div class="list-container" style="margin-top: 1rem;">

    <!-- Avisos de Estado -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'borrado_ok'): ?>
        <div style="background: rgba(46, 204, 113, 0.1); border: 1px solid #2ecc71; color: #2ecc71; padding: 1rem; border-radius: var(--radius-container); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.8rem;">
            <span>✅</span> Localidad eliminada correctamente.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: rgba(231, 76, 60, 0.1); border: 1px solid var(--color-error); color: var(--color-error); padding: 1rem; border-radius: var(--radius-container); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.8rem;">
            <span>❌</span> <strong>Error:</strong> <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <?php if ($modo_lista): ?>
        <!-- VISTA LISTA (Tabla) -->
        <div class="localidades-list sira-table-container">
            <table class="sira-table">
                <thead>
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>Código Postal</th>
                        <th>Municipio</th>
                        <th>Provincia</th>
                        <th style="text-align: center;">Parcelas</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todas_las_localidades as $loc): ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php if ($loc['num_parcelas'] == 0): ?>
                                <?php else: ?>
                                    <span style="opacity: 0.2; font-size: 1.1rem;" title="Localidad con parcelas activa">🏢</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="list-badge-tech badge-muted"><?= htmlspecialchars($loc['codigo_postal']) ?></span></td>
                            <td>
                                <div class="list-cell-main">
                                    <span class="list-main-icon">🏙️</span>
                                    <div class="list-main-stack">
                                        <strong class="list-title"><?= htmlspecialchars($loc['municipio']) ?></strong>
                                        <span class="list-subtitle">Municipio Registrado</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="list-subtitle"><?= htmlspecialchars($loc['provincia']) ?></span></td>
                            <td style="text-align: center;">
                                <div class="list-data-pair">
                                    <span class="list-status-dot <?= $loc['num_parcelas'] == 0 ? 'status-offline' : 'status-online' ?>"></span>
                                    <strong style="<?= $loc['num_parcelas'] == 0 ? 'color: var(--color-error);' : '' ?>">
                                        <?= $loc['num_parcelas'] ?>
                                    </strong>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <?php if ($loc['num_parcelas'] > 0): ?>
                                        <a href="dashboard.php?ver_detalle_loc=1&cp=<?= urlencode($loc['codigo_postal']) ?>" class="mini-btn-opt" style="color: var(--color-primary); text-decoration: none;" title="Consultar Activos en <?= htmlspecialchars($loc['municipio']) ?>">🗺️</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- VISTA MOSAICO (Tarjetas) - Siempre disponible para CSS responsivo -->
    <div class="sira-grid" style="<?= $modo_lista ? 'display: none;' : '' ?>">
        <?php foreach ($todas_las_localidades as $loc): ?>
            <div class="sira-card">
                <div class="sira-card-accent"></div>
                
                <div class="sira-card-header">
                    <div class="card-icon-box">🏙️</div>
                    <span class="list-badge-tech" style="position: relative; z-index: 10;"><?= htmlspecialchars($loc['codigo_postal']) ?></span>
                </div>
                <div class="sira-card-body">
                    <span class="list-subtitle"><?= htmlspecialchars($loc['provincia']) ?></span>
                    <h3 class="card-title"><?= htmlspecialchars($loc['municipio']) ?></h3>
                    
                    <div class="loc-stats" style="margin-top: 1rem; background: var(--color-bg-stats); padding: 0.8rem; border-radius: var(--radius-container); display: flex; justify-content: space-between; align-items: center;">
                        <span class="list-subtitle">Parcelas Registradas</span>
                        <span class="list-data-pair">
                            <span class="list-status-dot <?= $loc['num_parcelas'] == 0 ? 'status-offline' : 'status-online' ?>"></span>
                            <strong style="<?= $loc['num_parcelas'] == 0 ? 'color: var(--color-error);' : 'color: var(--color-primary);' ?> font-size: 1.1rem;">
                                <?= $loc['num_parcelas'] ?>
                            </strong>
                        </span>
                    </div>
                </div>
                <div class="sira-card-footer" style="gap: 8px; position: relative; z-index: 10;">
                     <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                        <?php if ($loc['num_parcelas'] > 0): ?>
                            <a href="dashboard.php?ver_detalle_loc=1&cp=<?= urlencode($loc['codigo_postal']) ?>" class="btn-sira btn-primary btn-sm" style="flex: 1; text-align: center; text-decoration: none;">Explorar Parcelas</a>
                        <?php else: ?>
                            <span class="btn-sira btn-outline btn-sm" style="flex: 1; text-align: center; opacity: 0.5; cursor: default;">Sin Parcelas</span>
                        <?php endif; ?>
                     </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($todas_las_localidades)): ?>
        <div style="text-align: center; padding: 4rem; background: var(--color-bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color); margin-top: 1rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📍</div>
            <h3 style="color: var(--color-text-main);">No hay localidades registradas</h3>
            <p style="color: var(--color-text-muted);">El catálogo está vacío actualmente.</p>
        </div>
    <?php endif; ?>
</div>
