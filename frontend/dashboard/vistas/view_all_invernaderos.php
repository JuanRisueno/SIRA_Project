<?php
/**
 * view_all_invernaderos.php - Listado maestro de invernaderos (V6.5 - IoT Center)
 */
?>

<div class="infra-grid-container inv-cards-container">
    
    <?php if (empty($todos_los_invernaderos)): ?>
        <div class="user-form-container card" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            <p>No tienes invernaderos registrados aún en tu infraestructura.</p>
        </div>
    <?php else: ?>
        <?php foreach ($todos_los_invernaderos as $inv): 
            $is_target = (isset($_GET['plant_inv_id']) && $_GET['plant_inv_id'] == $inv['invernadero_id']) || (isset($_GET['highlight_id']) && $_GET['highlight_id'] == $inv['invernadero_id']);
        ?>
            <div id="inv-card-<?= $inv['invernadero_id'] ?>" 
                 class="inv-horizontal-card <?= $is_target ? 'highlight-glow' : '' ?>" 
                 style="background: var(--color-bg-card); border: 1px solid <?= $is_target ? 'var(--color-primary)' : 'var(--border-color)' ?>; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: relative; overflow: hidden;">
                
                <!-- NIVEL 1: IDENTIDAD, TELEMETRÍA Y UBICACIÓN -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                    
                    <!-- IZQUIERDA: Identidad -->
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 200px; flex: 1;">
                        <span style="font-size: 2rem; filter: drop-shadow(0 0 5px rgba(52, 211, 153, 0.2));">🏡</span>
                        <div style="display: flex; flex-direction: column; width: 100%;">
                            <?php 
                            $edit_inv_id = isset($_GET['edit_inv_id']) ? (int)$_GET['edit_inv_id'] : null;
                            if ($edit_inv_id === (int)$inv['invernadero_id']): 
                            ?>
                                <!-- MODO EDICIÓN RÁPIDA -->
                                <form method="POST" style="display: flex; gap: 8px; align-items: center;">
                                    <input type="hidden" name="invernadero_id" value="<?= $inv['invernadero_id'] ?>">
                                    <input type="text" name="nuevo_nombre" value="<?= htmlspecialchars($inv['nombre']) ?>" 
                                           style="font-size: 1.1rem; color: var(--color-primary); background: rgba(52, 211, 153, 0.1); border: 1px solid var(--color-primary); padding: 4px 10px; border-radius: 8px; font-weight: 700; width: 100%;" 
                                           autofocus onfocus="this.select();">
                                    <button type="submit" name="btn_quick_rename_inv" value="1" style="background: none; border: none; cursor: pointer; font-size: 1.2rem;" title="Guardar">✅</button>
                                    <a href="dashboard.php?seccion=mis_invernaderos<?= $url_query_cliente ?>" style="text-decoration: none; font-size: 1.2rem;" title="Cancelar">❌</a>
                                </form>
                            <?php else: ?>
                                <!-- MODO LECTURA + DISPARADOR -->
                                <a href="dashboard.php?seccion=mis_invernaderos&edit_inv_id=<?= $inv['invernadero_id'] ?><?= $url_query_cliente ?>" 
                                   style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 8px;"
                                   title="Clic para renombrar rápidamente">
                                    <strong style="font-size: 1.35rem; color: var(--color-primary); letter-spacing: -0.02em; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                                        <?= htmlspecialchars($inv['nombre']) ?>
                                    </strong>
                                </a>
                            <?php endif; ?>
                            
                            <span style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600; opacity: 0.6;">DASHBOARD ID: #<?= $inv['invernadero_id'] ?></span>
                        </div>
                    </div>

                    <!-- CENTRO: Seguimiento IoT (Premium Look) -->
                    <div class="inv-iot-box">
                        <span class="inv-iot-label">Seguimiento IoT</span>
                        <div class="inv-iot-status">
                            <span class="status-dot-pulse"></span>
                            <span style="font-size: 0.9rem; font-weight: 700; color: #34d399; letter-spacing: 0.02em;">Sincronizado</span>
                        </div>
                        <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $url_query_cliente ?>" 
                           class="btn-sira btn-secondary"
                           style="font-size: 0.65rem; padding: 4px 12px; border-radius: 8px; border-color: rgba(52, 211, 153, 0.3); color: var(--color-primary); font-weight: 800;"
                           title="Acceder al control de sensores">
                           ⚡ PANEL IOT
                        </a>
                    </div>

                    <!-- DERECHA: Ubicación (Unificada en 1 línea para móviles) -->
                    <div class="inv-location-container">
                        <div class="inv-location-line">
                            <span class="loc-label">🚜 Parcela:</span>
                            <strong class="loc-parcel-name"><?= htmlspecialchars($inv['parcela']['nombre'] ?: $inv['parcela']['ref_catastral']) ?></strong>
                            
                            <span class="loc-separator">|</span>
                            
                            <span class="loc-icon">📍</span>
                            <span class="loc-city"><?= htmlspecialchars($inv['parcela']['localidad']['municipio'] ?? 'Desconocido') ?></span>
                            <span class="loc-cp"><?= htmlspecialchars($inv['parcela']['localidad']['codigo_postal'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>

                <!-- NIVEL 2: ESPECIFICACIONES Y ACCIONES -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                    
                    <div class="inv-specs-container">
                        <!-- Dimensiones -->
                        <div class="inv-spec-item">
                            <div class="inv-spec-icon">
                                <span style="font-size: 1.1rem;">📐</span>
                            </div>
                            <div class="inv-spec-info">
                                <span class="inv-spec-label">Superficie</span>
                                <span class="inv-spec-value">
                                    <?= $inv['largo_m'] * $inv['ancho_m'] ?> m²
                                </span>
                            </div>
                        </div>

                        <!-- Cultivo -->
                        <div class="inv-spec-item inv-spec-crop">
                            <div class="inv-spec-icon">
                                <span style="font-size: 1.1rem; filter: <?= $inv['cultivo'] ? 'none' : 'grayscale(1)' ?>;">🌱</span>
                            </div>
                            <div class="inv-spec-info">
                                <span class="inv-spec-label">Estado de Producción</span>
                                
                                <?php 
                                $plant_inv_id = isset($_GET['plant_inv_id']) ? (int)$_GET['plant_inv_id'] : null;
                                if ($plant_inv_id === (int)$inv['invernadero_id']): 
                                ?>
                                    <!-- MODO SIEMBRA RÁPIDA -->
                                    <form method="POST" style="display: flex; gap: 6px; align-items: center; margin-top: 2px;">
                                        <input type="hidden" name="invernadero_id" value="<?= $inv['invernadero_id'] ?>">
                                        <select name="cultivo_id" style="background: rgba(52, 211, 153, 0.1); color: #34d399; border: 1px solid var(--color-primary); border-radius: 6px; padding: 2px 5px; font-size: 0.85rem; font-weight: 600; cursor: pointer; height: 26px;">
                                            <option value="0">-- Barbecho --</option>
                                            <?php foreach ($lista_cultivos_siembra as $c): ?>
                                                <option value="<?= $c['cultivo_id'] ?>" <?= ($inv['cultivo'] && $inv['cultivo']['cultivo_id'] == $c['cultivo_id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($c['nombre_cultivo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="btn_quick_plant" value="1" style="background: none; border: none; cursor: pointer; font-size: 1rem;" title="Confirmar Siembra">✅</button>
                                        <a href="dashboard.php?seccion=mis_invernaderos<?= $url_query_cliente ?>" style="text-decoration: none; font-size: 0.9rem;" title="Cancelar">❌</a>
                                    </form>
                                <?php else: ?>
                                    <!-- MODO LECTURA -->
                                    <?php if ($inv['cultivo']): ?>
                                        <span class="inv-spec-value" style="color: #34d399;">
                                            <?= htmlspecialchars($inv['cultivo']['nombre_cultivo']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inv-spec-value" style="color: var(--color-text-muted); font-style: italic; font-size: 0.85rem;">En barbecho</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Botonera -->
                    <div class="card-actions-row">
                        <?php 
                            $query_params = "dashboard.php?plant_inv_id=" . $inv['invernadero_id'] . $url_query_cliente;
                            if (isset($_GET['seccion'])) $query_params .= "&seccion=" . $_GET['seccion'];
                            if (isset($_GET['localidad_cp'])) $query_params .= "&localidad_cp=" . $_GET['localidad_cp'];
                            if (isset($_GET['parcela_id'])) $query_params .= "&parcela_id=" . $_GET['parcela_id'];
                        ?>
                        <a href="<?= $query_params ?>" 
                           class="btn-sira btn-secondary btn-sm" 
                           style="display: inline-flex; align-items: center; gap: 8px; padding: 0.75rem 1.2rem; border-radius: 10px; border-color: rgba(52, 211, 153, 0.3); font-weight: 600; color: #34d399;">
                            🌱 <?= $inv['cultivo'] ? 'Cambiar' : 'Plantar' ?>
                        </a>
                        <a href="management/edit_invernadero.php?id=<?= $inv['invernadero_id'] ?>&from=lista" 
                           class="btn-sira btn-secondary btn-sm" 
                           style="display: inline-flex; align-items: center; gap: 8px; padding: 0.75rem 1.2rem; border-radius: 10px; border-color: rgba(255,255,255,0.08); font-weight: 600;">
                            ⚙️ Ajustes
                        </a>
                        <a href="sensores.php?id=<?= $inv['invernadero_id'] ?>&nombre=<?= urlencode($inv['nombre']) ?><?= $url_query_cliente ?>" 
                           class="btn-sira btn-primary btn-sm" 
                           style="display: inline-flex; align-items: center; gap: 8px; padding: 0.75rem 1.4rem; border-radius: 10px; box-shadow: 0 4px 15px var(--color-primary-glow); font-weight: 700; background: linear-gradient(135deg, var(--color-primary), #10b981);">
                            Ver Sensores →
                        </a>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
/* Punto de estado estático (V9.1 - Sin animaciones) */
.status-dot-pulse {
    width: 8px;
    height: 8px;
    background-color: #34d399;
    border-radius: 50%;
    position: relative;
    display: inline-block;
}

/* Marcado visual estático para item seleccionado (V9.0 UX) */
.highlight-glow {
    border-color: var(--color-primary) !important;
    border-width: 2px !important;
}

.inv-horizontal-card {
    /* Eliminamos transiciones de movimiento para máxima sobriedad */
    transition: none !important;
}

.inv-horizontal-card:hover {
    border-color: var(--color-primary) !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
}

.inv-name:hover {
    color: var(--color-primary-light);
}
</style>
