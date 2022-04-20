<?php

use yii\web\View;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Slider */
/* @var $pricture_sliders common\models\PictureSlider[] */

$this->title = 'Cập nhật slider: '.$model->title;
?>
<div class="slider-update">

    <?= $this->render('_form', [
        'model' => $model,
        'pricture_sliders' => $pricture_sliders,
    ]) ?>

</div>

<?php $this->registerCssFile(Yii::$app->request->baseUrl.'/../backend/assets/plugins/lightbox2/dist/css/lightbox.min.css'); ?>

<?php $this->registerJsFile(Yii::$app->request->baseUrl.'/../backend/assets/plugins/lightbox2/dist/js/lightbox.min.js', 
    ['depends' => ['backend\assets\AppAsset'], 'position' => View::POS_END]); ?>
<?php $this->registerJsFile(Yii::$app->request->baseUrl.'/../backend/assets/js/slider.js', ['depends' => ['backend\assets\AppAsset'],
    'position' => View::POS_END]); ?>
