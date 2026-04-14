<?php
/**
 * view_infrastructure.php - Vista clásica de infraestructura (Legacy compatible)
 */
?>

<div class="inv-cards-container" style="display: flex; flex-direction: column; gap: 1rem; width: 100%; max-width: 1000px; margin: 0 auto;">
    <?php foreach ($envolvente_invernaderos as $inv): ?>
        <div id="inv-card-<?= $inv['invernadero_id'] ?>" class="user-form-container card" style="padding: 1.5rem; margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                
                <div style="display: flex; align-items: center; gap: 1rem; min-width: 200px;">
                    <span style="font-size: 2.2rem;">🏡</span>
                    <div style="display: flex; flex-direction: column;">
                        <strong style="font-size: 1.2rem; color: var(--color-primary);"><?= htmlspecialchars($inv['nombre']) ?></strong>
                        <span style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600;">ID: #<?= $inv['invernadero_id'] ?></span>
                    </div>
                </div>

                <div style="flex: 1; min-width: 250px; background: rgba(255,255,255,0.03); padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <div style="font-size: 0.65rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; margin-bottom: 0.5rem;">Ficha Técnica</div>
                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 0.8rem; color: var(--color-text-muted);">📐 Superficie</span>
                            <strong style="font-size: 1rem; color: var(--color-text-main);"><?= $inv['largo_m'] * $inv['ancho_m'] ?> m²</strong>
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 0.8rem; color: var(--color-text-muted);">📋 Parcela</span>
                            <strong style="font-size: 0.9rem; color: var(--color-text-main); font-family: monospace;"><?= htmlspecialchars($inv['parcela']['nombre'] ?: $inv['parcela']['ref_catastral']) ?></strong>
                        </div>
                    </div>
                </div>

                <div style="min-width: 200px; flex: 1; display: flex; flex-direction: column; gap: 4px;">
                    <div style="font-size: 0.65rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; margin-bottom: 4px;">Producción</div>
                    
                    <?php if (isset($_GET['plant_inv_id']) && $_GET['plant_inv_id'] == $inv['invernadero_id']): ?>
                        <form method="POST" style="display: flex; gap: 6px; align-items: center; background: rgba(52, 211, 153, 0.08); padding: 4px 8px; border-radius: 8px; border: 1px solid var(--color-primary);">
                            <input type="hidden" name="invernadero_id" value="<?= $inv['invernadero_id'] ?>">
                            <select name="cultivo_id" style="background: none; border: none; color: #34d399; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                                <option value="0">En barbecho</option>
                                <?php foreach ($lista_cultivos_siembra as $c): ?>
                                    <option value="<?= $c['cultivo_id'] ?>"><?= htmlspecialchars($c['nombre_cultivo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="btn_quick_plant" value="1" style="background: none; border: none; cursor: pointer; font-size: 1rem; line-height: 1;" title="Confirmar">✅</button>
                            <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?><?= $url_query_cliente ?>#inv-card-<?= $inv['invernadero_id'] ?>" style="text-decoration: none; font-size: 0.9rem;" title="Cancelar">❌</a>
                        </form>
                    <?php else: ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; flex: 1;">
                            <strong style="color: <?= $inv['cultivo'] ? '#34d399' : 'var(--color-text-muted)' ?>; font-size: 0.9rem;">
                                <?= htmlspecialchars($inv['cultivo'] ?? 'Sin asignar') ?>
                            </strong>
                            <a href="dashboard.php?localidad_cp=<?= urlencode($loc_seleccionada['codigo_postal']) ?>&parcela_id=<?= $parc_seleccionada['parcela_id'] ?>&plant_inv_id=<?= $inv['invernadero_id'] ?><?= $url_query_cliente ?>#inv-card-<?= $inv['invernadero_id'] ?>" 
                               class="btn-sira btn-secondary btn-sm"
                               style="font-size: 0.65rem; padding: 2px 8px; border-radius: 4px; font-weight: 800;"
                               title="Click para plantar o cambiar de cultivo rápidamente">
                                🌱 Plantar
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $cliente_id_seleccionado ? '&cliente_id=' . $cliente_id_seleccionado : '' ?>"
                    class="card-btn">Panel IoT →</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
