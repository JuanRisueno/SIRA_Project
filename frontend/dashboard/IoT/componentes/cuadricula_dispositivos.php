<!-- Cabeceras Alineadas -->
<div class="iot-headers">
    <div style="display: flex; align-items: flex-end;">
        <h3 class="iot-col-title" style="margin: 0;">Lectura de Sensores</h3>
    </div>
    <div>
        <h3 class="iot-col-title" style="margin: 0;">Control de Actuadores</h3>
        <span style="font-size: 0.75rem; color: #94a3b8; display: block; margin-top: 4px; font-weight: 500;">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            La intervención manual suspende el control del SIRA durante 120 minutos
        </span>
    </div>
</div>

<!-- Grid Layout IoT Dinámico y Alineado -->
<div class="iot-grid">
    <?php 
    $max_rows = max(count($sensores_final), count($actuadores_final));
    for ($i = 0; $i < $max_rows; $i++):
        $s = $sensores_final[$i] ?? null;
        $a = $actuadores_final[$i] ?? null;
    ?>
        <!-- COLUMNA SENSOR -->
        <?php if ($s): 
            $color = "var(--color-primary)";
            if(stripos($s['tipo'], 'temp') !== false) $color = "#f59e0b";
            if(stripos($s['tipo'], 'humedad') !== false || stripos($s['tipo'], 'suelo') !== false) $color = "#3b82f6";
            if(stripos($s['tipo'], 'viento') !== false) $color = "#a855f7";
            if(stripos($s['tipo'], 'lluvia') !== false) $color = "#60a5fa";
        ?>
            <div class="card iot-card sensor-card">
                <div class="iot-card-header">
                    <span class="iot-label"><?= htmlspecialchars($s['tipo']) ?></span>
                    <span class="iot-tag">En vivo</span>
                </div>
                <div class="iot-value-block" style="color: <?= $color ?>; margin-bottom: 15px;">
                    <span class="iot-value"><?= $s['valor'] !== null ? htmlspecialchars($s['valor']) : '--' ?></span>
                    <span class="iot-unit"><?= htmlspecialchars($s['unidad']) ?></span>
                </div>
                <div class="iot-chart-box" style="margin-top: auto;">
                    <?= render_svg_chart($s['sensor_id'], $token, $color) ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-placeholder"></div>
        <?php endif; ?>

        <!-- COLUMNA ACTUADOR -->
        <?php if ($a): 
            $estado = strtoupper($a['estado'] ?? 'APAGADO');
            $is_on = (stripos($estado, 'ENCENDI') !== false || stripos($estado, 'ABIERT') !== false);
            $es_led = (stripos($a['tipo'], 'luz') !== false || stripos($a['tipo'], 'led') !== false || stripos($a['tipo'], 'ilumina') !== false);
        ?>
            <div class="card iot-card actuator-card">
                <div class="iot-card-header">
                    <span class="iot-label"><?= htmlspecialchars($a['tipo']) ?></span>
                    <?php if ($es_led && !$jornada_configurada): ?>
                        <span class="iot-tag" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">⚠️ Sin Jornada</span>
                    <?php elseif ($a['modo_manual']): ?>
                        <span class="iot-tag manual">✋ Manual</span>
                    <?php else: ?>
                        <span class="iot-tag auto">⚙ Auto</span>
                    <?php endif; ?>
                </div>
                
                <div class="actuator-status <?= $is_on ? 'status-on' : 'status-off' ?>" style="flex-grow: 1; display:flex; align-items:center; justify-content:center; margin-bottom: 20px;">
                    <?= htmlspecialchars($estado) ?>
                </div>

                <form method="POST" class="actuator-controls" style="margin-top: auto; display: flex; flex-direction: column; gap: 0.5rem;">
                    <input type="hidden" name="override_actuador" value="1">
                    <input type="hidden" name="actuador_id" value="<?= $a['actuador_id'] ?>">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <?php if(stripos($a['tipo'], 'ventana') !== false): ?>
                            <button type="submit" name="nuevo_estado" value="ABIERTO 100%" class="btn-override">Abrir</button>
                            <button type="submit" name="nuevo_estado" value="CERRADO" class="btn-override action-stop">Cerrar</button>
                        <?php else: ?>
                            <button type="submit" name="nuevo_estado" value="ENCENDIDO" class="btn-override">ON</button>
                            <button type="submit" name="nuevo_estado" value="APAGADO" class="btn-override action-stop">OFF</button>
                        <?php endif; ?>
                    </div>

                    <?php if ($a['modo_manual'] && !($es_led && !$jornada_configurada)): ?>
                        <button type="submit" name="nuevo_estado" value="AUTO" class="btn-override" style="background-color: transparent; border: 1px solid var(--color-primary); color: var(--color-primary); margin-top: 5px;">Volver a modo AUTO</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php else: ?>
            <div class="empty-placeholder"></div>
        <?php endif; ?>

    <?php endfor; ?>
</div>
