<!-- Menú de opciones de la aplicación -->

<div class="bg-light">
    <button class="btn btn-light menu-toggle" id="menu-toggle">
        <img src="img/menu.png" />
    </button>
</div>
<div class="bg-light border-right" id="sidebar-wrapper" style="z-index:100;">
    <div class="sidebar-heading">Gestión IES<br />
        <em><?= isset($_SESSION['loginUsuario'])?$_SESSION['loginUsuario']:'' ?></em>
    </div>
    <div class="list-group list-group-flush">
        <?php
            foreach ($menus as $menu)
            {
                // Miramos si el rol del usuario es compatible con el menú actual
                // Será compatible si el menú no tiene roles o tiene alguno como el del usuario
                $rolAdecuado = FALSE;

                if ($menu["roles"] != NULL)
                {
                    foreach($menu["roles"] as $rol)
                        if (isset($_SESSION["rol"]) && $_SESSION["rol"] == $rol)
                            $rolAdecuado = TRUE;
                }
                else
                {
                    $rolAdecuado = TRUE;
                }

                // Si el rol es adecuado mostramos ese item del menú
                if ($rolAdecuado)
                {
                    // Menú que activa una página concreta
                    if ($menu["link"] != NULL)
                    {
                        $class = "bg-light ";
                        if ($menu["submenu"])
                            $class = "bg-dark submenu submenu" . $menu["id"] . " ";
        ?>

                    <a href="<?= $menu["link"] ?>" class="<?= $class ?>list-group-item list-group-item-action">
                        <img src="img/<?= $menu["icono"]?>.png" />
                        <?= $menu["texto"] ?>
                    </a>        

        <?php
                    }

                    // Menú que simplemente despliega submenús
                    else
                    {
        ?>

                    <a href="#" class="list-group-item list-group-item-action bg-light" 
                        onclick="showSubmenu(<?= $menu["id"] ?>)">
                        <img src="img/<?= $menu["icono"]?>.png" />
                        <?= $menu["texto"] ?>
                    </a>        

        <?php
                    }
                }
            }
        ?>
    </div>
</div>
