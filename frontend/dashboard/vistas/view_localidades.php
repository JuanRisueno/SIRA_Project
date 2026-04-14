<?php
/**
 * view_localidades.php - Gestión Administrativa de Municipios y Provincias
 * Formato de lista (tabla) exclusiva para Admin/Root.
 */
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

    <table class="sira-table">
        <thead>
            <tr>
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
                    <td><span class="badge" style="letter-spacing: 1px;"><?= htmlspecialchars($loc['codigo_postal']) ?></span></td>
                    <td><strong><?= htmlspecialchars($loc['municipio']) ?></strong></td>
                    <td><?= htmlspecialchars($loc['provincia']) ?></td>
                    <td style="text-align: center;">
                        <span style="color: <?= $loc['num_parcelas'] == 0 ? 'var(--color-error)' : 'inherit' ?>; font-weight: <?= $loc['num_parcelas'] == 0 ? 'bold' : 'normal' ?>;">
                            <?= $loc['num_parcelas'] ?>
                        </span>
                    </td>
                    <td style="text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                        <!-- Botón Editar -->
                        <a href="management/edit_localidad.php?cp=<?= urlencode($loc['codigo_postal']) ?>" class="mini-btn-opt" title="Editar Localidad">
                            📝
                        </a>
                        
                        <!-- Botón Borrar (Borrado físico) -->
                        <a href="dashboard.php?confirmar_borrar_loc=1&cp=<?= urlencode($loc['codigo_postal']) ?>" 
                           class="mini-btn-opt delete-opt" 
                           title="Eliminar Localidad">
                            🗑️
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (empty($todas_las_localidades)): ?>
        <div style="text-align: center; padding: 4rem; background: var(--color-bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color); margin-top: 1rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📍</div>
            <h3 style="color: var(--color-text-main);">No hay localidades registradas</h3>
            <p style="color: var(--color-text-muted);">El catálogo está vacío actualmente.</p>
        </div>
    <?php endif; ?>
</div>
