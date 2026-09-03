<?php
require_once __DIR__ . '/include.php';
$task = taskManagerFind(getParam('taskid'), getUser());
if (!$task)
    taskManagerRedirect('Task not found.');
$error = '';
$taskTitle = $_SERVER['REQUEST_METHOD'] === 'POST' ? trim(getParam('title', '')) : $task->title;
$description = $_SERVER['REQUEST_METHOD'] === 'POST' ? trim(getParam('description', '')) : $task->description;
$priority = $_SERVER['REQUEST_METHOD'] === 'POST' ? getParam('priority', 'normal') : $task->priority;
$dueDate = $_SERVER['REQUEST_METHOD'] === 'POST' ? trim(getParam('due_date', '')) : (string)$task->due_date;
$priorities = array('low', 'normal', 'high');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkModuleFormToken(getParam('form_token')))
        $error = 'The form expired. Please try again.';
    elseif ($taskTitle === '' || strlen($taskTitle) > 255 || !in_array($priority, $priorities, true))
        $error = 'Enter a valid title and priority.';
    elseif ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate))
        $error = 'Enter a valid due date.';
    else {
        sql('UPDATE module_taskmanager_task SET title=' . sql_string($taskTitle) . ', description=' . sql_string($description) . ', priority=' . sql_string($priority) . ', due_date=' . ($dueDate === '' ? 'NULL' : sql_string($dueDate)) . ' WHERE taskid=' . (int)$task->taskid . ' AND username=' . sql_string(getUser()));
        taskManagerRedirect('Task updated.');
    }
}
taskManagerPageStart('Edit task');
?>
<section class="task-form-card card border-0 shadow-sm">
    <div class="card-header bg-white"><span class="module-eyebrow">Task Manager</span><h1>Edit task</h1></div>
    <div class="card-body">
        <?php if ($error !== '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error) ?></div><?php } ?>
        <form method="post" action="<?php echo htmlspecialchars(moduleUrl('taskmanager', 'edit', array('taskid' => $task->taskid))) ?>">
            <input type="hidden" name="form_token" value="<?php echo htmlspecialchars(moduleFormToken()) ?>">
            <label class="form-label" for="task-title">Title</label><input class="form-control mb-3" id="task-title" name="title" maxlength="255" value="<?php echo htmlspecialchars($taskTitle) ?>" required autofocus>
            <label class="form-label" for="task-description">Description</label><textarea class="form-control mb-3" id="task-description" name="description" rows="5"><?php echo htmlspecialchars($description) ?></textarea>
            <div class="row g-3 mb-4"><div class="col-md-6"><label class="form-label" for="task-priority">Priority</label><select class="form-select" id="task-priority" name="priority"><?php foreach ($priorities as $value) { ?><option value="<?php echo $value ?>" <?php echo $priority === $value ? 'selected' : '' ?>><?php echo ucfirst($value) ?></option><?php } ?></select></div><div class="col-md-6"><label class="form-label" for="task-due">Due date</label><input class="form-control" id="task-due" type="date" name="due_date" value="<?php echo htmlspecialchars($dueDate) ?>"></div></div>
            <div class="d-flex gap-2"><button class="btn btn-primary" type="submit">Save changes</button><a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars(moduleUrl('taskmanager')) ?>">Cancel</a></div>
        </form>
    </div>
</section>
<?php taskManagerPageEnd() ?>

