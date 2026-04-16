<?php
/**
 * view_clients.php - Gestión de Agricultores (Refactorización Estándar SIRA V12.0)
 * Sincronizado con el sistema de tarjetas de infraestructura premium.
 */

// Variables de rol y permisos ya definidas en logic.php
$puede_editar = ($es_admin || $user_rol === 'root');
?>

<?php 
// 1. Lógica de Filtrado Exclusivo
$ver_ocultos = $_SESSION['ver_ocultos'] ?? false;
$todos_los_clientes = array_filter($todos_los_clientes, function($c) use ($ver_ocultos) {
    $is_active = (bool)($c['activa'] ?? true);
    return $ver_ocultos ? !$is_active : $is_active;
});
?>

<?php if ($vista_grid_activa): ?>
    <div class="infra-grid-container">
        <?php foreach ($todos_los_clientes as $cli): 
            // Lógica de permisos de edición
            $puede_editar = ($user_rol === 'root') || ($es_admin && $cli['rol'] === 'cliente');
        ?>
            <div class="inv-smart-card <?= !$cli['activa'] ? 'sira-item-archived' : '' ?>">
                
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
                        : "formularios/formulario_usuario.php?id=" . $cli['cliente_id'];
                ?>
                <a href="<?= $url_principal ?>" class="stretched-link"></a>
                
                <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 10;">
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <?php if ($puede_editar): ?>
                            <!-- Icono de Gestión de Estado (🗑️ / 👁️) -->
                            <?php if ($cli['activa']): ?>
                                <a href="dashboard.php?confirmar_ocultar=1&id=<?= $cli['cliente_id'] ?>#cli-card-<?= $cli['cliente_id'] ?>" 
                                   class="mini-btn-opt" style="color: var(--color-warning);" title="Archivar/Ocultar Agricultor">
                                    🗑️
                                </a>
                            <?php else: ?>
                                <a href="dashboard.php?accion=activar&id=<?= $cli['cliente_id'] ?>#cli-card-<?= $cli['cliente_id'] ?>" 
                                   class="mini-btn-opt" style="color: var(--color-primary);" title="Restaurar/Mostrar Agricultor">
                                    👁️
                                </a>
                            <?php endif; ?>

                            <span style="opacity: 0.2;">|</span>

                            <a href="formularios/formulario_usuario.php?id=<?= $cli['cliente_id'] ?>" class="btn-sira btn-secondary" style="padding: 6px 14px; font-size: 0.75rem;">
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
                    <th style="width: 50px;"></th>
                    <th>ID</th>
                    <th>Empresa / Agricultor</th>
                    <th>CIF</th>
                    <th>Contacto</th>
                    <th>Rol</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todos_los_clientes as $cli): 
                    $puede_editar = ($user_rol === 'root') || ($es_admin && $cli['rol'] === 'cliente');
                ?>
                    <tr class="<?= !$cli['activa'] ? 'sira-item-archived' : '' ?>">
                        <td style="text-align: center;">
                             <?php if ($puede_editar): ?>
                                <?php if ($cli['activa']): ?>
                                    <a href="dashboard.php?confirmar_ocultar=1&id=<?= $cli['cliente_id'] ?>" class="mini-btn-opt" style="color: var(--color-warning); font-size: 1.1rem;" title="Archivar">🗑️</a>
                                <?php else: ?>
                                    <a href="dashboard.php?accion=activar&id=<?= $cli['cliente_id'] ?>" class="mini-btn-opt" style="color: var(--color-primary); font-size: 1.1rem;" title="Restaurar">👁️</a>
                                <?php endif; ?>
                             <?php endif; ?>
                        </td>
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
                                    <a href="formularios/formulario_usuario.php?id=<?= $cli['cliente_id'] ?>" class="mini-btn-opt" title="Editar">📝</a>
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
<div class="card empty-state-premium" style="padding: 3.5rem 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; background: rgba(255, 255, 255, 0.02); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: var(--radius-container); margin: 2rem 0;">
    
    <div class="empty-visual-wrapper" style="margin-bottom: 1.5rem; position: relative;">
        <span style="font-size: 3.8rem; display: block; filter: drop-shadow(0 8px 15px rgba(0,0,0,0.4));"><?= $ver_ocultos ? '📂' : '👥' ?></span>
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70px; height: 70px; background: var(--color-primary); filter: blur(35px); opacity: 0.15; z-index: -1;"></div>
    </div>

    <div class="empty-text-stack" style="margin-bottom: 2rem; max-width: 650px;">
        <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--color-text-main); margin-bottom: 0.5rem; letter-spacing: -0.02em;">
            <?= $ver_ocultos ? 'No hay agricultores archivados' : ($busqueda ? 'Sin coincidencias de búsqueda' : 'Registro de clientes vacío') ?>
        </h2>
        
        <p style="opacity: 0.6; line-height: 1.6; font-size: 1rem; font-weight: 500;">
            <?= $ver_ocultos 
                ? 'Actualmente todos los registros de clientes y administradores se encuentran activos en el sistema principal.' 
                : ($busqueda 
                    ? 'No se han encontrado registros que coincidan con "<span style="color: var(--color-primary);">' . htmlspecialchars($busqueda) . '</span>". Pruebe con otros términos o revise la ortografía.' 
                    : 'Aún no se han registrado agricultores en la base de datos global de SIRA.') ?>
        </p>
    </div>

    <div class="empty-actions-row" style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
        <a href="dashboard.php?reset_ocultos=1" class="btn-sira btn-primary" style="padding: 0.8rem 2.2rem; font-weight: 700; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.02em;">
            Ver todos los activos
        </a>
        
        <?php if (!$ver_ocultos && ($es_admin || $user_rol === 'root')): ?>
             <a href="dashboard.php?toggle_ocultos=1" class="btn-sira btn-secondary" style="padding: 0.8rem 2.2rem; background: rgba(255,255,255,0.05); font-weight: 700; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.02em;">
                📂 Consultar Histórico
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
