<?php
/**
 * SIRA — sensores.php
 * Panel de control y monitorización IoT para invernaderos.
 * Refactorizado v12.0: Arquitectura Modular (IoT Directory)
 */
session_start();
require_once 'includes/config.php';

// PARÁMETROS BASE
$id_inv     = $_GET['id'] ?? 1;
$nombre_inv = $_GET['nombre'] ?? 'Invernadero';
$token      = $_SESSION['jwt_token'];

// 1. CARGA DE LÓGICA Y DATOS (Ecosistema IoT)
require_once 'dashboard/IoT/logic.php';

// 2. CONFIGURACIÓN DE PÁGINA
$page_title = "SIRA Console | " . htmlspecialchars($nombre_inv);
$page_css   = "sensores";    
require_once 'includes/header.php';
?>

<div class="container iot-console">

    <!-- Navegación -->
    <div style="margin-bottom: 2rem;">
        <a href="dashboard.php" class="btn-back">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a Jerarquía
        </a>
    </div>
    
    <!-- Módulos de Interfaz IoT -->
    <?php 
        // 1. Banner de Inteligencia SIRA
        include 'dashboard/IoT/componentes/banner_diagnostico.php'; 
        
        // 2. Consola de Presets y Escenarios
        include 'dashboard/IoT/componentes/consola_presets.php'; 

        // 3. Barra de Estado Maestro (LEDs rápidos)
        include 'dashboard/IoT/componentes/barra_estado_maestro.php';

        // 4. Parrilla Principal (Sensores y Actuadores)
        include 'dashboard/IoT/componentes/cuadricula_dispositivos.php';
    ?>

</div>

<?php require_once 'includes/footer.php'; ?>