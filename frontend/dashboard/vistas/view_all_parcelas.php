<?php
/**
 * view_all_parcelas.php - Listado maestro de parcelas (Formato Tarjetas Horizontales V6.6)
 */
?>

<div class="infra-grid-container parc-cards-container">
    
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
                 class="inv-smart-card <?= $is_target ? 'highlight-glow' : '' ?>" 
                 style="background: var(--color-bg-card); border: 1px solid <?= $is_target ? 'var(--color-primary)' : 'var(--border-color)' ?>; border-radius: var(--radius-container); padding: 1.5rem; position: relative; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; gap: 1.2rem;">
                
                <!-- NIVEL 1: CABECERA DE IDENTIDAD (Nombre + Ubicación) -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <?php 
                        $edit_parc_id = isset($_GET['edit_parc_id']) ? (int)$_GET['edit_parc_id'] : null;
                        if ($edit_parc_id === (int)$parc['parcela_id']): 
                        ?>
                            <form method="POST" style="display: flex; gap: 8px; align-items: center;">
                                <input type="hidden" name="parcela_id" value="<?= $parc['parcela_id'] ?>">
                                <input type="text" name="nuevo_nombre" value="<?= htmlspecialchars($parc['nombre'] ?: 'Finca #' . $parc['parcela_id']) ?>" 
                                       style="font-size: 1.25rem; color: var(--color-primary); background: rgba(16, 185, 129, 0.1); border: 1px solid var(--color-primary); padding: 4px 12px; border-radius: var(--radius-container); font-weight: 800; width: auto;" 
                                       autofocus>
                                <button type="submit" name="btn_quick_rename_parc" value="1" style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">✅</button>
                            </form>
                        <?php else: ?>
                            <a href="dashboard.php?seccion=mis_parcelas&edit_parc_id=<?= $parc['parcela_id'] ?><?= $url_query_cliente ?>" 
                               class="inv-name-link" style="text-decoration: none;">
                                <h3 style="margin: 0; color: var(--color-primary); font-size: 1.5rem; letter-spacing: -0.03em; font-weight: 800;"><?= mb_convert_case($parc['nombre'] ?: 'Finca #' . $parc['parcela_id'], MB_CASE_TITLE, "UTF-8") ?></h3>
                            </a>
                        <?php endif; ?>
                        
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.7rem; color: var(--color-text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8;">
                            <span>#<?= $parc['parcela_id'] ?></span>
                            <span style="opacity: 0.3;">|</span>
                            <span>📍 <?= mb_convert_case($parc['localidad']['municipio'] ?? 'S/L', MB_CASE_TITLE, "UTF-8") ?></span>
                            <span style="opacity: 0.3;">|</span>
                            <span>CP <?= $parc['localidad']['codigo_postal'] ?></span>
                        </div>
                    </div>

                    <div style="background: rgba(16, 185, 129, 0.05); padding: 4px 12px; border-radius: var(--radius-container); border: 1px solid rgba(16, 185, 129, 0.15);">
                        <span style="font-size: 0.65rem; font-weight: 900; color: var(--color-primary); letter-spacing: 0.1em;">FINCA ACTIVA</span>
                    </div>
                </div>

                <!-- NIVEL 2: CORAZÓN TÉCNICO (Distribución Balanceada) -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: rgba(255,255,255,0.02); padding: 1.2rem; border-radius: var(--radius-container); border: 1px solid rgba(255,255,255,0.03);">
                    
                    <!-- BLOQUE IZQUIERDO: Infraestructura -->
                    <div style="display: flex; align-items: center; gap: 1.2rem; flex: 1; min-width: 0;">
                        <div style="width: 66px; height: 66px; background: rgba(16, 185, 129, 0.08); border-radius: var(--radius-container); display: flex; align-items: center; justify-content: center; font-size: 2.6rem; border: 1px solid rgba(16, 185, 129, 0.1); flex-shrink: 0; transition: transform 0.3s;" class="inv-avatar-icon">
                            🚜
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 2px; overflow: hidden;">
                            <span style="font-size: 0.6rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Infraestructura</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong style="font-size: 1.15rem; color: var(--color-text-main);"><?= count($parc['invernaderos'] ?? []) ?> Invernaderos</strong>
                                <span style="font-size: 0.65rem; background: rgba(16, 185, 129, 0.15); color: var(--color-primary); padding: 2px 8px; border-radius: 6px; font-weight: 800;">TOTAL</span>
                            </div>
                            <span style="font-size: 0.65rem; color: var(--color-text-muted); font-weight: 700; opacity: 0.8;"><?= mb_convert_case($parc['direccion'] ?: 'Sin Dirección', MB_CASE_TITLE, "UTF-8") ?></span>
                        </div>
                    </div>

                    <!-- BLOQUE DERECHO: Datos Catastrales -->
                    <div style="display: flex; flex-direction: column; gap: 8px; text-align: right; flex-shrink: 0;">
                        <div style="display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span style="font-size: 0.6rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 800; opacity: 0.7;">Ref. Catastral</span>
                                <span style="font-size: 0.9rem; font-weight: 800; color: var(--color-text-main); font-family: 'Roboto Mono', monospace; letter-spacing: -0.02em;"><?= htmlspecialchars($parc['ref_catastral'] ?: 'NO DISPONIBLE') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 3: ACCIONES PREMIUM (Blindadas) -->
                <div style="display: flex; gap: 8px; margin-top: auto;">
                    <a href="dashboard.php?localidad_cp=<?= urlencode($parc['localidad']['codigo_postal'] ?? '') ?>&parcela_id=<?= $parc['parcela_id'] ?><?= $url_query_cliente ?>" 
                       class="btn-sira btn-primary" style="flex: 2; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none !important;">
                        <span>Gestionar Invernaderos</span>
                    </a>
                    
                    <a href="management/edit_parcela.php?id=<?= $parc['parcela_id'] ?>&from=lista" 
                       class="btn-sira btn-secondary" style="flex: 1; display: flex; align-items: center; justify-content: center; text-decoration: none !important;" title="Ajustes de parcela">
                        ⚙️ Ajustes
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php // Estilos locales eliminados: ahora gestionados por infra_components.css ?>
