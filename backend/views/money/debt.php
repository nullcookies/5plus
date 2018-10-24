<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \backend\models\DebtSearch */
/* @var $debtorMap \backend\models\User[] */

$this->title = 'Задолженности';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="debt-index">
    <div class="pull-right"><a href="<?= \yii\helpers\Url::to(['money/income']); ?>" class="btn btn-info">Внести оплату</a></div>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'user_id',
                'content' => function ($model, $key, $index, $column) {
                    return $model->user->name;
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'user_id',
                    $debtorMap,
                    ['class' => 'form-control']
                )
            ],
            [
                'attribute' => 'group_id',
                'content' => function ($model, $key, $index, $column) {
                    return $model->group->name;
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'group_id',
                    \yii\helpers\ArrayHelper::map($groups, 'id', 'name'),
                    ['class' => 'form-control']
                )
            ],
            [
                'attribute' => 'amount',
                'filter' => \kartik\field\FieldRange::widget([
                    'model' => $searchModel,
                    'attribute1' => 'amountFrom',
                    'attribute2' => 'amountTo',
//                    'name1'=>'amountFrom',
//                    'name2'=>'amountTo',
                    'separator' => '-',
                    'template' => '{widget}',
                    'type' => \kartik\field\FieldRange::INPUT_TEXT,
                ]),
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'created_at',
                'format' => 'date',
            ],
            [
                'content' => function ($model, $key, $index, $column) {
                    return Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-usd']), \yii\helpers\Url::to(['money/income', 'user' => $model->user_id]), ['class' => 'btn btn-default', 'title' => 'Внести деньги']);
                },
            ],
        ],
    ]); ?>
</div>