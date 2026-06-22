<?php

define('ROOT_DIR', '../');

require_once(ROOT_DIR . 'Pages/Authentication/ExternalAuthLoginPage.php');

$page = new ExternalAuthLoginPage();
$page->PageLoad();
