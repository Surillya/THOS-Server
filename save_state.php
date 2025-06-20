<?php
require_once('state.php');
$input = json_decode(file_get_contents('php://input'), true);
if ($input) thos_save_state($input);
?>
