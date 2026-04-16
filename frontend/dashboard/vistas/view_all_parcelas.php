<?php
/**
 * view_all_parcelas.php - Listado maestro de parcelas (SIRA Standard V12.0)
 * Refactorizado para usar el sistema nativo de tarjetas premium.
 */
?>

<div class="infra-grid-container">
    
    <?php if (empty($todas_las_parcelas)): ?>
        <div class="user-form-container card" style="text-align: center; padding: 4rem; grid-column: 1 / -1;">
            <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">🚜</div>
            <p>No tienes parcelas registradas aún en tu cuenta.</p>
        </div>
    <?php else: ?>
        <?php foreach ($todas_las_parcelas as $parc): 
            $is_target = (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $parc['parcela_id']);
        ?>
            <div id="parcela-card-<?= $parc['parcela_id'] ?>" 
                 class="inv-smart-card <?= $is_target ? 'highlight-glow' : '' ?>">
                
                <!-- NIVEL 1: CABECERA DE IDENTIDAD (Nombre + ID) -->
                <div class="card-nivel-header">
                    <div class="card-title-group">
                        <h3 title="<?= htmlspecialchars($parc['nombre'] ?: 'Finca #' . $parc['parcela_id']) ?>">
                            <?= mb_convert_case($parc['nombre'] ?: 'Finca #' . $parc['parcela_id'], MB_CASE_TITLE, "UTF-8") ?>
                        </h3>
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
                        <div class="tecnico-avatar-icon">🚜</div>
                        <div class="tecnico-datos-group">
                            <span class="tecnico-label">Operativa</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong class="tecnico-valor-main"><?= count($parc['invernaderos'] ?? []) ?> Invernaderos</strong>
                                <span style="font-size: 0.65rem; background: rgba(16, 185, 129, 0.15); color: var(--color-primary); padding: 2px 8px; border-radius: 6px; font-weight: 800;">TOTAL</span>
                            </div>
                        </div>
                    </div>

                    <!-- BLOQUE DERECHO: Datos Catastrales -->
                    <div class="tecnico-datos-derecha">
                        <div class="tecnico-item-mini">
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span class="tecnico-label" style="opacity: 0.7;">Ref. Catastral</span>
                                <span style="font-size: 0.9rem; font-weight: 800; color: var(--color-text-main); font-family: 'Roboto Mono', monospace; letter-spacing: -0.01em;"><?= htmlspecialchars($parc['ref_catastral'] ?: 'S/REF') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 3: ACCIONES ESTÁNDAR SIRA -->
                <a href="dashboard.php?localidad_cp=<?= urlencode($parc['localidad']['codigo_postal'] ?? '') ?>&parcela_id=<?= $parc['parcela_id'] ?><?= $url_query_cliente ?>" 
                   class="stretched-link"></a>

                <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 10;">
                    <div>
                        <a href="management/edit_parcela.php?id=<?= $parc['parcela_id'] ?>&from=lista" class="btn-sira btn-secondary" style="padding: 6px 14px; font-size: 0.75rem;">
                            ⚙️ <span>Editar</span>
                        </a>
                    </div>
                    <div style="text-align: right;">
                        <span class="list-subtitle" style="font-size: 0.7rem; opacity: 0.5;">GESTIONAR INVERNADEROS ➜</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
