<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \common\models\PaymentSearch */
/* @var $studentMap \common\models\User[] */
/* @var $adminMap \common\models\User[] */
/* @var $groupMap \common\models\User[] */

$this->title = 'Платежи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-index">
    <div class="pull-right"><a href="<?= \yii\helpers\Url::to(['money/income']); ?>" class="btn btn-info">Внести оплату</a></div>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => 'grid-view table-responsive'],
        'rowOptions' => function ($model, $index, $widget, $grid) {
            $return = [];
            if ($model->amount > 0) {
                $return['class'] = $model->discount ? 'info' : 'success';
            } elseif ($model->amount < 0) {
                $return['class'] = $model->discount ? 'warning' : 'danger';
            }
            return $return;
        },
        'columns' => [
            [
                'attribute' => 'admin_id',
                'content' => function ($model, $key, $index, $column) {
                    /** @var \common\models\Payment $model */
                    return
                    ($model->contract_id && $model->contract->payment_type != \common\models\Contract::PAYMENT_TYPE_MANUAL
                        ? '<span class="label label-info">online</span> '
                        : ''
                    )
                    . ($model->cash_received == \common\models\Payment::STATUS_INACTIVE
                        ? '<span class="label label-danger">деньги не получены</span> '
                        : ''
                    )
                    . ($model->admin_id ? $model->admin->name : '');
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'admin_id',
                    $adminMap,
                    ['class' => 'form-control']
                )
            ],
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
                'attribute' => 'group_id',
                'label' => 'Группа',
                'content' => function ($model, $key, $index, $column) {
                    return Html::a($model->group->name, \yii\helpers\Url::to(['group/update', 'id' => $model->group_id]));
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'group_id',
                    $groupMap,
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
                'content' => function ($model, $key, $index, $column) {
                    return $model->amount
                        . ($model->amount < 0 && $model->used_payment_id === null
                            ? ' <span class="label label-warning">no</span>'
                            : ''
                        );
                },
            ],
            'comment',
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'label' => 'Дата операции',
                'filter' => \dosamigos\datepicker\DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'createDateString',
                    'template' => '{addon}{input}',
                    'clientOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ]),
            ],
        ],
    ]); ?>
</div>
