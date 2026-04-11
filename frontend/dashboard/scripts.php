<script>
    /**
     * scripts.php - Interacciones del Dashboard
     */
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Gestionar clics en los botones de tres puntos (Menús de Opciones)
        const optionBtns = document.querySelectorAll('.options-btn');

        optionBtns.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation(); // Evitar que el clic llegue a la tarjeta
                const menu = this.nextElementSibling;

                // Cerrar otros menús abiertos
                document.querySelectorAll('.options-menu.show').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });

                // Alternar el menú actual
                menu.classList.toggle('show');
            });
        });

        // 2. Cerrar menús al hacer clic fuera
        document.addEventListener('click', function () {
            document.querySelectorAll('.options-menu.show').forEach(m => {
                m.classList.remove('show');
            });
        });
    });
</script>
