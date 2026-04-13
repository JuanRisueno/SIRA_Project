<?php
/**
 * header.php - Cabecera del Contenido
 * Migas de pan, Título y Botones de acción.
 */
?>

<!-- Migas de pan -->
<div class="breadcrumbs">
    <span>📍 Tú estás aquí:</span>
    <a href="dashboard.php<?= $cliente_id_seleccionado ? '?cliente_id=' . $cliente_id_seleccionado : '' ?>">💼
        <?= htmlspecialchars($arbol['nombre_empresa']) ?></a>
    <?php if ($loc_seleccionada): ?>
        <span>/</span>
        <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?><?= $url_query_cliente ?>">
            <?= htmlspecialchars($loc_seleccionada['municipio']) ?>
        </a>
    <?php endif; ?>
    <?php if ($parc_seleccionada): ?>
        <span>/</span>
        <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?><?= $url_query_cliente ?>">
            Parcela <?= htmlspecialchars($parc_seleccionada['ref_catastral']) ?>
        </a>
    <?php endif; ?>
</div>

<!-- Título y Acciones -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 class="dashboard-title" style="margin-bottom: 0.5rem;">
            <?php
            if ($vista_actual === 'selector_cliente') {
                echo ($_SESSION['user_rol'] === 'root') ? "Control Global de Infraestructura" : "Lista de Agricultores";
            } elseif ($vista_actual === 'gestion_localidades') {
                echo "Catálogo de Localidades";
            } elseif ($vista_actual === 'localidades')
                echo "Tus Zonas Geográficas";
            elseif ($vista_actual === 'parcelas')
                echo "Parcelas en " . htmlspecialchars($loc_seleccionada['municipio']);
            else {
                $dir_limpia = explode(' - ', $parc_seleccionada['direccion'] ?? '')[0];
                echo "Invernaderos en " . htmlspecialchars($loc_seleccionada['municipio'] ?? '') . " — " . htmlspecialchars($dir_limpia);
            }
            ?>
        </h1>
        <p class="dashboard-subtitle">
            <?php
            if ($vista_actual === 'selector_cliente') {
                echo ($_SESSION['user_rol'] === 'root') ? "Acceso total a clientes y administradores del sistema." : "Selecciona un cliente para supervisar su actividad.";
            } elseif ($vista_actual === 'gestion_localidades') {
                echo "Supervisa y edita el listado global de municipios y provincias del sistema.";
            } else
                echo "Selecciona un elemento para navegar por tu infraestructura.";
            ?>
        </p>
    </div>

    <div style="display: flex; gap: 12px; align-items: center;">
        <?php if ($vista_actual === 'selector_cliente' && $es_admin): ?>
            <a href="dashboard.php?toggle_view=1" class="btn-sira btn-secondary btn-sm">
                <?php if ($vista_grid_activa): ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    Vista Lista
                <?php else: ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Vista Mosaico
                <?php endif; ?>
            </a>

            <a href="management/add_user.php" class="btn-sira btn-primary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="17" y1="11" x2="23" y2="11"></line></svg>
                Añadir Nuevo Usuario
            </a>

            <a href="dashboard.php?seccion=localidades" class="btn-sira btn-secondary btn-sm" title="Gestionar municipios y provincias">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                Localidades
            </a>

            <?php if ($_SESSION['user_rol'] === 'root'): ?>
                <?php $ver_ocultos = $_SESSION['ver_ocultos'] ?? false; ?>
                <a href="dashboard.php?toggle_ocultos=1" class="btn-sira <?= $ver_ocultos ? 'confirm-btn-yes' : 'btn-primary' ?> btn-sm">
                    <?php if ($ver_ocultos): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Ocultar Inactivos
                    <?php else: ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        Ver Ocultos
                    <?php endif; ?>
                </a>
            <?php endif; ?>

        <?php elseif ($vista_actual === 'gestion_localidades' && $es_admin): ?>
            <a href="management/add_localidad.php" class="btn-sira btn-primary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Añadir Localidad
            </a>
            <a href="dashboard.php" class="btn-sira btn-secondary btn-sm">
                Volver al Panel
            </a>

        <?php elseif ($cliente_id_seleccionado): ?>
            <!-- Acciones de Entorno (Admin o Cliente) -->
            <?php if ($es_admin): ?>
                <?php if ($vista_actual === 'invernaderos'): ?>
                    <a href="management/add_invernadero.php?cliente_id=<?= $cliente_id_seleccionado ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>" class="btn-sira btn-primary btn-sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10v11h18V10l-9-7z"></path><path d="M9 21v-8h6v8"></path></svg>
                        Añadir Invernadero
                    </a>
                <?php endif; ?>

                <a href="management/add_parcela.php?cliente_id=<?= $cliente_id_seleccionado ?>" class="btn-sira btn-primary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M11 18l-2-1-2 1V6l2-1 2 1 2-1 2 1v7"></path><path d="M3 6l2-1 2 1"></path><path d="M15 11l2 1 2-1"></path><path d="M19 16v6"></path><path d="M16 19h6"></path></svg>
                    <?= ($vista_actual === 'invernaderos') ? 'Añadir Otra Parcela' : 'Añadir Parcela' ?>
                </a>
            <?php endif; ?>

            <?php if (in_array($vista_actual, ['parcelas', 'invernaderos'])): ?>
                <a href="dashboard.php<?= $cliente_id_seleccionado ? '?cliente_id=' . $cliente_id_seleccionado : '' ?>" class="btn-sira btn-secondary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    Mi Entorno
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
