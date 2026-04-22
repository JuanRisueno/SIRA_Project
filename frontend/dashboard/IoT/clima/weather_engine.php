<?php
/**
 * weather_engine.php - Motor central de efectos climatológicos SIRA
 * Determina qué efectos VFX cargar según el escenario activo.
 * Refactorizado v2.0: Soporte para Nieve, Tormenta y Ola de Calor.
 */

$sim_key = (string)($id_inv ?? 1);
$escenario_id = $_SESSION['simulacion_activa'][$sim_key]['id_escenario'] ?? '';
$escenario_nombre = $_SESSION['simulacion_activa'][$sim_key]['nombre'] ?? '';

// 1. Identificar el tipo de clima (Jerarquía de detección)
$clima = 'despejado';
if (stripos($escenario_id, 'helada') !== false || stripos($escenario_id, 'nieve') !== false || stripos($escenario_nombre, 'helada') !== false) {
    $clima = 'nieve';
} elseif (stripos($escenario_id, 'tormenta') !== false || stripos($escenario_id, 'lluvia') !== false || stripos($escenario_nombre, 'tormenta') !== false) {
    $clima = 'tormenta';
} elseif (stripos($escenario_id, 'nublado') !== false || stripos($escenario_nombre, 'nublado') !== false) {
    $clima = 'nublado';
} elseif (stripos($escenario_id, 'sequia') !== false) {
    $clima = 'sequia';
} elseif (stripos($escenario_id, 'calor') !== false || stripos($escenario_nombre, 'calor') !== false) {
    $clima = 'calor';
}
?>

