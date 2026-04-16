<?php
/**
 * view_all_parcelas.php - Listado maestro de parcelas (Formato Tarjetas Horizontales V6.6)
 */
?>

<div class="infra-grid-container parc-cards-container">
    
    <?php if (empty($todas_las_parcelas)): ?>
        <div class="user-form-container card" style="text-align: center; padding: 4rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">🚜</div>
            <p>No tienes parcelas registradas aún en tu cuenta.</p>
        </div>
    <?php else: ?>
        <?php foreach ($todas_las_parcelas as $parc): 
            $is_target = (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $parc['parcela_id']);
        ?>
            <div id="parcela-card-<?= $parc['parcela_id'] ?>" 
                 class="inv-smart-card <?= $is_target ? 'highlight-glow' : '' ?>">
                
                <!-- NIVEL 1: CABECERA DE IDENTIDAD (Nombre + Ubicación) -->
                <div class="card-nivel-header">
                    <div class="card-title-group">
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
                                <h3><?= mb_convert_case($parc['nombre'] ?: 'Finca #' . $parc['parcela_id'], MB_CASE_TITLE, "UTF-8") ?></h3>
                            </a>
                        <?php endif; ?>
                        
                        <div class="card-subtitle">
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

                <!-- NIVEL 2: CORAZÓN TÉCNICO -->
                <div class="card-nivel-tecnico">
                    
                    <!-- BLOQUE IZQUIERDO: Infraestructura -->
                    <div class="tecnico-bloque-identidad">
                        <div class="tecnico-avatar-icon">
                            🚜
                        </div>

                        <div class="tecnico-datos-group">
                            <span class="tecnico-label">Infraestructura</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong class="tecnico-valor-main"><?= count($parc['invernaderos'] ?? []) ?> Invernaderos</strong>
                                <span style="font-size: 0.65rem; background: rgba(16, 185, 129, 0.15); color: var(--color-primary); padding: 2px 8px; border-radius: 6px; font-weight: 800;">TOTAL</span>
                            </div>
                            <span style="font-size: 0.65rem; color: var(--color-text-muted); font-weight: 700; opacity: 0.8;"><?= mb_convert_case($parc['direccion'] ?: 'Sin Dirección', MB_CASE_TITLE, "UTF-8") ?></span>
                        </div>
                    </div>

                    <!-- BLOQUE DERECHO: Datos Catastrales -->
                    <div class="tecnico-datos-derecha">
                        <div class="tecnico-item-mini">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span class="tecnico-label" style="opacity: 0.7;">Ref. Catastral</span>
                                <span style="font-size: 0.9rem; font-weight: 800; color: var(--color-text-main); font-family: 'Roboto Mono', monospace; letter-spacing: -0.02em;"><?= htmlspecialchars($parc['ref_catastral'] ?: 'NO DISPONIBLE') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 3: ACCIONES PREMIUM -->
                <div class="card-nivel-acciones">
                    <a href="dashboard.php?localidad_cp=<?= urlencode($parc['localidad']['codigo_postal'] ?? '') ?>&parcela_id=<?= $parc['parcela_id'] ?><?= $url_query_cliente ?>" 
                       class="btn-sira btn-primary" style="flex: 2; text-decoration: none !important;">
                        <span>Gestionar Invernaderos</span>
                    </a>
                    
                    <a href="management/edit_parcela.php?id=<?= $parc['parcela_id'] ?>&from=lista" 
                       class="btn-sira btn-secondary" style="flex: 1; text-decoration: none !important;" title="Ajustes de parcela">
                        ⚙️ Ajustes
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php // Estilos locales eliminados: ahora gestionados por infra_components.css ?>
