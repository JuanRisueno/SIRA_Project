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
                <?php if ($es_admin): ?>
                    <div class="card-options">
                        <button class="options-btn" title="Opciones">⋮</button>
                        <div class="options-menu">
                            <button onclick="alert('Editar localidad')">📝 Editar</button>
                            <button class="delete-opt" onclick="alert('Desactivar localidad')">👁️‍🗨️ Ocultar</button>
                        </div>
                    </div>
                <?php endif; ?>

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
                <?php if ($es_admin): ?>
                    <div class="card-options">
                        <button class="options-btn" title="Opciones">⋮</button>
                        <div class="options-menu">
                            <button onclick="alert('Editar parcela')">📝 Editar</button>
                            <button class="delete-opt" onclick="alert('Desactivar parcela')">👁️‍🗨️ Ocultar</button>
                        </div>
                    </div>
                <?php endif; ?>

                <span class="status">ID <?= $parc['parcela_id'] ?></span>
                <h3><?= htmlspecialchars($parc['direccion']) ?></h3>
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
                <?php if ($es_admin): ?>
                    <div class="card-options">
                        <button class="options-btn" title="Opciones">⋮</button>
                        <div class="options-menu">
                            <button onclick="alert('Editar invernadero')">📝 Editar</button>
                            <button class="delete-opt" onclick="alert('Desactivar invernadero')">👁️‍🗨️ Ocultar</button>
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
