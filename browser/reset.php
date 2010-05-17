<?php

/**
 * @file reset.php
 *
 * Reset the triple store
 *
 */

require_once (dirname(__FILE__) . '/triple_store.php');

// Triple store
global $store_config;

$r = $store->reset();

?>
