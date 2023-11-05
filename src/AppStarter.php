<?php

namespace App;

use App\Resourses\Container;

class AppStarter
{
    private Container $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    public function run(): void
    {
        $module = strtok($_SERVER['REQUEST_URI'], '?');

        $task = $this->findRoute($module);

        if ($task) {
            $filePath = ROOT_PATH."/src/Controllers/{$task['controller']}.php";

            if (file_exists($filePath)) {
                $class = 'App\Controllers\\'.$task['controller'];
                // $name = sprintf('App\Controllers\%s', 'DefaultController');
                //include_once ROOT_PATH.'/src/Controllers/DefaultController.php';

                $controller = new $class();
                $controller->setTemplate($this->container->template);
                $controller->setDbc($this->container->db);
                $controller->setPage($this->container->page);
                $controller->setUser($this->container->user);
                $controller->setRoutes($this->container->routes);
                $controller->setValidation($this->container->validation);
                $controller->setEntityId($task['entity_id']);
                $controller->runAction($task['action']);
            }
        } else {
            echo 'Not found';
            http_response_code(404);
        }
    }

    private function findRoute(string $uri): array|false
    {
        $route = $this->container->routes->first('pretty_url', $uri);

        if (! empty($route->module)) {
            $module['controller'] = ucfirst($route->module).'Controller';
            $module['action'] = $route->action;
            $module['entity_id'] = $route->entity_id;

            return $module;
        }

        return false;
    }
}