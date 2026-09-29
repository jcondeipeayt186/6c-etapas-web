<?php
function fechaFormato($fecha) {
    $date = new DateTime($fecha);
    return $date->format('d-m-Y');
}

?>