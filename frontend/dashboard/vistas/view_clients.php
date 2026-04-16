<?php
/**
 * view_clients.php - Selector de Agricultores
 * Renderiza la Grid o la Lista según preferencia.
 */
?>

<?php if ($vista_actual === 'selector_cliente'): ?>
    <?php
    $ver_ocultos_actual = $_SESSION['ver_ocultos'] ?? false;
    $clientes_filtrados = array_filter($todos_los_clientes, function ($cli) use ($ver_ocultos_actual) {
        if ($cli['rol'] === 'root')
            return false;
        if (!$cli['activa'] && !$ver_ocultos_actual)
            return false;
        return true;
    });
    ?>

    <?php if ($vista_grid_activa): ?>
        <!-- VISTA MOSAICO (Grid) -->
        <div class="sira-grid">
            <?php foreach ($clientes_filtrados as $cli): ?>
                <div class="sira-card <?= !$cli['activa'] ? 'inactivo' : '' ?>">
                    <div class="sira-card-accent"></div>

                    <div class="sira-card-header">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="card-icon-box">🏢</div>
                            <div style="display: flex; flex-direction: column;">
                                <span class="list-subtitle" style="font-size: 0.65rem; letter-spacing: 0.05em;">ID ÚNICO</span>
                                <span class="list-title" style="font-size: 0.9rem; color: var(--color-primary);">#<?= $cli['cliente_id'] ?></span>
                            </div>
                        </div>

                        <div class="card-meta-group">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span class="list-badge-tech" style="margin-right: 4px;"><?= htmlspecialchars(strtoupper($cli['rol'])) ?></span>
                                <?php if ($es_admin): ?>
                                    <div class="card-options">
                                        <input type="checkbox" id="menu-cli-<?= $cli['cliente_id'] ?>" class="menu-toggle">
                                        <label for="menu-cli-<?= $cli['cliente_id'] ?>" class="options-btn" title="Opciones">⋮</label>
                                        <div class="options-menu">
                                            <a href="management/edit_user.php?id=<?= $cli['cliente_id'] ?>" class="menu-item">📝 Editar</a>
                                            <?php if ($cli['activa']): ?>
                                                <a href="dashboard.php?confirmar_ocultar=1&id=<?= $cli['cliente_id'] ?>" class="menu-item delete-opt">👁️‍🗨️ Ocultar</a>
                                            <?php else: ?>
                                                <a href="dashboard.php?accion=activar&id=<?= $cli['cliente_id'] ?>" class="menu-item" style="color: var(--color-primary);">👁️ Activar</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="sira-card-body">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <h3 class="card-title" style="font-size: 1.5rem; line-height: 1.2;" title="<?= htmlspecialchars($cli['nombre_empresa']) ?>">
                                <?= htmlspecialchars($cli['nombre_empresa']) ?>
                            </h3>
                            <div class="card-meta-line" style="color: var(--color-text-muted);">
                                <span>📄 CIF:</span>
                                <span style="font-family: monospace; letter-spacing: 1px;"><?= htmlspecialchars($cli['cif']) ?></span>
                            </div>
                        </div>

                        <div class="card-divider"></div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span class="list-subtitle" style="font-size: 0.6rem;">ENTORNO PRODUCTIVO</span>
                                <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-main); opacity: 0.8;">SIRA Infraestructura v2.0</span>
                            </div>
                            <span style="font-size: 1.2rem; opacity: 0.1;">🚀</span>
                        </div>
                    </div>

                    <div class="sira-card-footer">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--color-primary-glow); display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">👤</div>
                            <div style="display: flex; flex-direction: column;">
                                <span class="list-subtitle" style="font-size: 0.6rem;">RESPONSABLE</span>
                                <span style="color: var(--color-text-main); font-weight: 700; font-size: 0.85rem;"><?= htmlspecialchars($cli['persona_contacto']) ?></span>
                            </div>
                        </div>
                        <?php if ($cli['rol'] === 'cliente'): ?>
                            <a href="dashboard.php?cliente_id=<?= $cli['cliente_id'] ?>" class="stretched-link" style="color: var(--color-primary); font-size: 1.1rem; font-weight: 900; text-decoration: none; transition: transform 0.2s;">➜</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- VISTA LISTA (Table) -->
        <div class="list-container sira-table-container">
            <table class="sira-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Empresa / Agricultor</th>
                        <th>CIF</th>
                        <th>Contacto</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes_filtrados as $cli): ?>
                        <tr class="<?= !$cli['activa'] ? 'row-inactive' : '' ?>">
                            <td><span class="list-badge-tech badge-muted"><?= $cli['cliente_id'] ?></span></td>
                            <td>
                                <div class="list-cell-main">
                                    <span class="list-main-icon">🏢</span>
                                    <div class="list-main-stack">
                                        <strong class="list-title"><?= htmlspecialchars($cli['nombre_empresa']) ?></strong>
                                        <span class="list-subtitle">Entorno Productivo</span>
                                    </div>
                                </div>
                            </td>
                            <td><code><?= htmlspecialchars($cli['cif']) ?></code></td>
                            <td><span class="list-subtitle" style="font-weight: 600;"><?= htmlspecialchars($cli['persona_contacto']) ?></span></td>
                            <td><span class="list-badge-tech"><?= strtoupper($cli['rol']) ?></span></td>
                            <td>
                                <div class="list-data-pair">
                                    <span class="list-status-dot <?= $cli['activa'] ? 'status-online' : 'status-offline' ?>"></span>
                                    <span class="list-subtitle" style="font-weight: 700;"><?= $cli['activa'] ? 'ACTIVO' : 'OCULTO' ?></span>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <?php if ($cli['rol'] === 'cliente'): ?>
                                        <a href="dashboard.php?cliente_id=<?= $cli['cliente_id'] ?>" class="mini-btn" title="Ver Entorno">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($es_admin): ?>
                                        <a href="management/edit_user.php?id=<?= $cli['cliente_id'] ?>" class="mini-btn-opt" title="Editar">📝</a>
                                        <?php if ($cli['activa']): ?>
                                            <a href="dashboard.php?confirmar_ocultar=1&id=<?= $cli['cliente_id'] ?>" class="mini-btn-opt delete-opt" title="Ocultar">👁️‍🗨️</a>
                                        <?php else: ?>
                                            <a href="dashboard.php?accion=activar&id=<?= $cli['cliente_id'] ?>" class="mini-btn-opt" style="color: var(--color-primary);" title="Activar">👁️</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Mensaje No Resultados -->
    <?php if (empty($clientes_filtrados)): ?>
    <div id="no-results" class="empty-state-container">
        <div class="empty-state-icon">🔍</div>
        <h3 style="color: var(--color-text-main);">Sin coincidencias para "<?= htmlspecialchars($busqueda ?? '') ?>"</h3>
        <p style="color: var(--color-text-muted);">El sistema buscó en Nombres, Empresas y CIFs.</p>
        <a href="dashboard.php" class="card-btn empty-state-btn">Ver todos los agricultores</a>
    </div>
    <?php endif; ?>

<?php endif; ?>
