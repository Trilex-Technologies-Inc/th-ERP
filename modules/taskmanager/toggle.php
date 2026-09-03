<?php
require_once __DIR__ . '/include.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !checkModuleFormToken(getParam('form_token')))
    taskManagerRedirect('Invalid request.');
$task = taskManagerFind(getParam('taskid'), getUser());
if ($task)
    sql('UPDATE module_taskmanager_task SET completed=' . ($task->completed ? 0 : 1) . ' WHERE taskid=' . (int)$task->taskid . ' AND username=' . sql_string(getUser()));
taskManagerRedirect($task && !$task->completed ? 'Task completed.' : 'Task reopened.');

