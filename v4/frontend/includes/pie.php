            </div>
        </div>

        <!-- Scripts -->
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <!-- Bootstrap 5.3.8 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
            // Al hacer clic en el botón del menú, se muestra/oculta el menú
            $("#menu-toggle").click(function(e) {
                e.preventDefault();
                $("#wrapper").toggleClass("toggled");
            });
            
            // Inicialmente ocultamos todos los submenús
            $(".submenu").hide();
            
            // Función para desplegar el submenú seleccionado
            function showSubmenu(id)
            {
                // Para ocultar el resto de submenús
                $(".submenu").hide();
                // Mostrar submenú seleccionado
                $(".submenu" + id).toggle(200);
            }
        </script>

    </body>
</html>
