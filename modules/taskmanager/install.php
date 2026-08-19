<?php
sql("CREATE TABLE IF NOT EXISTS module_taskmanager_task (
    taskid INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority VARCHAR(12) NOT NULL DEFAULT 'normal',
    due_date DATE NULL,
    completed TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    INDEX taskmanager_user (username, completed, due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

