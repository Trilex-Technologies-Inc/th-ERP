<?php
$configured = file_exists(__DIR__ . '/conf/config.php') || file_exists(__DIR__ . '/conf/configure.php');
header('Location: ' . ($configured ? 'common/modules.php' : 'install/'));
exit;
