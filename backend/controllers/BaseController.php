<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use yii\helpers\Url;
use yii\web\Controller;

class BaseController extends Controller
{
    public function beforeAction($action)
    {
        if(!in_array($action->id, [
            'login',
            'logout',
            'error'
        ])) {
            if(!Yii::$app->user->isGuest){
                $user = User::findOne(Yii::$app->user->id);
                if($user->status == 0)
                    $this->redirect(Url::toRoute('site/logout'));
            }
        }

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }
}
