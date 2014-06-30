<?php
namespace yii\metadata;

use Yii;
use yii\base\Component;
use yii\helpers\Inflector;

/**
 * Description of AccessHelper
 *
 * @author MDMunir
 */
class MetaData{
    /**
     * 
     * @return array
     */
    public static function getRoutes($exclude = ['module'=>['debug','gii'],'controller'=>['test']])
    {
        $result = [];
        self::getRouteRecrusive(Yii::$app, $result,$exclude);
        return $result;
    }

    /**
     * 
     * @param \yii\base\Module $module
     * @param array $result
     */
    private static function getRouteRecrusive($module, &$result,$exclude)
    {
        foreach ($module->getModules() as $id => $child) {
            if (($child = $module->getModule($id)) !== null) {
                if(!in_array($child->id, $exclude['module'])){
                    self::getRouteRecrusive($child, $result,$exclude);
                }
            }
        }
        
        /* @var $controller \yii\base\Controller */
        foreach ($module->controllerMap as $id => $value) {            
            $controller = Yii::createObject($value, [$id, $module]);            
            if(!in_array($controller->module->id, $exclude['module']) && !in_array($controller->id, $exclude['controller'])){
                self::getActionRoutes($controller, $result);
                //$result[$controller->module->id][$controller->id][] = '*';
                $result['map'][] = [
                    'module' => $controller->module->id,
                    'controller' => $controller->id,
                    'action'    =>  '*',
                ];
            }
        }
        
        if(!in_array($module->id, $exclude['module'])){
            $namespace = trim($module->controllerNamespace, '\\') . '\\';
            self::getControllerRoutes($module, $namespace, '', $result,$exclude);
            //$result[$module->id]['*'] = '*';
            $result['map'][] = [
                    'module' => $module->id,
                    'controller' => '*',
                    'action'    =>  '*',
                ];
        }
    }

    private static function getControllerRoutes($module, $namespace, $prefix, &$result,$exclude)
    {
        $path = Yii::getAlias('@' . str_replace('\\', '/', $namespace));
        foreach (scandir($path) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($path . '/' . $file)) {
                self::getControllerRoutes($module, $namespace . $file . '\\', $prefix . $file . '/', $result,$exclude);
            } elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
                $id = Inflector::camel2id(substr(basename($file), 0, -14));
                $className = $namespace . Inflector::id2camel($id) . 'Controller';
                if (strpos($className, '-') === false && class_exists($className) && is_subclass_of($className, 'yii\base\Controller')) {
                    $controller = new $className($prefix . $id, $module);
                    if(!in_array($controller->id, $exclude['controller'])){
                        self::getActionRoutes($controller, $result);
                        //$result[$controller->module->id][$controller->id][] = '*';
                        $result['map'][] = [
                            'module' => $controller->module->id,
                            'controller' => $controller->id,
                            'action'    =>  '*',
                        ];
                    }
                }
            }
        }
    }

    /**
     * 
     * @param \yii\base\Controller $controller
     * @param Array $result all controller action.
     */
    private static function getActionRoutes($controller, &$result)
    {
        $prefix = '/' . $controller->uniqueId . '/';
        //print_r(['controllerId'=>$controller->id,'moduleId'=>$controller->module->id]);
        foreach ($controller->actions() as $id => $value) {
            //$result[$controller->module->id][$controller->id][] = $id;            
            self::setActionList($id, $result);
            self::setControllerList($controller, $result);
            self::setModuleList($controller->module, $result);
            $result['map'][] = [
                            'module' => $controller->module->id,
                            'controller' => $controller->id,
                            'action'    =>  $id,
                        ];
        }
        $class = new \ReflectionClass($controller);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
                //$result[$controller->module->id][$controller->id][] = Inflector::camel2id(substr($name, 6));
                
                $action = Inflector::camel2id(substr($name, 6));
                self::setActionList($action, $result);
                self::setControllerList($controller, $result);
                self::setModuleList($controller->module, $result);
                $result['map'][] = [
                            'module' => $controller->module->id,
                            'controller' => $controller->id,
                            'action'    =>  $action,
                        ];
            }
        }
    }
    
    /**
     * 
     * @param \yii\base\Module $module
     * @param array $result
     */
    private static function setModuleList($module, &$result)
    {
        if(!isset($result['module']) || !in_array($module->id, $result['module'])){
            $result['module'][] = $module->id;
        }
    }
    
    /**
     * 
     * @param \yii\base\Controller $controller
     * @param array $result
     */
    private static function setControllerList($controller, &$result)
    {
        if(!isset($result['controller']) || !in_array($controller->id, $result['controller'])){
            $result['controller'][] = $controller->id;
        }
    }
    
    /**
     * 
     * @param  $actionName
     * @param array $result
     */
    private static function setActionList($action, &$result)
    {
        if(!isset($result['action']) || !in_array($action, $result['action'])){
            $result['action'][] = $action;
        }
    }
}