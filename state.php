<?php
define('THOS_STATE_FILE', '/home/surillya/.thos_state.json');

function thos_load_state() {
    if (!file_exists(THOS_STATE_FILE)) return null;
    return json_decode(file_get_contents(THOS_STATE_FILE), true);
}

function thos_save_state($data) {
    file_put_contents(THOS_STATE_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>
