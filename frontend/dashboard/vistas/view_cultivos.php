<?php
/**
 * view_cultivos.php - Dashboard Dual de Variedades Botánicas (V10.2 Failsafe)
 */

$modo_lista = (($_SESSION['dashboard_view'] ?? 'grid') === 'list');
$user_rol = $_SESSION['user_rol'] ?? '';
$mi_cliente_id = $_SESSION['cliente_id'] ?? null;

// --- MOTOR DE ORDENACIÓN ---
if (!empty($todos_los_cultivos)) {
    usort($todos_los_cultivos, function($a, $b) use ($user_rol, $mi_cliente_id) {
        $es_admin = in_array($user_rol, ['admin', 'root']);
        
        if ($es_admin) {
            $p_a = empty($a['cliente_id']) ? 1 : 2;
            $p_b = empty($b['cliente_id']) ? 1 : 2;
        } else {
            if ($a['cliente_id'] == $mi_cliente_id) $p_a = 1;
            elseif (!empty($a['cliente_id'])) $p_a = 2;
            else $p_a = 3;

            if ($b['cliente_id'] == $mi_cliente_id) $p_b = 1;
            elseif (!empty($b['cliente_id'])) $p_b = 2;
            else $p_b = 3;
        }

        if ($p_a == $p_b) {
            return strcasecmp($a['nombre_cultivo'], $b['nombre_cultivo']);
        }
        return $p_a <=> $p_b;
    });
}
?>

