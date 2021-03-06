<?php

use common\components\WidgetHtml;
use frontend\components\widgets\ReviewCarouselWidget;
use frontend\components\widgets\SubjectCarouselWidget;
use frontend\components\widgets\TeacherCarouselWidget;
use yii\bootstrap\Html;
use \himiklab\yii2\recaptcha\ReCaptcha;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $page common\models\Page */
/* @var $subjectCategoryCollection \common\models\SubjectCategory[] */
/* @var $webpage \common\models\Webpage */
/* @var $quizWebpage \common\models\Webpage */
/* @var $paymentWebpage \common\models\Webpage */

$this->registerJs('MainPage.init(' . count($subjectCategoryCollection) . ');');

//unset($this->params['h1']);

?>
</div>

<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-4 text-center">
            <p>
                <a class="btn btn-lg btn-primary" href="<?= Url::to(['webpage', 'id' => $quizWebpage->id]); ?>">
                    Проверь свои знания
                </a>
            </p>
        </div>
        <div class="col-xs-12 col-sm-4 text-center">
            <p>
                <a tabindex="0" class="btn btn-lg btn-primary" role="button" data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="focus" data-html="1" data-content="<a class='btn btn-primary' href='<?= Url::to(['webpage', 'id' => $paymentWebpage->id, 'type' => 'pupil']); ?>'>для учащихся</a><br><br> <a class='btn btn-primary' href='<?= Url::to(['webpage', 'id' => $paymentWebpage->id, 'type' => 'new']); ?>'>для новых студентов</a>">Онлайн-оплата</a>
            </p>
        </div>
        <div class="col-xs-12 col-sm-4 text-center">
            <p>
                <?= YII_ENV == 'prod' ? WidgetHtml::getByName('callback') : ''; ?>
            </p>
        </div>
    </div>
</div>

<?php
    $i = 0;
    foreach ($subjectCategoryCollection as $subjectCategory):
?>
    <?php if ($i % 2 == 0): ?>
        <div class="clouds-line-bottom"></div>
        <div class="light-block">
    <?php endif; ?>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <?= $i == 0 ? $page->content : ''; ?>
                <?= SubjectCarouselWidget::widget(['subjectCategory' => $subjectCategory, 'buttonLeft' => $i % 2 == 1, 'index' => $i]); ?>
            </div>
        </div>
    </div>
    <?php if ($i % 2 == 0): ?>
        </div>
        <div class="clouds-line-top"></div>
    <?php endif; ?>
<?php
    $i++;
    endforeach;
?>

<div class="clouds-line-bottom"></div>
<div class="light-block">
    <div class="container">
        <?= TeacherCarouselWidget::widget(); ?>
    </div>
</div>
<div class="clouds-line-top"></div>

<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <?= ReviewCarouselWidget::widget(); ?>
        </div>
    </div>
</div>

<div id="order_form" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?= Html::beginForm(
                    Url::to(['order/create']),
                    'post',
                    [
                        'onsubmit' => 'fbq("track", "Lead", {content_name: $("#order-subject").find("option:selected").text()}); return MainPage.completeOrder(this);'
                    ]
            ); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                <h4 class="modal-title">Записаться на курс</h4>
            </div>
            <div class="modal-body">
                <div id="order_form_body">
                    <div class="form-group">
                        <label for="order-name">Ваше имя</label>
                        <input name="order[name]" id="order-name" class="form-control" required minlength="2" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="order-subject">Предмет</label>
                        <select name="order[subject]" id="order-subject" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label for="order-phone">Ваш номер телефона</label>
                        <div class="input-group"><span class="input-group-addon">+998</span>
                            <input type="tel" name="order[phoneFormatted]" id="order-phone" class="form-control" maxlength="11" pattern="\d{2} \d{3}-\d{4}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="order-comment">Дополнительные сведения, пожелания</label>
                        <textarea name="order[user_comment]" id="order-comment" class="form-control" maxlength="255"></textarea>
                    </div>
                    <?= ReCaptcha::widget(['name' => 'order[reCaptcha]']) ?>
                </div>
                <div id="order_form_extra" class="hidden"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">отмена</button>
                <button class="btn btn-primary">записаться</button>
            </div>
            <?= Html::endForm(); ?>
        </div>
    </div>
</div>
<div class="container">
