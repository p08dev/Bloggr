<?php
include_once(__DIR__."/lib/autoload.php");
$auth->logout();
header("Location: /");
die();
