<?php
require_once __DIR__ . '/include.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !checkModuleFormToken(getParam('form_token')))
    taskManagerRedirect('Invalid request.');
sql('DELETE FROM module_taskmanager_task WHERE taskid=' . (int)getParam('taskid') . ' AND username=' . sql_string(getUser()));
taskManagerRedirect('Task deleted.');

