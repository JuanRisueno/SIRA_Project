<!-- SIRA Console: Escenarios de Simulación -->
<div class="console-panel">
    <?php if (!empty($estado_iot['_error'])): ?>
        <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; padding: 1.5rem; margin-bottom: 2rem; border-radius: 8px;">
            <h3 style="color: #fca5a5; margin-bottom: 0.5rem;">🔥 API Error <?= htmlspecialchars($estado_iot['_code']) ?></h3>
            <p style="color: white; font-family: monospace;"><?= htmlspecialchars($estado_iot['_response']) ?></p>
            <p style="color: gray; font-size: 0.8rem;"><?= htmlspecialchars($estado_iot['_curl_err']) ?></p>
        </div>
    <?php endif; ?>

    <div class="console-header">
        <div>
            <h2>SIRA Console | 🌱 <?= htmlspecialchars($nombre_inv) ?></h2>
            <div style="display: flex; gap: 10px; align-items: center; margin-top: 5px; flex-wrap: wrap;">
                <span style="background: rgba(16, 185, 129, 0.2); color: var(--color-primary); padding: 5px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 700;">
                    🍇 Cultivo Activo: <?= htmlspecialchars(strtoupper($nombre_cultivo)) ?>
                </span>
                
                <?php if ($parametros_optimos): ?>
                    <span style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; padding: 5px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; border: 1px solid rgba(14, 165, 233, 0.3);">
                        Fase: <b><?= htmlspecialchars($parametros_optimos['fase']) ?></b> &nbsp;|&nbsp; 
                        🌡️ <?= $parametros_optimos['temp_min'] ?> - <?= $parametros_optimos['temp_max'] ?>ºC &nbsp;|&nbsp; 
                        💧 <?= $parametros_optimos['hum_min'] ?> - <?= $parametros_optimos['hum_max'] ?>%
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="jornada-badge <?= $jornada_activa ? 'jornada-on' : 'jornada-off' ?>">
            <?= $jornada_activa ? '☀ JORNADA LABORAL' : '🌙 FUERA DE HORARIO' ?>
        </div>
    </div>
    
    <form method="POST" action="sensores.php?<?= http_build_query($_GET) ?>" class="preset-bar">
        <input type="hidden" name="invernadero_id" value="<?= htmlspecialchars($id_inv) ?>">
        
        <button type="submit" name="simular_escenario" value="ideal" class="btn-preset btn-ideal">🌱 Ideal</button>
        <button type="submit" name="simular_escenario" value="tormenta" class="btn-preset btn-tormenta">⛈ Tormenta</button>
        <button type="submit" name="simular_escenario" value="calor" class="btn-preset btn-calor">🔥 Calor</button>
        <button type="submit" name="simular_escenario" value="helada" class="btn-preset btn-helada">❄ Helada</button>
        <button type="submit" name="simular_escenario" value="nublado" class="btn-preset btn-nublado">☁ Nublado</button>
        <button type="submit" name="simular_escenario" value="sequia" class="btn-preset btn-sequia">🏜 Sequía</button>
        
        <button type="submit" name="simular_escenario" value="random" class="btn-random">🎲 RANDOMIZE</button>
    </form>
</div>
