<?php
/**
 * view_infrastructure.php - Visualización de Activos
 * Renderiza Localidades, Parcelas e Invernaderos.
 */
?>

<div class="grid">
    <?php if ($vista_actual === 'localidades'): ?>
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
        <?php foreach ($invernaderos_data as $inv): ?>
            <div class="card">
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
                <div class="meta">🌾 Cultivo: <?= htmlspecialchars($inv['cultivo'] ?? 'Sin asignar') ?></div>
                <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $cliente_id_seleccionado ? '&cliente_id=' . $cliente_id_seleccionado : '' ?>"
                    class="card-btn">Panel IoT →</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
