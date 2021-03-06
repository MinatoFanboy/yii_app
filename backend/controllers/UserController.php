<?php

namespace backend\controllers;

use common\models\exports\exportUser;
use common\models\myAPI;
use common\models\Role;
use common\models\searchs\UserSearch;
use common\models\User;
use common\models\UserRole;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\VarDumper;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use \yii\web\Response;

class UserController extends BaseController
{
    public function behaviors()
    {
        $arr_action = ['index', 'create', 'update', 'delete', 'detail', 'download', 'upload'];
        $rules = [];
        foreach ($arr_action as $item) {
            $rules[] = [
                'actions' => [$item],
                'allow' => true,
                'matchCallback' => function ($rule, $action) {
                    $action_name = strtolower(str_replace('action', '', $action->id));
                    return myAPI::isAccess2('User', $action_name);
                },
            ];
        }

        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => $rules,
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /** index */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /** create */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new User();
        $model->type = User::THANH_VIEN;
        $roles = ArrayHelper::map(Role::find()->andWhere(['status' => myAPI::ACTIVE])->andWhere(['<>', 'id', 1])->all(), 'id', 'name');

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Th??m ng?????i d??ng",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                        'roles' => $roles,
                    ]),
                    'footer' => Html::button('<i class="fas fa-times"></i> ????ng l???i', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('<i class="fas fa-save"></i> L??u l???i', ['class' => 'btn btn-primary', 'type' => "submit"]),

                ];
            } else if ($model->load($request->post())) {
                $oldModel = User::findOne(['username' => $model->username, 'status' => User::STATUS_DELETED]);
                if (!is_null($oldModel)) {
                    $oldModel->password_hash = $model->password_hash;
                    $oldModel->name = $model->name;
                    $oldModel->phone = $model->phone;
                    $oldModel->email = $model->email;
                    $oldModel->status = User::STATUS_ACTIVE;
                    $oldModel->roles = $model->roles;
                    if ($oldModel->save()) {
                        return [
                            'forceReload' => '#crud-datatable-pjax',
                            'title' => "Th??m ng?????i d??ng",
                            'content' => '<span class="text-success">???? th??m ng?????i d??ng th??nh c??ng!</span>',
                            'footer' => Html::button('<i class="fa fa-close"></i> ????ng l???i', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('<i class="glyphicon glyphicon-plus"></i> Th??m ti???p', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote']),

                        ];
                    } else {
                        throw new HttpException(500, Html::errorSummary($oldModel));
                    }
                } else {
                    if ($model->save()) {
                        return [
                            'forceReload' => '#crud-datatable-pjax',
                            'title' => "Th??m ng?????i d??ng",
                            'content' => '<span class="text-success">Th??m ng?????i d??ng th??nh c??ng!</span>',
                            'footer' => Html::button('<i class="fas fa-times"></i> ????ng l???i', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('<i class="glyphicon glyphicon-plus"></i> Th??m ti???p', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote']),
                        ];
                    }
                }
            } else {
                return [
                    'title' => "Th??m ng?????i d??ng",
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                        'roles' => $roles,
                    ]),
                    'footer' => Html::button('<i class="fas fa-times"></i> ????ng l???i', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('<i class="fas fa-save"></i> L??u l???i', ['class' => 'btn btn-primary', 'type' => "submit"]),

                ];
            }
        } else {
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }

    }

    /** update */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $model->roles = ArrayHelper::map(UserRole::findAll(['user_id' => $model->id]), 'id', 'role_id');
        $roles = ArrayHelper::map(Role::find()->andWhere(['status' => myAPI::ACTIVE])->andWhere(['<>', 'id', 1])->all(), 'id', 'name');

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "C???p nh???t ng?????i d??ng: " . $model->username,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                        'roles' => $roles,
                    ]),
                    'footer' => Html::button('<i class="fas fa-times"></i> ????ng l???i', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('<i class="fas fa-save"></i> L??u l???i', ['class' => 'btn btn-primary', 'type' => "submit"]),
                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => "C???p nh???t ng?????i d??ng: " . $model->username,
                    'content' => '<span class="text-success">???? c???p nh???t ng?????i d??ng th??nh c??ng!</span>',
                    'footer' => Html::button('<i class="fas fa-times"></i> ????ng l???i', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('<i class="fas fa-edit"></i> C???p nh???t', ['update', 'id' => $id], ['class' => 'btn btn-primary', 'role' => 'modal-remote']),
                ];
            } else {
                return [
                    'title' => "C???p nh???t ng?????i d??ng: " . $model->username,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                        'roles' => $roles,
                    ]),
                    'footer' => Html::button('<i class="fas fa-times"></i> ????ng l???i', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('<i class="fas fa-save"></i> L??u l???i', ['class' => 'btn btn-primary', 'type' => "submit"]),
                ];
            }
        } else {
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                    'roles' => $roles,
                ]);
            }
        }
    }

    /** delete */
    public function actionDelete()
    {
        if (Yii::$app->request->isAjax) {
            if (isset($_POST['id'])) {
                $model = $this->findModel($_POST['id']);
                $model->updateAttributes(['status' => User::STATUS_DELETED]);

                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => 'X??a b???n ghi!',
                    'content' => '???? x??a b???n ghi th??nh c??ng',
                ];
            } else {
                throw new NotFoundHttpException('Kh??ng x??c th???c ???????c d??? li???u');
            }
        } else {
            throw new NotFoundHttpException('???????ng d???n sai c?? ph??p');
        }
    }

    /** detail */
    public function actionDetail()
    {
        if (Yii::$app->request->isAjax) {
            if (isset($_POST['id'])) {
                $model = $this->findModel($_POST['id']);

                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => 'Th??ng tin ng?????i d??ng',
                    'content' => $this->renderAjax('detail', [
                        'model' => $model,
                    ]),
                ];
            } else {
                throw new NotFoundHttpException('Ng?????i d??ng kh??ng t???n t???i');
            }
        } else {
            throw new NotFoundHttpException('???????ng d???n sai c?? ph??p');
        }
    }

    /** download */
    public function actionDownload()
    {
        if (Yii::$app->request->isAjax) {
            $users = User::find()->all();

            $export = new exportUser();
            $export->data = $users;
            $export->path = dirname(dirname(__DIR__)) . '/excels/';
            $file_name = $export->init();
            $file = str_replace('index.php', '', Yii::$app->request->baseUrl) . '/../excels/' . $file_name;

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'T???i file k???t qu???',
                'link' => Html::a('<i class="fa fa-cloud-download"></i> Nh???n v??o ????y ????? t???i file v???!', $file, ['class' => 'text-primary', 'target' => '_blank']),
            ];
        } else {
            throw new HttpException(500, '???????ng d???n sai c?? ph??p');
        }
    }

    /** upload */
    public function actionUpload()
    {
        if (Yii::$app->request->isAjax) {
            $file = UploadedFile::getInstanceByName('file');
            $path = dirname(dirname(__DIR__)) . '/uploads/';
            $fileName = myAPI::createCode($file->name);
            $filePath = $path . date('Y/m/d') . '/' . $fileName;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            try {
                if (FileHelper::createDirectory($path . date('Y/m/d') . '/', $mode = 0775, $recursive = true)) {
                    $file->saveAs($filePath);
                }
            } catch (\Exception $ex) {
                if (is_file($filePath)) {
                    unlink($filePath);
                }
                throw new HttpException(500, $ex->getMessage());
            }
            
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (is_file($filePath)) {
                $reader = IOFactory::createReaderForFile($filePath);
                $spreadsheet = $reader->load($filePath);

                $activeSheet = $spreadsheet->getActiveSheet();
                $highestRow = $activeSheet->getHighestRow();
                for ($row = 4; $row <= $highestRow; $row++) {
                    $tempt = [];
                    $tempt['username'] = (string) trim($activeSheet->getCell('B' . $row)->getValue());
                    $tempt['password_hash'] = (string) trim($activeSheet->getCell('C' . $row)->getValue());
                    $tempt['ho_ten'] = (string) trim($activeSheet->getCell('D' . $row)->getValue());
                    $tempt['dien_thoai'] = (string) trim($activeSheet->getCell('E' . $row)->getValue());
                    $tempt['email'] = (string) trim($activeSheet->getCell('F' . $row)->getValue());
                    $data[] = $tempt;
                }
                VarDumper::dump($data, $depth = 10, $highlight = true);exit();

                return [
                    'title' => 'Upload th??ng tin user',
                    'content' => '???? upload th??ng tin user th??nh c??ng',
                ];
            } else {
                return [
                    'title' => 'Upload th??ng tin user',
                    'content' => 'C?? l???i x???y ra. Vui l??ng th??? l???i',
                ];
            }
        } else {
            throw new NotFoundHttpException(500, '???????ng d???n sai c?? ph??p.');
        }
    }

    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id, 'type' => User::THANH_VIEN])) !== null) {
            if ($id === 1 && Yii::$app->user->id !== 1) {
                throw new NotFoundHttpException('B???n kh??ng ???????c ph??p s???a ng?????i d??ng n??y');
            } else {
                return $model;
            }
        } else {
            throw new NotFoundHttpException('Ng?????i d??ng kh??ng t???n t???i');
        }
    }
}
