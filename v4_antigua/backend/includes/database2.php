<?php

if (!empty($db)) {
    mysqli_close($db);
    unset($db);
}

?>