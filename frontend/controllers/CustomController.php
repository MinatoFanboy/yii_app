<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\myAPI;
use common\models\Keyword;
use yii\helpers\VarDumper;

class CustomController extends Controller {
    public function beforeAction($action)
    {
        $this->view->params['keywords'] = Keyword::find()->andWhere(['active' => myAPI::ACTIVE])->limit(4)->all();

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }
}