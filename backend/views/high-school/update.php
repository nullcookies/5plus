<?php

use yii\helpers\Html;
use \yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $highSchool common\models\HighSchool */
/* @var $label string */
/* @var $labelParent string */

$this->title = $highSchool->isNewRecord ? 'Добавить ' . $label : 'Изменить ' . $highSchool->name;
$this->params['breadcrumbs'][] = ['label' => $labelParent, 'url' => ['index']];
$this->params['breadcrumbs'][] = $highSchool->isNewRecord ? $label : $highSchool->name;
?>
<div class="highschool-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div id="messages_place"></div>

    <div class="highschool-form">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <?= $form->field($highSchool, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($highSchool, 'name_short')->textInput(['maxlength' => true]) ?>

        <?= $form->field($highSchool, 'photoFile', ['options' => ['class' => 'form-group col-xs-10']])->fileInput(['accept' => 'image/jpeg,image/png']); ?>
        <div class="col-xs-2 text-center" id="highschool_photo">
            <?php if ($highSchool->photo): ?>
                <img src="<?= $highSchool->photoUrl; ?>" style="width: 100%;">
                <a href="<?= \yii\helpers\Url::to(['high-school/delete-photo', 'id' => $highSchool->id]); ?>" onclick="return HighSchool.deletePhoto(this);" class="text-danger"><span class="glyphicon glyphicon-remove"></span> Удалить</a>
            <?php endif; ?>
        </div>
        <div class="clearfix"></div>

        <?= $form->field($highSchool, 'descriptionForEdit')->hint('Чтобы выделить часть текста цветом, заключите текст в {{текст}}')->textarea(['rows' => 6]) ?>

        <?=
        $form->field($highSchool, 'description')->widget(\dosamigos\tinymce\TinyMce::class, \backend\components\DefaultValuesComponent::getTinyMceSettings()); ?>

        <div class="form-group">
            <?= Html::submitButton($highSchool->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $highSchool->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>
