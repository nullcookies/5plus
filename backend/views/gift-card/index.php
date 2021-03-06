<?php

use common\models\GiftCard;
use common\models\GiftCardSearch;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
/* @var $searchModel GiftCardSearch */
/* @var $status string */

$this->title = 'Предоплаченные карты';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gift-card-type-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <ul class="nav nav-pills">
        <li role="presentation" <?= $status === null ? 'class="active"' : ''; ?>>
            <a href="<?= Url::to(['gift-card/index']); ?>">Все</a>
        </li>
        <li role="presentation" <?= $status === GiftCard::STATUS_NEW ? 'class="active"' : ''; ?>>
            <a href="<?= Url::to(['gift-card/index', 'status' => GiftCard::STATUS_NEW]); ?>">Не оплаченные</a>
        </li>
        <li role="presentation" <?= $status == GiftCard::STATUS_PAID ? 'class="active"' : ''; ?>>
            <a href="<?= Url::to(['gift-card/index', 'status' => GiftCard::STATUS_PAID]); ?>">Оплаченные</a>
        </li>
        <li role="presentation" <?= $status == GiftCard::STATUS_USED ? 'class="active"' : ''; ?>>
            <a href="<?= Url::to(['gift-card/index', 'status' => GiftCard::STATUS_USED]); ?>">Активированные</a>
        </li>
    </ul>
    <hr>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function ($model, $index, $widget, $grid) {
            $return  = [];
            switch ($model->status) {
                case GiftCard::STATUS_PAID:
                    $return['class'] = 'success';
                    break;
                case GiftCard::STATUS_USED:
                    $return['class'] = 'warning';
                    break;
            }
            return $return;
        },
        'columns' => [
            'name',
            'amount',
            'customer_name',
            [
                'attribute' => 'customer_phone',
                'content' => function ($model, $key, $index, $column) {
                    return "<span class='text-nowrap'>{$model->phoneFull}</span>";
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'filter' => DatePicker::widget([
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
            [
                'attribute' => 'paid_at',
                'format' => 'datetime',
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'paidDateString',
                    'template' => '{addon}{input}',
                    'clientOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ]),
            ],
            [
                'attribute' => 'used_at',
                'format' => 'datetime',
                'filter' => DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'usedDateString',
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
