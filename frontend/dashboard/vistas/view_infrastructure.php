<?php
/**
 * view_infrastructure.php - Visualización de Activos
 * Renderiza Localidades, Parcelas e Invernaderos.
 */
?>

<div class="grid">
    <?php if ($vista_actual === 'localidades'): ?>
        <?php if (empty($localidades_data)): ?>
            <div class="card empty-state" style="grid-column: 1 / -1; text-align: center; padding: 4rem; background: var(--color-bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color);">
                <div style="font-size: 3.5rem; margin-bottom: 1.5rem;">🚜</div>
                <h3 style="color: var(--color-text-main); margin-bottom: 0.5rem;">No hay infraestructura registrada</h3>
                <p style="color: var(--color-text-muted); margin-bottom: 2rem;">Para comenzar a monitorizar, necesitas añadir tu primera parcela.</p>
                <?php if ($es_admin): ?>
                    <a href="management/add_parcela.php?cliente_id=<?= $cliente_id_seleccionado ?>" class="btn-sira btn-primary" style="width: auto; padding: 0.8rem 2rem;">Añadir Primera Parcela</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($localidades_data as $loc): ?>
                <div class="card">
                    <span class="status">CP <?= htmlspecialchars($loc['codigo_postal']) ?></span>
                    <h3><?= htmlspecialchars($loc['municipio']) ?></h3>
                    <div class="meta">📌 Provincia: <?= htmlspecialchars($loc['provincia']) ?></div>
                    <div class="meta">🚜 <?= $loc['num_parcelas'] ?> Parcelas</div>
                    <div class="meta">🌱 <?= $loc['num_invernaderos_total'] ?> Invernaderos</div>
                    <a href="dashboard.php?localidad_cp=<?= urlencode($loc['codigo_postal']) ?><?= $url_query_cliente ?>"
                        class="card-btn">Ver Parcelas →</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($vista_actual === 'parcelas'): ?>
        <?php if (empty($parcelas_data)): ?>
            <div class="card empty-state">
                <p>No hay parcelas registradas en esta localidad.</p>
            </div>
        <?php endif; ?>
        <?php foreach ($parcelas_data as $parc): ?>
            <div class="card">
                <?php if ($es_admin || $user_rol === 'cliente'): ?>
                    <div class="card-options">
                        <input type="checkbox" id="menu-parc-<?= $parc['parcela_id'] ?>" class="menu-toggle">
                        <label for="menu-parc-<?= $parc['parcela_id'] ?>" class="options-btn" title="Opciones">⋮</label>
                        <div class="options-menu">
                            <a href="management/edit_parcela.php?id=<?= $parc['parcela_id'] ?>" class="menu-item">📝 Editar</a>
                            
                            <?php if ($user_rol === 'root'): ?>
                                <a href="dashboard.php?confirmar_borrar_parc=1&id=<?= $parc['parcela_id'] ?>&localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?><?= $url_query_cliente ?>" class="menu-item delete-opt">🗑️ Borrar Parcela</a>
                            <?php else: ?>
                                <small style="display:block; padding: 10px; color: var(--color-error); font-size: 0.7rem; border-top: 1px solid var(--border-color); font-weight: 600;">
                                    🔒 Solo Root puede borrar parcelas.
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <span class="status">ID #<?= $parc['parcela_id'] ?></span>
                <h3><?= htmlspecialchars($parc['nombre'] ?: $parc['direccion']) ?></h3>
                <div class="meta">📍 Dirección: <?= htmlspecialchars($parc['direccion']) ?></div>
                <div class="meta">📋 Ref. Catastral: <?= htmlspecialchars($parc['ref_catastral']) ?></div>
                <div class="meta">🌱 <?= $parc['num_invernaderos'] ?> Invernaderos</div>
                <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc['parcela_id'] ?><?= $url_query_cliente ?>"
                    class="card-btn">Ver Invernaderos →</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($vista_actual === 'invernaderos'): ?>
        <?php if (empty($invernaderos_data)): ?>
            <div class="card empty-state">
                <p>No hay invernaderos en esta parcela.</p>
            </div>
        <?php endif; ?>
        <?php foreach ($invernaderos_data as $inv): 
            $is_target = (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $inv['invernadero_id']);
        ?>
            <div id="inv-card-<?= $inv['invernadero_id'] ?>" class="card <?= $is_target ? 'highlight-glow' : '' ?>" style="<?= $is_target ? 'border-color: var(--color-primary);' : '' ?>">
                <?php if ($es_admin || $user_rol === 'cliente'): ?>
                    <div class="card-options">
                        <input type="checkbox" id="menu-inv-<?= $inv['invernadero_id'] ?>" class="menu-toggle">
                        <label for="menu-inv-<?= $inv['invernadero_id'] ?>" class="options-btn" title="Opciones">⋮</label>
                        <div class="options-menu">
                            <a href="management/edit_invernadero.php?id=<?= $inv['invernadero_id'] ?>" class="menu-item">📝 Editar</a>
                            
                            <?php if ($es_admin): ?>
                                <a href="dashboard.php?confirmar_borrar_inv=1&id=<?= $inv['invernadero_id'] ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?><?= $url_query_cliente ?>" class="menu-item delete-opt">🗑️ Borrar Invernadero</a>
                            <?php else: ?>
                                <small style="display:block; padding: 10px; color: var(--color-error); font-size: 0.7rem; border-top: 1px solid var(--border-color); font-weight: 600;">
                                    🔒 Solo Administradores pueden borrar invernaderos.
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <span class="status">ACTIVO</span>
                <h3><?= htmlspecialchars($inv['nombre']) ?></h3>
                <div class="meta">📏 <?= htmlspecialchars($inv['largo_m']) ?>m × <?= htmlspecialchars($inv['ancho_m']) ?>m
                </div>
                <div class="meta" style="display: flex; align-items: center; gap: 8px; min-height: 28px;">
                    <span>🌾 Cultivo:</span>
                    <?php 
                    $plant_inv_id = isset($_GET['plant_inv_id']) ? (int)$_GET['plant_inv_id'] : null;
                    if ($plant_inv_id === (int)$inv['invernadero_id']): 
                    ?>
                        <!-- MODO SIEMBRA RÁPIDA -->
                        <form method="POST" style="display: flex; gap: 6px; align-items: center; margin: 0;">
                            <input type="hidden" name="invernadero_id" value="<?= $inv['invernadero_id'] ?>">
                            <select name="cultivo_id" style="background: rgba(52, 211, 153, 0.1); color: #34d399; border: 1px solid var(--color-primary); border-radius: 6px; padding: 1px 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer; height: 24px;">
                                <option value="0">-- Barbecho --</option>
                                <?php foreach ($lista_cultivos_siembra as $c): ?>
                                    <option value="<?= $c['cultivo_id'] ?>" <?= ($inv['cultivo'] == $c['nombre_cultivo']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nombre_cultivo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="btn_quick_plant" value="1" style="background: none; border: none; cursor: pointer; font-size: 1rem; line-height: 1;" title="Confirmar">✅</button>
                            <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?><?= $url_query_cliente ?>#inv-card-<?= $inv['invernadero_id'] ?>" style="text-decoration: none; font-size: 0.9rem;" title="Cancelar">❌</a>
                        </form>
                    <?php else: ?>
                        <!-- MODO LECTURA + BOTÓN RÁPIDO -->
                        <div style="display: flex; justify-content: space-between; align-items: center; flex: 1;">
                            <strong style="color: <?= $inv['cultivo'] ? '#34d399' : 'var(--color-text-muted)' ?>; font-size: 0.9rem;">
                                <?= htmlspecialchars($inv['cultivo'] ?? 'Sin asignar') ?>
                            </strong>
                            <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&plant_inv_id=<?= $inv['invernadero_id'] ?><?= $url_query_cliente ?>#inv-card-<?= $inv['invernadero_id'] ?>" 
                               style="text-decoration: none; font-size: 0.7rem; color: var(--color-primary); font-weight: 800; text-transform: uppercase; background: rgba(52, 211, 153, 0.05); padding: 2px 8px; border-radius: 4px; border: 1px solid rgba(52, 211, 153, 0.1); transition: all 0.2s;"
                               onmouseover="this.style.background='var(--color-primary)'; this.style.color='white';"
                               onmouseout="this.style.background='rgba(52, 211, 153, 0.05)'; this.style.color='var(--color-primary)';"
                               title="Click para plantar o cambiar de cultivo rápidamente">
                               🌱 Plantar
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $cliente_id_seleccionado ? '&cliente_id=' . $cliente_id_seleccionado : '' ?>"
                    class="card-btn">Panel IoT →</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
