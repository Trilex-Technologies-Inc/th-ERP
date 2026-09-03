<?php

class ThERPModuleManager
{
    private $modulesDir;

    public function __construct($modulesDir = null)
    {
        $this->modulesDir = $modulesDir ?: dirname(__DIR__) . '/modules';
    }

    public function initialize()
    {
        sql("CREATE TABLE IF NOT EXISTS module_registry (
            module_name VARCHAR(80) NOT NULL PRIMARY KEY,
            version VARCHAR(30) NOT NULL,
            enabled TINYINT(1) NOT NULL DEFAULT 0,
            installed_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    public function discover()
    {
        $modules = array();
        if (!is_dir($this->modulesDir))
            return $modules;

        foreach (scandir($this->modulesDir) as $directory) {
            if (!preg_match('/^[a-z][a-z0-9_-]*$/', $directory))
                continue;
            $manifestFile = $this->modulesDir . '/' . $directory . '/module.json';
            if (!is_file($manifestFile))
                continue;
            $manifest = json_decode(file_get_contents($manifestFile), true);
            if (!is_array($manifest) || ($manifest['id'] ?? '') !== $directory)
                continue;
            $manifest['path'] = dirname($manifestFile);
            $manifest['title'] = $manifest['title'] ?? $directory;
            $manifest['description'] = $manifest['description'] ?? '';
            $manifest['version'] = $manifest['version'] ?? '0.1.0';
            $manifest['author'] = $manifest['author'] ?? '';
            $manifest['icon'] = $manifest['icon'] ?? '&#9638;';
            $manifest['default_action'] = $manifest['default_action'] ?? 'main';
            $modules[$directory] = $manifest;
        }
        ksort($modules);
        return $modules;
    }

    public function all()
    {
        $this->initialize();
        $installed = array();
        $rows = query('SELECT module_name, version, enabled, installed_at FROM module_registry');
        while ($row = fetch_assoc($rows))
            $installed[$row['module_name']] = $row;

        $modules = $this->discover();
        foreach ($modules as $name => &$module) {
            $module['installed'] = isset($installed[$name]);
            $module['enabled'] = isset($installed[$name]) && (bool)$installed[$name]['enabled'];
            $module['installed_version'] = isset($installed[$name]) ? $installed[$name]['version'] : null;
            $module['installed_at'] = isset($installed[$name]) ? $installed[$name]['installed_at'] : null;
        }
        unset($module);
        return $modules;
    }

    public function get($name)
    {
        $modules = $this->all();
        return isset($modules[$name]) ? $modules[$name] : null;
    }

    private function runLifecycle($module, $action)
    {
        $file = $module['path'] . '/' . $action . '.php';
        if (!is_file($file))
            return true;
        $modulePath = $module['path'];
        require $file;
        return true;
    }

    public function install($name)
    {
        $module = $this->get($name);
        if (!$module || $module['installed'])
            return false;
        begin();
        try {
            $this->runLifecycle($module, 'install');
            sql("INSERT INTO module_registry (module_name, version, enabled, installed_at) VALUES (" .
                sql_string($name) . ', ' . sql_string($module['version']) . ", 1, NOW())");
            commit();
            return true;
        } catch (Throwable $error) {
            rollback();
            throw $error;
        }
    }

    public function uninstall($name)
    {
        $module = $this->get($name);
        if (!$module || !$module['installed'])
            return false;
        begin();
        try {
            $this->runLifecycle($module, 'uninstall');
            sql('DELETE FROM module_registry WHERE module_name=' . sql_string($name));
            commit();
            return true;
        } catch (Throwable $error) {
            rollback();
            throw $error;
        }
    }

    public function setEnabled($name, $enabled)
    {
        $module = $this->get($name);
        if (!$module || !$module['installed'])
            return false;
        sql('UPDATE module_registry SET enabled=' . ($enabled ? 1 : 0) . ' WHERE module_name=' . sql_string($name));
        return affected_rows() >= 0;
    }

    public function resolve($route)
    {
        $parts = explode(':', $route, 2);
        $name = $parts[0] ?? '';
        $action = $parts[1] ?? 'main';
        if (!preg_match('/^[a-z][a-z0-9_-]*$/', $name) || !preg_match('/^[a-z][a-z0-9_-]*$/', $action))
            return null;
        $module = $this->get($name);
        if (!$module || !$module['installed'] || !$module['enabled'])
            return null;
        $controller = $module['path'] . '/' . $action . '.php';
        if (!is_file($controller))
            return null;
        return array('name' => $name, 'action' => $action, 'module' => $module, 'controller' => $controller);
    }
}

function moduleFormToken()
{
    if (empty($_SESSION['module_form_token']))
        $_SESSION['module_form_token'] = bin2hex(random_bytes(24));
    return $_SESSION['module_form_token'];
}

function checkModuleFormToken($token)
{
    return is_string($token) && hash_equals(moduleFormToken(), $token);
}

function moduleUrl($module, $action = 'main', $params = array())
{
    $params = array_merge(array('page' => $module . ':' . $action), $params);
    return 'module.php?' . http_build_query($params);
}

