<?php
/**
 * view_all_parcelas.php - Listado maestro de parcelas (Formato Tarjetas Horizontales V6.6)
 */
?>

<div class="parc-cards-container" style="display: flex; flex-direction: column; gap: 1rem; width: 100%; max-width: 1000px; margin: 0 auto;">
    
    <?php if (empty($todas_las_parcelas)): ?>
        <div class="user-form-container card" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">🚜</div>
            <p>No tienes parcelas registradas aún en tu cuenta.</p>
        </div>
    <?php else: ?>
        <?php foreach ($todas_las_parcelas as $parc): 
            $is_target = (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $parc['parcela_id']);
        ?>
            <div id="parcela-card-<?= $parc['parcela_id'] ?>" 
                 class="parc-horizontal-card <?= $is_target ? 'highlight-glow' : '' ?>" 
                 style="background: var(--color-bg-card); border: 1px solid <?= $is_target ? 'var(--color-primary)' : 'var(--border-color)' ?>; border-radius: 16px; padding: 1.5rem; transition: transform 0.2s, border-color 0.2s; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: relative; overflow: hidden;">
                
                <!-- NIVEL 1: IDENTIDAD Y UBICACIÓN -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                    
                    <!-- IZQUIERDA: Identidad de la Finca -->
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 250px; flex: 1.5;">
                        <span style="font-size: 2rem; filter: drop-shadow(0 0 5px rgba(52, 211, 153, 0.2));">🚜</span>
                        <div style="display: flex; flex-direction: column; width: 100%;">
                            <?php 
                            $edit_parc_id = isset($_GET['edit_parc_id']) ? (int)$_GET['edit_parc_id'] : null;
                            if ($edit_parc_id === (int)$parc['parcela_id']): 
                            ?>
                                <!-- MODO EDICIÓN RÁPIDA -->
                                <form method="POST" style="display: flex; gap: 8px; align-items: center;">
                                    <input type="hidden" name="parcela_id" value="<?= $parc['parcela_id'] ?>">
                                    <input type="text" name="nuevo_nombre" value="<?= htmlspecialchars($parc['nombre'] ?: 'Finca #' . $parc['parcela_id']) ?>" 
                                           style="font-size: 1.1rem; color: var(--color-primary); background: rgba(52, 211, 153, 0.1); border: 1px solid var(--color-primary); padding: 4px 10px; border-radius: 8px; font-weight: 700; width: 100%; max-width: 250px;" 
                                           autofocus onfocus="this.select();">
                                    <button type="submit" name="btn_quick_rename_parc" value="1" style="background: none; border: none; cursor: pointer; font-size: 1.2rem;" title="Guardar">✅</button>
                                    <a href="dashboard.php?seccion=mis_parcelas<?= $url_query_cliente ?>" style="text-decoration: none; font-size: 1.2rem;" title="Cancelar">❌</a>
                                </form>
                            <?php else: ?>
                                <!-- MODO LECTURA + DISPARADOR -->
                                <a href="dashboard.php?seccion=mis_parcelas&edit_parc_id=<?= $parc['parcela_id'] ?><?= $url_query_cliente ?>" 
                                   style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 8px;"
                                   title="Clic para renombrar rápidamente">
                                    <strong style="font-size: 1.35rem; color: var(--color-primary); letter-spacing: -0.02em; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                                        <?= htmlspecialchars($parc['nombre'] ?: 'Finca #' . $parc['parcela_id']) ?>
                                    </strong>
                                </a>
<?php endif; ?>
                            
                            <span style="font-size: 0.8rem; color: var(--color-text-muted); opacity: 0.8;">
                                📍 <?= htmlspecialchars($parc['direccion'] ?: 'Sin dirección registrada') ?>
                            </span>
                        </div>
                    </div>

                    <!-- DERECHA: Datos Geográficos -->
                    <div style="display: flex; flex-direction: column; align-items: flex-end; text-align: right; flex: 1; min-width: 200px;">
                        <div style="font-size: 1.1rem; color: var(--color-text-main); font-weight: 700;">
                            <?= htmlspecialchars($parc['localidad']['municipio'] ?? 'Desconocida') ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--color-text-muted); display: flex; align-items: center; gap: 5px;">
                            <span style="opacity: 0.5;">C.P.</span> 
                            <span style="font-family: monospace; letter-spacing: 1px; font-weight: 600;"><?= htmlspecialchars($parc['localidad']['codigo_postal'] ?? '-') ?></span>
                            <span style="opacity: 0.3;">|</span> 
                            <span><?= htmlspecialchars($parc['localidad']['provincia'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 2: INFRAESTRUCTURA Y ACCIONES -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                    
                    <div style="display: flex; align-items: center; gap: 2.5rem;">
                        <!-- Invernaderos -->
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="background: rgba(52, 211, 153, 0.1); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(52, 211, 153, 0.15);">
                                <span style="font-size: 1.15rem;">🏢</span>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 0.65rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Infraestructura</span>
                                <span style="font-size: 1rem; font-weight: 700; color: #34d399; line-height: 1;">
                                    <?= count($parc['invernaderos'] ?? []) ?> Invernaderos
                                </span>
                            </div>
                        </div>

                        <!-- Catastro -->
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="background: rgba(255,255,255,0.03); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.05);">
                                <span style="font-size: 1.15rem;">📋</span>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 0.65rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Ref. Catastral</span>
                                <span style="font-size: 0.95rem; font-family: monospace; font-weight: 600; color: var(--color-text-main); line-height: 1; letter-spacing: 0.5px;">
                                    <?= htmlspecialchars($parc['ref_catastral'] ?: 'NO DISPONIBLE') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Botonera -->
                    <div style="display: flex; gap: 0.8rem;">
                        <a href="management/edit_parcela.php?id=<?= $parc['parcela_id'] ?>&from=lista" 
                           class="btn-sira btn-secondary btn-sm" 
                           style="display: inline-flex; align-items: center; gap: 8px; padding: 0.75rem 1.2rem; border-radius: 10px; border-color: rgba(255,255,255,0.08); font-weight: 600;">
                            ⚙️ Ajustes
                        </a>
                        <a href="dashboard.php?localidad_cp=<?= urlencode($parc['localidad']['codigo_postal'] ?? '') ?>&parcela_id=<?= $parc['parcela_id'] ?><?= $url_query_cliente ?>" 
                           class="btn-sira btn-primary btn-sm" 
                           style="display: inline-flex; align-items: center; gap: 8px; padding: 0.75rem 1.4rem; border-radius: 10px; box-shadow: 0 4px 15px var(--color-primary-glow); font-weight: 700; background: linear-gradient(135deg, var(--color-primary), #10b981);">
                            Invernaderos
                        </a>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
/* Marcado visual estático para item seleccionado (V9.0 UX) */
.highlight-glow {
    border-color: var(--color-primary) !important;
    border-width: 2px !important;
    box-shadow: 0 0 20px var(--color-primary-glow) !important;
}

.parc-horizontal-card:hover {
    transform: translateY(-4px);
    border-color: var(--color-primary) !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
}

.parc-horizontal-card:hover .parc-identity strong {
    color: var(--color-primary-light);
}
</style>
