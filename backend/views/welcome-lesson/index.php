<?php

use backend\models\WelcomeLesson;
use dosamigos\datepicker\DatePicker;
use yii\grid\Column;
use yii\bootstrap\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \common\models\PaymentSearch */
/* @var $studentMap \common\models\User[] */
/* @var $subjectMap \common\models\Subject[] */
/* @var $teacherMap \common\models\Teacher[] */

$this->title = 'Пробные уроки';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="welcome-lesson-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => 'grid-view table-responsive'],
        'rowOptions' => function ($model, $index, $widget, $grid) {
            $return = [];
            switch ($model->status) {
                case WelcomeLesson::STATUS_PASSED:
                    $return['class'] = 'success';
                    break;
                case WelcomeLesson::STATUS_MISSED:
                    $return['class'] = 'warning';
                    break;
                case WelcomeLesson::STATUS_CANCELED:
                    $return['class'] = 'info';
                    break;
                case WelcomeLesson::STATUS_DENIED:
                    $return['class'] = 'danger';
                    break;
            }
            return $return;
        },
        'columns' => [
            [
                'attribute' => 'user_id',
                'content' => function ($model, $key, $index, $column) {
                    return $model->user->name;
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'user_id',
                    $studentMap,
                    ['class' => 'form-control']
                )
            ],
            [
                'attribute' => 'subject_id',
                'content' => function ($model, $key, $index, $column) {
                    return $model->subject->name;
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'subject_id',
                    $subjectMap,
                    ['class' => 'form-control']
                )
            ],
            [
                'attribute' => 'teacher_id',
                'content' => function ($model, $key, $index, $column) {
                    return $model->teacher->name;
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'teacher_id',
                    $teacherMap,
                    ['class' => 'form-control']
                )
            ],
            [
                'attribute' => 'lesson_date',
                'format' => 'datetime',
                'label' => 'Дата',
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'lessonDateString',
                    'template' => '{addon}{input}',
                    'clientOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ]),
            ],
            [
                'attribute' => 'status',
                'content' => function ($model, $key, $index, $column) {
                    return WelcomeLesson::STATUS_LABELS[$model->status];
                },
            ],
            [
                'class' => Column::class,
                'content' => function ($model, $key, $index, $column) {
                    return '<script>$(function() { WelcomeLesson.setButtons($(\'tr[data-key="' . $model->id . '"] td:last-child\'), ' . $model->id . ', ' . $model->status . ') });</script>';
                },
            ],
        ],
    ]); ?>
</div>