<div class="cultivos-container" style="margin-top: 2rem;">

    <?php if ($modo_lista): ?>
        <div class="cultivos-list sira-table-container">
            <table class="sira-table">
            <thead>
                <tr>
                    <th style="width: 35%;">VARIEDAD</th>
                    <th>PROPIETARIO</th>
                    <th style="text-align: center;">PARÁMETROS (T/H)</th>
                    <th style="text-align: center;">ESTADO</th>
                    <th style="text-align: right;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todos_los_cultivos as $cult): 
                    $params = $cult['parametros'] ?? null;
                    $es_dueno = ($cult['cliente_id'] == $mi_cliente_id);
                    $es_admin_eff = in_array($user_rol, ['admin', 'root']);
                    $puede_editar = $es_admin_eff || $es_dueno;
                    $is_target = (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $cult['cultivo_id']);
                ?>
                    <tr id="cultivo-card-<?= $cult['cultivo_id'] ?>" 
                        class="<?= $is_target ? 'highlight-glow' : '' ?> <?= empty($cult['activa']) ? 'row-inactive' : '' ?>">
                        <td>
                            <div class="list-cell-main">
                                <span class="list-main-icon"><?= get_crop_icon($cult['nombre_cultivo']) ?></span>
                                <div class="list-main-stack">
                                    <strong class="list-title"><?= htmlspecialchars($cult['nombre_cultivo']) ?></strong>
                                    <span class="cult-scientific" style="font-size: 0.75rem; color: var(--color-text-muted);">
                                        <?= htmlspecialchars($cult['nombre_cientifico'] ?? '') ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                         <td>
                              <span class="list-badge-tech <?= !empty($cult['cliente_id']) ? 'badge-muted' : '' ?>">
                                  <?php 
                                     if ($es_dueno) {
                                         echo 'TU CULTIVO';
                                     } elseif (empty($cult['cliente_id'])) {
                                         echo 'SIRA';
                                     } elseif ($es_admin_eff) {
                                         echo 'CLIENTE #' . htmlspecialchars($cult['cliente_id']);
                                     } else {
                                         echo 'COMUNIDAD';
                                     }
                                  ?>
                              </span>
                         </td>
                        <td style="text-align: center;">
                            <?php if ($params): ?>
                                <div class="list-data-pair">
                                    <span class="list-accent-temp"><?= (int)($params['temp_optima_min'] ?? 0) ?>°-<?= (int)($params['temp_optima_max'] ?? 0) ?>°</span>
                                    <span class="list-separator">|</span>
                                    <span class="list-accent-hum"><?= (int)($params['humedad_optima_min'] ?? 0) ?>-<?= (int)($params['humedad_optima_max'] ?? 0) ?>%</span>
                                </div>
                            <?php else: ?>
                                <span class="list-subtitle" style="font-style: italic;">N/D</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="list-status-dot <?= !empty($cult['activa']) ? 'status-online' : 'status-offline' ?>"></span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <?php if ($puede_editar): ?>
                                    <a href="formularios/formulario_cultivo.php?id=<?= $cult['cultivo_id'] ?>" class="mini-btn-opt" title="Editar cultivo">📝</a>
                                <?php endif; ?>
                                <?php if ($es_admin_eff): ?>
                                    <a href="dashboard.php?seccion=cultivos&accion=status_cultivo&estado=<?= !empty($cult['activa']) ? 'desactivar' : 'activar' ?>&id=<?= $cult['cultivo_id'] ?>" 
                                       class="mini-btn-opt" 
                                       style="color: <?= !empty($cult['activa']) ? 'var(--color-warning)' : 'var(--color-primary)' ?>;"
                                       title="<?= !empty($cult['activa']) ? 'Ocultar del catálogo' : 'Mostrar en el catálogo' ?>">
                                        <?= !empty($cult['activa']) ? '👁️' : '🕶️' ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="sira-grid">
            <?php foreach ($todos_los_cultivos as $cult): 
                $params = $cult['parametros'] ?? null;
                $es_dueno = ($cult['cliente_id'] == $mi_cliente_id);
                $es_admin_eff = in_array($user_rol, ['admin', 'root']);
                $puede_editar = $es_admin_eff || $es_dueno;
                $is_target = (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $cult['cultivo_id']);
            ?>
                <div id="cultivo-card-<?= $cult['cultivo_id'] ?>" 
                     class="sira-card <?= $is_target ? 'highlight-glow' : '' ?> <?= empty($cult['activa']) ? 'inactivo' : '' ?>">
                    <div class="sira-card-accent" style="background: <?= !empty($cult['activa']) ? 'var(--color-primary)' : 'var(--color-error)' ?>;"></div>
                    
                    <?php if ($puede_editar): ?>
                        <a href="formularios/formulario_cultivo.php?id=<?= $cult['cultivo_id'] ?>" class="stretched-link"></a>
                    <?php endif; ?>

                    <div class="sira-card-header">
                        <span class="crop-main-icon" style="font-size: 2.8rem; filter: drop-shadow(0 0 10px rgba(255,255,255,0.05));">
                            <?= get_crop_icon($cult['nombre_cultivo']) ?>
                        </span>
                        
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px; position: relative; z-index: 10;">
                            <span class="list-badge-tech <?= !empty($cult['cliente_id']) ? 'badge-muted' : '' ?>">
                                <?php 
                                    if ($es_dueno) echo 'TU CULTIVO';
                                    elseif (empty($cult['cliente_id'])) echo 'SIRA';
                                    elseif ($es_admin_eff) echo 'CLIENTE #' . htmlspecialchars($cult['cliente_id']);
                                    else echo 'COMUNIDAD';
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="sira-card-body">
                        <h3 class="card-title" title="<?= htmlspecialchars($cult['nombre_cultivo']) ?>">
                            <?= mb_convert_case($cult['nombre_cultivo'], MB_CASE_TITLE, "UTF-8") ?>
                        </h3>
                        <p class="cult-description" style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 2px;">
                            <?= htmlspecialchars($cult['nombre_cientifico'] ?? '') ?>
                        </p>
                        
                        <div class="cult-tech-box" style="margin-top: 1rem;">
                            <?php if ($params): ?>
                                <div class="cult-tech-item">
                                    <div class="cult-tech-icon">🌡️</div>
                                    <div class="cult-tech-info">
                                        <span class="cult-tech-label">Temperatura Óptima</span>
                                        <span class="cult-tech-value" style="color: #ffab00;"><?= (int)($params['temp_optima_min'] ?? 0) ?>°C - <?= (int)($params['temp_optima_max'] ?? 0) ?>°C</span>
                                    </div>
                                </div>
                                <div class="cult-tech-item">
                                    <div class="cult-tech-icon">💧</div>
                                    <div class="cult-tech-info">
                                        <span class="cult-tech-label">Humedad Ambiente</span>
                                        <span class="cult-tech-value" style="color: #00d1ff;"><?= (int)($params['humedad_optima_min'] ?? 0) ?>% - <?= (int)($params['humedad_optima_max'] ?? 0) ?>%</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="text-align: center; padding: 0.5rem; color: var(--color-text-muted); font-size: 0.75rem; font-style: italic;">Sin pautas técnicas registradas.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="sira-card-footer">
                         <span class="list-subtitle">Entorno: <strong>P. Abierta</strong></span>
                         <span class="list-subtitle">ID Reg: #<?= $cult['cultivo_id'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($todos_los_cultivos)): ?>
        <div style="text-align: center; padding: 6rem 2rem; background: var(--color-bg-card); border-radius: var(--radius-container); border: 2px dashed rgba(255,255,255,0.05);">
            <div style="font-size: 4rem; margin-bottom: 2rem; opacity: 0.2;">🌾</div>
            <h3 style="color: var(--color-text-main);">Catálogo de Variedades Vacío</h3>
        </div>
    <?php endif; ?>

</div>