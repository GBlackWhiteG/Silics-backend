<?php

ob_start();
include '/var/www/code/script.php';
$output = ob_get_clean();

echo json_encode($output);
