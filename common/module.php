<?php
include('include.php');
require_once('../include/module_system.php');

$route = getParam('page', '');
$manager = new ThERPModuleManager();
$resolved = $manager->resolve($route);
if ($resolved === null) {
    http_response_code(404);
    head('Module unavailable');
    echo '<body>';
    menubar();
    title('Module unavailable');
    echo "<main class='container-fluid px-0'><div class='alert alert-warning'>The requested module is not installed, is disabled, or the action does not exist.</div></main>";
    bottom();
    echo '</body>';
    exit;
}

$module = $resolved['module'];
$moduleName = $resolved['name'];
$moduleAction = $resolved['action'];
$moduleManager = $manager;
require $resolved['controller'];

