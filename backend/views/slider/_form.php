<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Slider */
/* @var $form yii\widgets\ActiveForm */
/* @var $pricture_sliders yii\widgets\PictureSlider[] */
?>

<div class="slider-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'link')->textInput() ?>
        </div>
    </div>

    <?= $form->field($model, 'content')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'pictures[]')->fileInput(['accept' => 'image/*', 'multiple' => 'multiple']) ?>

    <?php if ($model->isNewRecord):  ?>
        <div class="row">
            <?php foreach ($pricture_sliders as $picture_slider): ?>
                <div class="col-md-2 picture-preview text-center">
                    <img src="../images/slider/<?= $picture_slider->file ?>" width="150px">
                    <div class="picture-preview-activity">
                        <a class="example-image-link text-muted" href="../images/slider/<?= $picture_slider->file ?>" data-lightbox="example-set">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="#" class="delete-picture-preview text-muted" data-value="<?= $picture_slider->id ?>">
                            <i class="fas fa-trash-restore"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  
	<div class="form-group text-right">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fas fa-save"></i> Thêm mới' : '<i class="fas fa-save"></i> Cập nhật', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>

    <?php ActiveForm::end(); ?>
    
</div>
