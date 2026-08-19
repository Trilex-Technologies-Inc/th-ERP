<?php
require_once __DIR__ . '/include.php';
$tasks = taskManagerTasks(getUser());
$message = getParam('message', '');
taskManagerPageStart('Tasks');
?>
<header class="task-manager-hero">
    <div><span class="module-eyebrow">Productivity</span><h1>My tasks</h1><p>Keep your work organized and your priorities visible.</p></div>
    <a class="btn btn-primary" href="<?php echo htmlspecialchars(moduleUrl('taskmanager', 'new')) ?>">+ New task</a>
</header>
<?php if ($message !== '') { ?><div class="alert alert-success"><?php echo htmlspecialchars($message) ?></div><?php } ?>
<section class="task-manager-list card border-0 shadow-sm">
    <div class="card-header bg-white"><h2>Tasks</h2></div>
    <div class="card-body">
    <?php $count = 0; while ($task = fetch($tasks)) { $count++; ?>
        <article class="task-item <?php echo $task->completed ? 'is-complete' : '' ?>">
            <form method="post" action="<?php echo htmlspecialchars(moduleUrl('taskmanager', 'toggle')) ?>">
                <input type="hidden" name="form_token" value="<?php echo htmlspecialchars(moduleFormToken()) ?>"><input type="hidden" name="taskid" value="<?php echo (int)$task->taskid ?>">
                <button class="task-check" type="submit" aria-label="<?php echo $task->completed ? 'Reopen task' : 'Complete task' ?>"><?php echo $task->completed ? '&#10003;' : '' ?></button>
            </form>
            <div class="task-copy"><strong><?php echo htmlspecialchars($task->title) ?></strong><?php if ($task->description !== '') { ?><p><?php echo nl2br(htmlspecialchars($task->description)) ?></p><?php } ?><small>Created <?php echo htmlspecialchars(date(DATE_PATTERN, strtotime($task->created_at))) ?></small></div>
            <div class="task-meta"><span class="task-priority is-<?php echo htmlspecialchars($task->priority) ?>"><?php echo htmlspecialchars(ucfirst($task->priority)) ?></span><?php if ($task->due_date) { ?><time class="<?php echo !$task->completed && $task->due_date < date('Y-m-d') ? 'is-overdue' : '' ?>">Due <?php echo htmlspecialchars($task->due_date) ?></time><?php } ?><a href="<?php echo htmlspecialchars(moduleUrl('taskmanager', 'edit', array('taskid' => $task->taskid))) ?>">Edit</a></div>
            <form method="post" action="<?php echo htmlspecialchars(moduleUrl('taskmanager', 'delete')) ?>" onsubmit="return confirm('Delete this task?')">
                <input type="hidden" name="form_token" value="<?php echo htmlspecialchars(moduleFormToken()) ?>"><input type="hidden" name="taskid" value="<?php echo (int)$task->taskid ?>">
                <button class="task-delete" type="submit" aria-label="Delete task">&times;</button>
            </form>
        </article>
    <?php } ?>
    <?php if (!$count) { ?><div class="task-empty"><span>&#10003;</span><strong>No tasks yet</strong><p>Create your first task to get started.</p></div><?php } ?>
    </div>
</section>
<?php taskManagerPageEnd() ?>
