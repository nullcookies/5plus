<?php

use yii\bootstrap\ActiveForm;


/* @var $this yii\web\View */
/* @var $pupil \backend\models\User */
/* @var $groups \backend\models\Group[] */
/* @var $groupData array */

$this->title = 'Добавить студента в группу';
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

/** @var bool $addGroup */
$addGroup = array_key_exists('add', $groupData) && $groupData['add'];

?>

<div class="pupil-add-to-group">
    <h1><?= $pupil->name; ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group">
        <label for="group">Группа</label>
        <select class="form-control" id="group" name="group[id]">
            <?php foreach ($groups as $group): ?>
                <option value="<?= $group->id; ?>" <?= array_key_exists('id', $groupData) && intval($groupData['id']) == $group->id ? 'selected' : ''; ?>>
                    <?= $group->name; ?> (с <?= $group->startDateObject->format('d.m.Y') . ($group->endDateObject ? "по {$group->endDateObject->format('d.m.Y')}" : ''); ?>) <?=$group->price3Month; ?> за 3 месяца
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="group_date_from">Начало занятий</label>
        <?= \dosamigos\datepicker\DatePicker::widget([
            'name' => 'group[date_from]',
            'value' => array_key_exists('date_from', $groupData) ? $groupData['date_from'] : date('d.m.Y'),
            'options' => ['id' => 'group_date_from', 'required' => true],
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'dd.mm.yyyy',
                'language' => 'ru',
                'weekStart' => 1,
            ]
        ]);?>
    </div>

    <button class="btn btn-primary">добавить</button>

    <?php ActiveForm::end(); ?>
</div>