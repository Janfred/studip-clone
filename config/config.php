<?php

include __DIR__."/config_defaults.inc.php";

if (file_exists(__DIR__."/config_local.inc.php")) {
    include __DIR__."/config_local.inc.php";
}