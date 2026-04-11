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
        <div class="grid">
            <?php foreach ($clientes_filtrados as $cli): ?>
                <div class="card" style="<?= !$cli['activa'] ? 'opacity: 0.5; border-style: dashed;' : '' ?>">
                    <?php if ($es_admin): ?>
                        <div class="card-options">
                            <button class="options-btn" title="Opciones">⋮</button>
                            <div class="options-menu">
                                <button onclick="alert('Editar cliente')">📝 Editar</button>
                                <?php if ($cli['activa']): ?>
                                    <a href="dashboard.php?confirmar_ocultar=1&id=<?= $cli['cliente_id'] ?>" class="delete-opt">👁️‍🗨️
                                        Ocultar</a>
                                <?php else: ?>
                                    <a href="dashboard.php?accion=activar&id=<?= $cli['cliente_id'] ?>"
                                        style="color: var(--color-primary);">👁️ Activar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <span class="status">ID <?= $cli['cliente_id'] ?> | <?= htmlspecialchars(strtoupper($cli['rol'])) ?></span>
                    <?php if (!$cli['activa']): ?>
                        <span class="status" style="right: 170px; background: #ef4444; color: white;">INACTIVO</span>
                    <?php endif; ?>

                    <h3><?= htmlspecialchars($cli['nombre_empresa']) ?></h3>
                    <div class="meta">🏢 CIF: <?= htmlspecialchars($cli['cif']) ?></div>
                    <div class="meta">👤 <?= htmlspecialchars($cli['persona_contacto']) ?></div>
                    <a href="dashboard.php?cliente_id=<?= $cli['cliente_id'] ?>" class="card-btn">Ver Entorno →</a>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- VISTA LISTA (Table) -->
        <div class="list-container">
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
                            <td><span class="badge"><?= $cli['cliente_id'] ?></span></td>
                            <td><strong><?= htmlspecialchars($cli['nombre_empresa']) ?></strong></td>
                            <td><code><?= htmlspecialchars($cli['cif']) ?></code></td>
                            <td><?= htmlspecialchars($cli['persona_contacto']) ?></td>
                            <td><span class="badge-rol"><?= strtoupper($cli['rol']) ?></span></td>
                            <td>
                                <?php if ($cli['activa']): ?>
                                    <span class="dot-active" title="Activo"></span> Activo
                                <?php else: ?>
                                    <span class="dot-inactive" title="Oculto"></span> Oculto
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="dashboard.php?cliente_id=<?= $cli['cliente_id'] ?>" class="mini-btn"
                                    title="Ver Entorno">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14"></path>
                                        <path d="M12 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                <?php if ($es_admin): ?>
                                    <button class="mini-btn-opt" onclick="alert('Editar')" title="Editar">📝</button>
                                    <?php if ($cli['activa']): ?>
                                        <a href="dashboard.php?confirmar_ocultar=1&id=<?= $cli['cliente_id'] ?>"
                                            class="mini-btn-opt delete-opt" title="Ocultar">👁️‍🗨️</a>
                                    <?php else: ?>
                                        <a href="dashboard.php?accion=activar&id=<?= $cli['cliente_id'] ?>" class="mini-btn-opt"
                                            style="color: var(--color-primary);" title="Activar">👁️</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Mensaje No Resultados -->
    <?php if (empty($clientes_filtrados)): ?>
    <div id="no-results" style="grid-column: 1 / -1; text-align: center; padding: 4rem; background: var(--color-bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color); margin-top: 1rem;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
        <h3 style="color: var(--color-text-main);">Sin coincidencias para "<?= htmlspecialchars($busqueda ?? '') ?>"</h3>
        <p style="color: var(--color-text-muted);">El sistema buscó en Nombres, Empresas y CIFs.</p>
        <a href="dashboard.php" class="card-btn" style="margin-top: 1.5rem; width: auto; display: inline-flex; background: var(--color-primary); color: #000;">Ver todos los agricultores</a>
    </div>
    <?php endif; ?>

<?php endif; ?>
