            </div>
        </div>

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