<?php
include('include.php');
checkPermission(PERMISSION_ADMINISTRATE_USERS);
?>
<?php head('Module Development Guide') ?>
<body>
<?php menubar('module_manager.php') ?>
<?php title('Modules > Development guide') ?>
<main class="module-guide-page">
    <header class="module-guide-hero">
        <div><span class="module-eyebrow">Developer documentation</span><h1>Module Development Guide</h1><p>Build optional thERP features without changing existing business modules.</p></div>
        <a class="btn btn-outline-secondary" href="module_manager.php">&larr; Back to modules</a>
    </header>

    <section class="module-guide-content card border-0 shadow-sm">
        <div class="card-body">
            <h2>How modules work</h2>
            <p>Optional packages live under <code>modules/</code>. Requests use the CiteCRM-style <code>module:action</code> route and are handled by the protected module dispatcher.</p>
            <pre><code>common/module.php?page=example:main
common/module.php?page=example:edit&amp;id=12</code></pre>

            <h2>Recommended structure</h2>
            <pre><code>modules/example/
├── module.json
├── install.php
├── uninstall.php
├── include.php
├── main.php
├── new.php
├── edit.php
└── delete.php</code></pre>

            <h2>Create the manifest</h2>
            <pre><code>{
  "id": "example",
  "title": "Example Module",
  "description": "A short description for administrators.",
  "version": "1.0.0",
  "author": "Your name",
  "default_action": "main"
}</code></pre>
            <p>The <code>id</code> must exactly match the directory name and use lowercase letters, numbers, underscores, or hyphens.</p>

            <h2>Install and uninstall</h2>
            <p>Use <code>install.php</code> to create tables owned by the module. Use <code>uninstall.php</code> only to remove those same tables. Both scripts run in the normal thERP context and may use <code>sql()</code>, <code>query()</code>, and <code>sql_string()</code>.</p>

            <h2>Build actions</h2>
            <p>Each action is a PHP file in the module directory. Shared queries and business rules belong in <code>include.php</code>. Use <code>moduleUrl('example', 'action')</code> when creating links.</p>

            <h2>Security checklist</h2>
            <ul>
                <li>Use POST for every state-changing action.</li>
                <li>Add <code>moduleFormToken()</code> to forms and validate it with <code>checkModuleFormToken()</code>.</li>
                <li>Allow-list action names and option values.</li>
                <li>Use <code>sql_string()</code> for database values and cast numeric IDs.</li>
                <li>Escape all rendered user and database values with <code>htmlspecialchars()</code>.</li>
                <li>Ensure users can only access records they own or are permitted to manage.</li>
            </ul>

            <div class="module-guide-example"><strong>Working example</strong><p>Inspect <code>modules/taskmanager/</code> for manifests, lifecycle scripts, CRUD actions, secure forms, and a complete interface.</p></div>
        </div>
    </section>
</main>
<?php bottom() ?>
</body>

