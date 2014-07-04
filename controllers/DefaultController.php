<?php

namespace yii\metadata\controllers;

use Yii;
use yii\web\Controller,
 yii\metadata\component\MetaData,
 yii\helpers\Json;

class DefaultController extends Controller
{
    public function actionModules()
    {
        $model = $this->getModel();        
        $data = $this->getStructureData($model->getModuleList());
        
        $result = new \stdClass;
        $result->output = $data;
        $result->selected = "";
        header('Content-Type: application/json');        
        echo Json::encode($result);
        \yii::$app->end();
    }
    
    public function actionControllers()
    {
        $request = Yii::$app->request->post();
        list($module,$controller,$action) = $request['depdrop_parents'];
        if($module == 'Loading ...') $module = '';
        if($controller == 'Loading ...') $controller = '';
        if($action == 'Loading ...') $action = '';
        
        $model = $this->getModel();        
        $data = $this->getStructureData($model->getControllerList($module));
        
        $result = new \stdClass;
        $result->output = $data;
        $result->selected = "";
        header('Content-Type: application/json');        
        echo Json::encode($result);
        \yii::$app->end();
    }
    
    public function actionActions()
    {
        $request = Yii::$app->request->post();
        list($module,$controller,$action) = $request['depdrop_parents'];
        if($module == 'Loading ...') $module = '';
        if($controller == 'Loading ...') $controller = '';
        if($action == 'Loading ...') $action = '';
        
        $model = $this->getModel();        
        $data = $this->getStructureData($model->getActionsList($module,$controller));
        
        $result = new \stdClass;
        $result->output = $data;
        $result->selected = "";
        header('Content-Type: application/json');        
        echo Json::encode($result);
        \yii::$app->end();
    }
    
    private function getModel(){
        return New MetaData();
    }
    
    private function getStructureData($array){
        $data = [];
        foreach($array as $id=>$name){
            $obj = new \stdClass;
            $obj->id = $id;
            $obj->name = $name;
            $data[] = $obj;
        }
        return $data;
    }
}
