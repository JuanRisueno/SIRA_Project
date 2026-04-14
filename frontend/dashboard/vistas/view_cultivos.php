<?php
/**
 * view_cultivos.php - Dashboard Dual de Variedades Botánicas (V10.0)
 * Permite alternar entre cuadrícula técnica y lista de gestión masiva.
 */

$modo_lista = (($_SESSION['dashboard_view'] ?? 'grid') === 'list');
?>

<div class="cultivos-container" style="margin-top: 2rem;">

    <?php if ($modo_lista): ?>
        <!-- VISTA LISTA: Gestión Masiva (Tabla) -->
        <table class="sira-table" style="width: 100%; border-collapse: separate; border-spacing: 0 8px; margin-top: -8px;">
            <thead>
                <tr>
                    <th style="padding: 1.2rem; border-radius: 12px 0 0 12px;">VARIEDAD</th>
                    <th>PROPIETARIO</th>
                    <th style="text-align: center;">PARÁMETROS (T/H)</th>
                    <th style="text-align: center;">ESTADO</th>
                    <th style="text-align: right; padding: 1.2rem; border-radius: 0 12px 12px 0;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todos_los_cultivos as $cult): 
                    $params = $cult['parametros'] ?? null;
                    $es_dueno = ($cult['cliente_id'] == ($_SESSION['cliente_id'] ?? null));
                    $es_admin_eff = in_array($_SESSION['user_rol'], ['admin', 'root']);
                    $puede_editar = $es_admin_eff || $es_dueno;
                    $is_target = (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $cult['cultivo_id']);
                ?>
                    <tr id="cultivo-card-<?= $cult['cultivo_id'] ?>" 
                        class="<?= $is_target ? 'highlight-glow' : '' ?>"
                        style="background: var(--color-bg-card); transition: transform 0.2s; <?= !$cult['activa'] ? 'opacity: 0.5;' : '' ?> <?= $is_target ? 'border: 2px solid var(--color-primary);' : '' ?>">
                        <td style="padding: 1rem; border-radius: 12px 0 0 12px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 1.2rem;">🌱</span>
                                <div style="display: flex; flex-direction: column;">
                                    <strong style="color: var(--color-text-main);"><?= htmlspecialchars($cult['nombre_cultivo']) ?></strong>
                                    <span style="font-size: 0.7rem; color: var(--color-text-muted);">ID: #<?= $cult['cultivo_id'] ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                             <span style="font-size: 0.8rem; font-weight: 600; color: <?= $cult['cliente_id'] ? 'var(--color-text-muted)' : 'var(--color-primary)' ?>;">
                                 <?= htmlspecialchars($cult['nombre_cliente'] ?? 'Sistema') ?>
                             </span>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($params): ?>
                                <span style="font-size: 0.85rem; color: #ffab00; font-weight: 700;"><?= (int)$params['temp_optima_min'] ?>°-<?= (int)$params['temp_optima_max'] ?>°</span>
                                <span style="color: var(--color-text-muted); margin: 0 5px;">|</span>
                                <span style="font-size: 0.85rem; color: #00d1ff; font-weight: 700;"><?= (int)$params['humedad_optima_min'] ?>-<?= (int)$params['humedad_optima_max'] ?>%</span>
                            <?php else: ?>
                                <span style="font-size: 0.75rem; color: var(--color-text-muted); font-style: italic;">N/D</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="status-indicator <?= $cult['activa'] ? 'status-online' : 'status-offline' ?>"></span>
                        </td>
                        <td style="text-align: right; padding: 1rem; border-radius: 0 12px 12px 0;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <?php if ($puede_editar): ?>
                                    <a href="management/edit_cultivo.php?id=<?= $cult['cultivo_id'] ?>" class="mini-btn-opt" title="Editar cultivo">📝</a>
                                <?php endif; ?>
                                <?php if ($es_admin_eff): ?>
                                    <a href="dashboard.php?seccion=cultivos&accion=status_cultivo&estado=<?= $cult['activa'] ? 'desactivar' : 'activar' ?>&id=<?= $cult['cultivo_id'] ?>" class="mini-btn-opt" title="<?= $cult['activa'] ? 'Ocultar del catálogo' : 'Mostrar en el catálogo' ?>">
                                        <?= $cult['activa'] ? '👁️' : '🕶️' ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <!-- VISTA CUADRÍCULA: Fichas Técnicas (Mosaico) -->
        <div class="cultivos-grid">
            <?php foreach ($todos_los_cultivos as $cult): 
                $params = $cult['parametros'] ?? null;
                $es_dueno = ($cult['cliente_id'] == ($_SESSION['cliente_id'] ?? null));
                $es_admin_eff = in_array($_SESSION['user_rol'], ['admin', 'root']);
                $puede_editar = $es_admin_eff || $es_dueno;
                $is_target = (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $cult['cultivo_id']);
            ?>
                <div id="cultivo-card-<?= $cult['cultivo_id'] ?>" 
                     class="cult-card <?= $is_target ? 'highlight-glow' : '' ?>" 
                     style="background: var(--color-bg-card); border: 1px solid <?= $is_target ? 'var(--color-primary)' : 'var(--border-color)' ?>; border-radius: 20px; padding: 1.5rem; position: relative; transition: all 0.3s; overflow: hidden; display: flex; flex-direction: column; <?= !$cult['activa'] ? 'opacity: 0.6;' : '' ?>">
                    <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: <?= $cult['activa'] ? 'var(--color-primary)' : 'var(--color-error)' ?>;"></div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.2rem;">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <span style="font-size: 0.65rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Ficha Técnica #<?= $cult['cultivo_id'] ?></span>
                            <h3 style="margin: 0; color: var(--color-text-main); font-size: 1.4rem; letter-spacing: -0.02em;"><?= htmlspecialchars($cult['nombre_cultivo']) ?></h3>
                            <div style="display: flex; gap: 6px; margin-top: 4px;">
                                <?php if ($es_dueno): ?>
                                    <span style="background: rgba(52, 211, 153, 0.1); color: var(--color-primary); font-size: 0.55rem; padding: 2px 8px; border-radius: 20px; font-weight: 700; border: 1px solid rgba(52, 211, 153, 0.2);">TU VARIEDAD</span>
                                <?php endif; ?>
                                <span style="background: rgba(255,255,255,0.05); color: var(--color-text-muted); font-size: 0.55rem; padding: 2px 8px; border-radius: 20px; font-weight: 700; border: 1px solid rgba(255,255,255,0.1);"><?= strtoupper($cult['nombre_cliente'] ?? '🌱 SISTEMA SIRA') ?></span>
                            </div>
                        </div>
                        <div class="card-actions" style="display: flex; gap: 5px;">
                            <?php if ($puede_editar): ?>
                                <a href="management/edit_cultivo.php?id=<?= $cult['cultivo_id'] ?>" class="mini-btn-opt" title="Editar cultivo">📝</a>
                            <?php endif; ?>
                            <?php if ($es_admin_eff): ?>
                                <a href="dashboard.php?seccion=cultivos&accion=status_cultivo&estado=<?= $cult['activa'] ? 'desactivar' : 'activar' ?>&id=<?= $cult['cultivo_id'] ?>" class="mini-btn-opt" title="<?= $cult['activa'] ? 'Ocultar del catálogo' : 'Mostrar en el catálogo' ?>">
                                    <?= $cult['activa'] ? '👁️' : '🕶️' ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="cult-tech-box">
                        <?php if ($params): ?>
                            <div class="cult-tech-item">
                                <div class="cult-tech-icon">🌡️</div>
                                <div class="cult-tech-info">
                                    <span class="cult-tech-label">Temperatura Óptima</span>
                                    <span class="cult-tech-value" style="color: #ffab00;"><?= (int)$params['temp_optima_min'] ?>°C - <?= (int)$params['temp_optima_max'] ?>°C</span>
                                </div>
                            </div>
                            <div class="cult-tech-item">
                                <div class="cult-tech-icon">💧</div>
                                <div class="cult-tech-info">
                                    <span class="cult-tech-label">Humedad Ambiente</span>
                                    <span class="cult-tech-value" style="color: #00d1ff;"><?= (int)$params['humedad_optima_min'] ?>% - <?= (int)$params['humedad_optima_max'] ?>%</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 1rem; color: var(--color-text-muted); font-size: 0.8rem; font-style: italic;">Sin pautas técnicas en fase 'General'.</div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: auto; padding-top: 1rem; font-size: 0.7rem; color: var(--color-text-muted); display: flex; justify-content: space-between; align-items: center;">
                         <span>Uso: <strong>Invernadero</strong></span>
                         <span>ID Reg: #<?= $cult['cultivo_id'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($todos_los_cultivos)): ?>
        <div style="text-align: center; padding: 6rem 2rem; background: var(--color-bg-card); border-radius: 24px; border: 2px dashed rgba(255,255,255,0.05);">
            <div style="font-size: 4rem; margin-bottom: 2rem; opacity: 0.2;">🌾</div>
            <h3 style="color: var(--color-text-main);">Catálogo de Variedades Vacío</h3>
        </div>
    <?php endif; ?>

</div>

<style>
/* Marcado visual estático para item seleccionado (V9.0 UX) */
.highlight-glow {
    border-color: var(--color-primary) !important;
    border-width: 2px !important;
    box-shadow: 0 0 20px var(--color-primary-glow) !important;
}

.cult-card:hover { transform: translateY(-8px); border-color: var(--color-primary-light); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
.sira-table tr:hover { transform: scale(1.005); background: rgba(52, 211, 153, 0.05) !important; z-index: 10; cursor: default; }
</style>
