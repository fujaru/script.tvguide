<?php
/**
 * Configuration file
 */
 
define("CACHE_DIR", dirname(__FILE__) . "/cache/");
define("BASE_URL", "http://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/') +1));
