            </div>
        </div>

        <!-- Scripts -->
        <!-- Bootstrap 5.3.8 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
            // Al hacer clic en el botón del menú, se muestra/oculta el menú
            document.getElementById('menu-toggle').addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('wrapper').classList.toggle('toggled');
            });
            
            // Inicialmente ocultamos todos los submenús
            document.querySelectorAll('.submenu').forEach(function(submenu) {
                submenu.style.display = 'none';
            });
            
            // Función para desplegar el submenú seleccionado
            function showSubmenu(id)
            {
                // Para ocultar el resto de submenús
                document.querySelectorAll('.submenu').forEach(function(submenu) {
                    submenu.style.display = 'none';
                });
                // Mostrar submenú seleccionado
                var submenuElement = document.querySelector('.submenu' + id);
                if (submenuElement) {
                    submenuElement.style.display = submenuElement.style.display === 'none' ? 'block' : 'none';
                }
            }
        </script>

    </body>
</html>