<!-- 🚀 Inyección Dinámica de Estilos y Overlays (Solo si VFX está activo) -->
<?php if (isset($_SESSION['vfx_enabled']) && $_SESSION['vfx_enabled'] === true): ?>
    <style>#sira-weather-overlay,#sira-weather-overlay-front{position:fixed;top:0;left:0;width:100vw;height:100vh;pointer-events:none !important;overflow:hidden;user-select:none}#sira-weather-overlay{z-index:-1}#sira-weather-overlay-front{z-index:1000}#sira-weather-overlay *,#sira-weather-overlay-front *{pointer-events:none !important}</style>
    
    <?php if ($clima === 'nieve'): ?>
        <link rel="stylesheet" href="css/weather/snow.css">
    <?php elseif ($clima === 'tormenta'): ?>
        <link rel="stylesheet" href="css/weather/rain.css">
    <?php elseif ($clima === 'nublado'): ?>
        <link rel="stylesheet" href="css/weather/cloudy.css">
    <?php elseif ($clima === 'calor'): ?>
        <link rel="stylesheet" href="css/weather/heat.css">
    <?php elseif ($clima === 'sequia'): ?>
        <link rel="stylesheet" href="css/weather/sequia.css">
    <?php elseif ($clima === 'despejado'): ?>
        <link rel="stylesheet" href="css/weather/ideal.css">
    <?php endif; ?>

    <!-- 🎭 Renderizado de Overlays -->
    <?php if ($clima): ?>
        
        <!-- Capa de Fondo (Detrás de los paneles) -->
        <div id="sira-weather-overlay" data-scenario="<?= htmlspecialchars($escenario_id) ?>">
            <?php if ($clima === 'despejado'): ?>
                <!-- Elementos CSS Puros -->
                <div class="fx-ideal-sun"></div>
                <div class="fx-ideal-ground"></div>
                <!-- Banco de Nubes Procedural (Muchas más) -->
                    <div class="fx-ideal-clouds">
                        <?php for($k=0; $k<24; $k++): 
                            $t = rand(0, 240); // Altura variada
                            $d = rand(120, 250); // Duración variada
                            $del = rand(-200, 0); // Retraso para que aparezcan ya en pantalla
                            $op = rand(2, 6) / 10; // Opacidad variada
                            $s = rand(5, 12) / 10; // Escala variada
                        ?>
                            <div class="fx-cloud-item" style="top: <?= $t ?>px; animation-duration: <?= $d ?>s; animation-delay: <?= $del ?>s; opacity: <?= $op ?>; transform: scale(<?= $s ?>);"></div>
                        <?php endfor; ?>
                    </div>
                <div class="fx-ideal-grass">
                    <?php for($i=0; $i<180; $i++): 
                        $h = rand(70, 150); // Altura única
                        $dur = rand(40, 70) / 10; // Duración de oscilación única (4s - 7s)
                        $del = rand(-50, 0) / 10; // Retraso único
                    ?>
                        <div class="blade" style="left: <?= $i * 0.55 ?>%; height: <?= $h ?>px; animation-duration: <?= $dur ?>s; animation-delay: <?= $del ?>s;"></div>
                    <?php endfor; ?>
                    <!-- Margaritas procedimentales con alturas variadas -->
                    <?php for($j=0; $j<6; $j++): 
                        $h = rand(140, 220); // Altura del tallo
                    ?>
                        <div class="fx-ideal-flower daisy" style="left: <?= 15 + ($j * 15) ?>%; --h: <?= $h ?>px;">
                            <div class="stem-container">
                                <div class="stem-path"></div>
                            </div>
                            <div class="flower-head">
                                <div class="petal"></div><div class="petal"></div>
                                <div class="petal"></div><div class="petal"></div>
                                <div class="petal :hover {}"></div><div class="petal"></div>
                                <div class="petal"></div><div class="petal"></div>
                                <div class="center"></div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="fx-ideal-glow"></div>
            <?php elseif ($clima === 'nieve'): ?>
                <div class="fx-snow fx-snow-1"></div>
                <div class="fx-snow fx-snow-2"></div>
                <div class="fx-snow fx-snow-3"></div>
            <?php elseif ($clima === 'tormenta'): ?>
                <div class="fx-rain fx-rain-1"></div>
                <div class="fx-rain fx-rain-2"></div>
                <div class="fx-rain fx-rain-3"></div>
                <div class="fx-lightning"></div>
            <?php elseif ($clima === 'nublado'): ?>
                <!-- Nubes Procedimentales de Máxima Densidad (v2.1) -->
                <div class="fx-cloud-bank">
                    <?php for($k=0; $k<80; $k++): 
                        $t = rand(-100, 500); // Cubren todo el cielo superior y medio
                        $d = rand(120, 280); 
                        $del = rand(-250, 0); 
                        $op = rand(4, 9) / 10; // Nubes más densas/opacas
                        $s = rand(10, 30) / 10; // Nubes mucho más grandes
                    ?>
                        <div class="fx-cloud-item" style="top: <?= $t ?>px; animation-duration: <?= $d ?>s; animation-delay: <?= $del ?>s; opacity: <?= $op ?>; transform: scale(<?= $s ?>);"></div>
                    <?php endfor; ?>
                </div>
            <?php elseif ($clima === 'calor'): ?>
                <div class="fx-heat-vapor"></div>
                <div class="fx-heat-glow"></div>
                <div class="fx-heat-clouds"></div>
            <?php elseif ($clima === 'sequia'): ?>
                <!-- Sequía: Atmósfera extrema 100% CSS, cero SVG/computación -->
                <div class="fx-drought-sun"></div>
                <div class="fx-drought-haze"></div>
                <div class="fx-drought-shimmer"></div>
                <div class="fx-drought-vignette"></div>
            <?php endif; ?>
        </div>

        <!-- Capa Frontal (Sobre los paneles) -->
        <div id="sira-weather-overlay-front" data-scenario="<?= htmlspecialchars($escenario_id) ?>">
            <?php if ($clima === 'despejado'): ?>
                <div class="fx-ideal-petals"></div>
                <div class="fx-ideal-rays"></div>
            <?php elseif ($clima === 'calor'): ?>
                <div class="fx-heat-flare"></div>
            <?php elseif ($clima === 'sequia'): ?>
                <div class="fx-drought-flare"></div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

<?php endif; ?>
