<?php
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    file_put_contents('/home/surillya/.thos_state.json', json_encode($input));
}
?>
