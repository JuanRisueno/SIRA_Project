<?php
/**
 * ===============================================================================================
 *                              SIRA - DASHBOARD ORQUESTADOR
 * ===============================================================================================
 * Este archivo centraliza la visualización de la infraestructura y gestión de usuarios.
 * Ha sido MODULARIZADO para facilitar el mantenimiento preventivo y correctivo (ASIR Architecture).
 * Piezas incluidas:
 * - dashboard/logic.php             -> API, Funciones, Sesión y Acciones.
 * - dashboard/header.php            -> Migas de pan, Título y Botones.
 * - dashboard/search_bar.php        -> Buscador PHP + SQL.
 * - dashboard/view_clients.php      -> Grid/Lista de agricultores.
 * - dashboard/view_infrastructure.php-> Localidades, Parcelas e Invernaderos.
 * - dashboard/scripts.php           -> Interacciones UI.
 */

session_start();
require_once 'includes/config.php';

// 1. Cargar Lógica, API y Preparación de Datos
require_once 'dashboard/logic.php';

// 2. Cargar Cabecera HTML Estándar (Meta, CSS, JWT)
$page_title = "SIRA - Panel de Control";
$page_css = "dashboard";
require_once 'includes/header.php';

// ── Error Crítico: Si la API no responde ──
if ($arbol === null): ?>
    <div class='container'>
        <div class='error-panel'>
            <h2>⚠️ Servicio Temporalmente Caído</h2>
            <p>No se pudo conectar con los servidores de SIRA. Por favor, inténtalo de nuevo en unos minutos.</p>
        </div>
    </div>
    <?php require_once 'includes/footer.php'; exit(); ?>
<?php endif; ?>

<!-- 3. Modal de Confirmación (Si aplica) -->
<?php if ($cliente_a_confirmar): ?>
    <div class="confirm-overlay">
        <div class="confirm-card">
            <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
            <h2>¿Estás seguro?</h2>
            <p>Vas a ocultar al agricultor:<br><strong><?= htmlspecialchars($cliente_a_confirmar['nombre_empresa']) ?></strong></p>
            <div class="confirm-actions">
                <a href="dashboard.php?accion=ocultar&id=<?= $cliente_a_confirmar['cliente_id'] ?>" class="confirm-btn-yes">Sí, ocultar</a>
                <a href="dashboard.php" class="confirm-btn-no">No, cancelar</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- 4. Contenedor Principal de la Interfaz -->
<div class="container">
    <?php 
        // Renderizar Cabecera de Contenido (Breadcrumbs + Título + Botones)
        require_once 'dashboard/header.php';
        
        // Renderizar Buscador (Sólo si estamos en vista de agricultores)
        require_once 'dashboard/search_bar.php';
        
        // Renderizar el contenido según la vista activa
        if ($vista_actual === 'selector_cliente') {
            require_once 'dashboard/view_clients.php';
        } else {
            require_once 'dashboard/view_infrastructure.php';
        }
    ?>
</div>

<!-- 5. Scripts de interacción -->
<?php require_once 'dashboard/scripts.php'; ?>

<!-- 6. Pie de página -->
<?php require_once 'includes/footer.php'; ?>