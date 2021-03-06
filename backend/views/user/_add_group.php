<?php

use backend\components\DefaultValuesComponent;
use dosamigos\datepicker\DatePicker;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $groups \common\models\Group[] */
/* @var $subjects \common\models\Subject[] */
/* @var $groupData array */
/* @var $paymentData array */
/* @var $contractData array */
/* @var $welcomeLessonData array */
/* @var $companies \common\models\Company[] */
/* @var $amount int */
/* @var $companyId int */
/* @var $incomeAllowed bool */
/* @var $contractAllowed bool */

/** @var bool $addGroup */
$addGroup = array_key_exists('add', $groupData) && $groupData['add'];
$addPayment = array_key_exists('add', $paymentData) && $paymentData['add'];
$addContract = array_key_exists('add', $contractData) && $contractData['add'];
$addWelcomeLesson = array_key_exists('add', $welcomeLessonData) && $welcomeLessonData['add'];

$getGroupOptionsList = function(int $selectedValue) use ($groups): string {
    $list = '';
    foreach ($groups as $group) {
        $list .= "<option value=\"{$group->id}\" " . ($selectedValue == $group->id ? 'selected' : '')
            . " data-price=\"{$group->priceMonth}\" data-price3=\"{$group->price3Month}\">{$group->name} (с {$group->startDateObject->format('d.m.Y')}"
            . ($group->endDateObject ? "по {$group->endDateObject->format('d.m.Y')}" : '')
            . ")</option>";
    }
    return $list;
};

$this->registerJs('User.init();');
?>

<div class="checkbox">
    <label>
        <input type="checkbox" id="welcome_lesson_switch" value="1" name="welcome_lesson[add]" onchange="User.checkWelcomeLesson(this);" <?= $addWelcomeLesson ? 'checked' : ''; ?> autocomplete="off">
        Пробный урок
    </label>
</div>

<div id="add_welcome_lesson" <?= $addWelcomeLesson ? '' : 'class="hidden"'; ?>>
    <div class="form-group">
        <label for="welcome_lesson_group">Группа</label>
        <select class="form-control" id="welcome_lesson_group" name="welcome_lesson[group_id]" onchange="User.setWelcomeLessonGroup(this);">
            <option value="">Неизвестна</option>
            <?= $getGroupOptionsList(array_key_exists('id', $groupData) ? intval($groupData['id']) : 0); ?>
        </select>
    </div>
    <div id="welcome_lesson_custom" <?= array_key_exists('id', $groupData) ? 'class="hidden"' : ''; ?>>
        <div class="form-group">
            <label for="welcome_lesson_subject">Предмет</label>
            <select class="form-control" id="welcome_lesson_subject" name="welcome_lesson[subject_id]" onchange="User.loadTeacherSelect(this);"
                <?= !empty($welcomeLessonData['group_id']) ? 'disabled' : ''; ?>>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= $subject->id; ?>" <?= !empty($welcomeLessonData['subject_id']) && $welcomeLessonData['subject_id'] == $subject->id ? 'selected' : ''; ?>><?= $subject->name; ?> (<?= $subject->subjectCategory->name; ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="welcome_lesson_teacher">Учитель</label>
            <select class="form-control" id="welcome_lesson_teacher" name="welcome_lesson[teacher_id]"
                <?= !empty($welcomeLessonData['group_id']) ? 'disabled' : ''; ?>></select>
        </div>
    </div>
    <div class="form-group">
        <label for="welcome_lesson_date">Дата</label>
        <?= DatePicker::widget(array_merge(
                DefaultValuesComponent::getDatePickerSettings(),
                [
                    'name' => 'welcome_lesson[date]',
                    'value' => array_key_exists('date', $welcomeLessonData) ? $welcomeLessonData['date'] : date('d.m.Y'),
                    'options' => ['id' => 'welcome_lesson_date', 'required' => true, 'disabled' => !$addWelcomeLesson],
                ]
        ));?>
    </div>
</div>

<div class="checkbox">
    <label>
        <input type="checkbox" id="add_group_switch" value="1" name="group[add]" onchange="User.checkAddGroup(this);" <?= $addGroup ? 'checked' : ''; ?> autocomplete="off">
        Добавить в группу
    </label>
</div>

<div id="add_group" <?= $addGroup ? '' : 'class="hidden"'; ?>>
    <div class="form-group">
        <label for="group">Группа</label>
        <select class="form-control" id="group" name="group[id]" onchange="User.setAmountHelperButtons(this, true);">
            <?= $getGroupOptionsList(array_key_exists('id', $groupData) ? intval($groupData['id']) : 0); ?>
        </select>
    </div>
    <div class="form-group">
        <label for="group_date_from">Начало занятий</label>
        <?= DatePicker::widget(array_merge(
                DefaultValuesComponent::getDatePickerSettings(),
                [
                    'name' => 'group[date_from]',
                    'value' => array_key_exists('date_from', $groupData) ? $groupData['date_from'] : date('d.m.Y'),
                    'options' => ['id' => 'group_date_from', 'required' => true, 'disabled' => !$addGroup],
                ]
        ));?>
    </div>
</div>

<div class="row">
    <?php if ($incomeAllowed): ?>
        <div class="col-xs-12 col-sm-6">
            <div class="checkbox">
                <label>
                    <input type="checkbox" value="1" name="payment[add]" id="add_payment_switch" onchange="User.checkAddPayment(this);" <?= $addPayment ? 'checked' : ($addGroup ? '' :  'disabled'); ?> autocomplete="off">
                    Внести платёж
                </label>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($contractAllowed): ?>
        <div class="col-xs-12 col-sm-6">
            <div class="checkbox">
                <label>
                    <input type="checkbox" value="1" name="contract[add]" id="add_contract_switch" onchange="User.checkAddContract(this);" <?= $addContract ? 'checked' : ''; ?> autocomplete="off">
                    Добавить договор
                </label>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="add_payment" <?= $addPayment ? '' : 'class="hidden"'; ?>>
    <div class="form-group">
        <label for="comment">Комментарий к платежу</label>
        <input id="comment" class="form-control" name="payment[comment]"
            <?= array_key_exists('comment', $paymentData) ? 'value="' . $paymentData['comment'] . '"' : ''; ?>>
    </div>
</div>

<div id="add_contract" <?= $addContract ? '' : 'class="hidden"'; ?>>
    <div id="contract_group_block" class="form-group <?= $addGroup ? 'class="hidden"' : ''; ?>">
        <label for="contract_group">Группа</label>
        <select id="contract_group" class="form-control" name="contract[group]" onchange="User.setAmountHelperButtons(this, true);">
            <?= $getGroupOptionsList(array_key_exists('group', $contractData) ? intval($contractData['group']) : 0); ?>
        </select>
    </div>
</div>

<div id="amount_block" <?= $addPayment || $addContract ? '' : 'class="hidden"'; ?>>
    <div class="form-group">
        <label for="amount">Сумма</label>
        <input id="amount" class="form-control" name="amount" type="number" step="1000" min="1000" required
            <?= $amount ? 'value="' . $amount . '"' : 'disabled'; ?>>
        <div id="amount_helper_buttons"></div>
    </div>
    <?= Html::radioList(
        'company_id',
        $companyId,
        ArrayHelper::map($companies, 'id', 'second_name'),
        ['class' => 'form-group', 'itemOptions' => ['required' => true, 'disabled' => !$addPayment && !$addContract]]
    ); ?>
</div>
