<?php
/**
 * view_clients.php - Gestión de Agricultores (Refactorización Estándar SIRA V12.0)
 * Sincronizado con el sistema de tarjetas de infraestructura premium.
 */

// Variables de rol y permisos ya definidas en logic.php
$puede_editar = ($es_admin || $user_rol === 'root');
?>

<?php if ($vista_grid_activa): ?>
    <div class="infra-grid-container">
        <?php foreach ($todos_los_clientes as $cli): 
            // Lógica de permisos de edición
            $puede_editar = ($user_rol === 'root') || ($es_admin && $cli['rol'] === 'cliente');
        ?>
            <div class="inv-smart-card <?= !$cli['activa'] ? 'inactivo' : '' ?>">
                
                <!-- NIVEL 1: CABECERA ESTÁNDAR (Empresa + Badge Rol) -->
                <div class="card-nivel-header">
                    <div class="card-title-group">
                        <h3 title="<?= htmlspecialchars($cli['nombre_empresa']) ?>">
                            <?= htmlspecialchars($cli['nombre_empresa']) ?>
                        </h3>
                        <div class="card-subtitle">
                            <span>🏢 SIRA CLIENTE</span>
                            <span style="opacity: 0.3;">|</span>
                            <span>#<?= str_pad($cli['cliente_id'], 4, '0', STR_PAD_LEFT) ?></span>
                        </div>
                    </div>

                    <div class="badge-iot-live">
                        <span class="badge-text-premium"><?= strtoupper($cli['rol']) ?></span>
                    </div>
                </div>

                <!-- NIVEL 2: CORAZÓN TÉCNICO (Icono Identidad + Datos) -->
                <div class="card-nivel-tecnico">
                    <div class="tecnico-bloque-identidad">
                        <div class="tecnico-avatar-icon">👤</div>
                        <div class="tecnico-datos-group">
                            <span class="tecnico-label">Responsable</span>
                            <span class="tecnico-valor-main"><?= htmlspecialchars($cli['persona_contacto']) ?></span>
                        </div>
                    </div>

                    <div class="tecnico-datos-derecha">
                        <div class="tecnico-item-mini">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span class="tecnico-label">CIF / NIF</span>
                                <span style="font-size: 0.9rem; font-weight: 800; color: var(--color-text-main); font-family: monospace;"><?= htmlspecialchars($cli['cif']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 3: ACCIONES ESTÁNDAR (Navegación + Gestión) -->
                <?php 
                    // El enlace principal de la tarjeta depende del rol
                    // Clientes -> Dashboard | Root/Admin -> Editar
                    $url_principal = ($cli['rol'] === 'cliente') 
                        ? "dashboard.php?cliente_id=" . $cli['cliente_id'] 
                        : "management/edit_user.php?id=" . $cli['cliente_id'];
                ?>
                <a href="<?= $url_principal ?>" class="stretched-link"></a>
                
                <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 10;">
                    <div>
                        <?php if ($puede_editar): ?>
                            <a href="management/edit_user.php?id=<?= $cli['cliente_id'] ?>" class="btn-sira btn-secondary" style="padding: 6px 14px; font-size: 0.75rem;">
                                ⚙️ <span>Editar</span>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($cli['rol'] === 'cliente'): ?>
                        <div style="text-align: right;">
                            <span class="list-subtitle" style="font-size: 0.70rem; opacity: 0.5;">ENTORNO PRODUCTIVO ➜</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <!-- VISTA LISTA (Table) - Mantiene el estándar de tablas.css -->
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
                <?php foreach ($todos_los_clientes as $cli): 
                    $puede_editar = ($user_rol === 'root') || ($es_admin && $cli['rol'] === 'cliente');
                ?>
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
                        <td><span class="list-subtitle contact-name"><?= htmlspecialchars($cli['persona_contacto']) ?></span></td>
                        <td><span class="list-badge-tech"><?= strtoupper($cli['rol']) ?></span></td>
                        <td>
                            <div class="list-data-pair">
                                <span class="list-status-dot <?= $cli['activa'] ? 'status-online' : 'status-offline' ?>"></span>
                                <span class="list-subtitle status-label"><?= $cli['activa'] ? 'ACTIVO' : 'OCULTO' ?></span>
                            </div>
                        </td>
                        <td class="table-actions-cell">
                            <div class="table-actions-wrapper">
                                <?php if ($cli['rol'] === 'cliente'): ?>
                                    <a href="dashboard.php?cliente_id=<?= $cli['cliente_id'] ?>" class="mini-btn" title="Ver Entorno">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($puede_editar): ?>
                                    <a href="management/edit_user.php?id=<?= $cli['cliente_id'] ?>" class="mini-btn-opt" title="Editar">📝</a>
                                    <?php if ($cli['activa']): ?>
                                        <a href="dashboard.php?confirmar_ocultar=1&id=<?= $cli['cliente_id'] ?>" class="mini-btn-opt delete-opt" title="Ocultar">👁️‍🗨️</a>
                                    <?php else: ?>
                                        <a href="dashboard.php?accion=activar&id=<?= $cli['cliente_id'] ?>" class="mini-btn-opt text-primary" title="Activar">👁️</a>
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
<?php if (empty($todos_los_clientes)): ?>
<div id="no-results" class="empty-state-container">
    <div class="empty-state-icon">🔍</div>
    <h3 class="empty-state-title">Sin coincidencias para "<?= htmlspecialchars($busqueda ?? '') ?>"</h3>
    <p class="empty-state-text">El sistema buscó en Nombres, Empresas y CIFs.</p>
    <a href="dashboard.php" class="card-btn empty-state-btn">Ver todos los agricultores</a>
</div>
<?php endif; ?>
