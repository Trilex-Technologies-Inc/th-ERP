<?php

function taskManagerTasks($username)
{
    return query('SELECT taskid, title, description, priority, due_date, completed, created_at FROM module_taskmanager_task WHERE username=' . sql_string($username) . ' ORDER BY completed, due_date IS NULL, due_date, taskid DESC');
}

function taskManagerFind($taskid, $username)
{
    return find('SELECT taskid, title, description, priority, due_date, completed FROM module_taskmanager_task WHERE taskid=' . (int)$taskid . ' AND username=' . sql_string($username));
}

function taskManagerRedirect($message = '')
{
    $params = $message === '' ? array() : array('message' => $message);
    header('Location: ' . moduleUrl('taskmanager', 'main', $params));
    exit;
}

function taskManagerPageStart($title)
{
    head($title);
    echo '<body>';
    menubar();
    title('Task Manager > ' . $title);
    echo '<main class="task-manager-page">';
}

function taskManagerPageEnd()
{
    echo '</main>';
    bottom();
    echo '</body>';
}

