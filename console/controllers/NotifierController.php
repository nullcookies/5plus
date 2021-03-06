<?php

namespace console\controllers;

use common\components\ComponentContainer;
use common\components\helpers\WordForm;
use common\components\paygram\PaygramApiException;
use common\components\PaymentComponent;
use common\components\telegram\Request;
use common\components\telegram\text\PublicMain;
use common\models\BotPush;
use common\models\GroupPupil;
use common\models\Notify;
use common\models\User;
use yii;
use yii\console\Controller;

/**
 * NotifierController is used to send notifications to users.
 */
class NotifierController extends Controller
{
    const QUANTITY_LIMIT = 40;
    const TIME_LIMIT = 50;

    /**
     * Search for a not sent notifications and sends it.
     * @return int
     * @throws \Longman\TelegramBot\Exception\TelegramException
     * @throws yii\db\Exception
     */
    public function actionSend()
    {
        $currentTime = intval(date('H'));
        if ($currentTime >= 20 || $currentTime < 9) return yii\console\ExitCode::OK;

        $condition = ['status' => Notify::STATUS_NEW];

        $tryTelegram = false;
        if (array_key_exists('telegramPublic', \Yii::$app->components)) {
            \Yii::$app->db->open();
            ComponentContainer::getTelegramPublic()->telegram;
            $tryTelegram = true;
        }

        $quantity = 0;
        $startTime = microtime(true);
        while (true) {
            $currentTime = microtime(true);
            if($quantity >= self::QUANTITY_LIMIT || $currentTime - $startTime > self::TIME_LIMIT) break;
            
            $toSend = Notify::findOne($condition);
            if (!$toSend) {
                sleep(1);
                continue;
            }

            $toSend->status = Notify::STATUS_SENDING;
            $toSend->save();

            $sendSms = true;
            if ($tryTelegram && $toSend->user->tg_chat_id && $toSend->user->telegramSettings['subscribe']) {
                $message = null;
                switch ($toSend->template_id) {
                    case Notify::TEMPLATE_PUPIL_DEBT:
                        $message = 'У вас задолженность в группе *' . Request::escapeMarkdownV2($toSend->group->legal_name) . '*'
                            . Request::escapeMarkdownV2(" - {$toSend->parameters['debt']} " . WordForm::getLessonsForm($toSend->parameters['debt']) . '.')
                            . ' [' . PublicMain::PAY_ONLINE . '](' . PaymentComponent::getPaymentLink($toSend->user_id, $toSend->group_id)->url . ')';
                        break;
                    case Notify::TEMPLATE_PUPIL_LOW:
                        $message = 'В группе *' . Request::escapeMarkdownV2($toSend->group->legal_name) . '*'
                            . Request::escapeMarkdownV2(" у вас осталось {$toSend->parameters['paid_lessons']} " . WordForm::getLessonsForm($toSend->parameters['paid_lessons']) . '.')
                            . ' [' . PublicMain::PAY_ONLINE . '](' . PaymentComponent::getPaymentLink($toSend->user_id, $toSend->group_id)->url . ')';
                        break;
                    case Notify::TEMPLATE_PARENT_DEBT:
                        $child = User::findOne($toSend->parameters['child_id']);
                        $message = 'У студента ' . Request::escapeMarkdownV2($toSend->user->telegramSettings['trusted'] ? $child->name : $child->nameHidden)
                            . ' задолженность в группе *' . Request::escapeMarkdownV2($toSend->group->legal_name) . '*'
                            . Request::escapeMarkdownV2(" - {$toSend->parameters['debt']} " . WordForm::getLessonsForm($toSend->parameters['debt']) . '.')
                            . ' [' . PublicMain::PAY_ONLINE . '](' . PaymentComponent::getPaymentLink($child->id, $toSend->group_id)->url . ')';
                        break;
                    case Notify::TEMPLATE_PARENT_LOW:
                        $child = User::findOne($toSend->parameters['child_id']);
                        $message = 'У студента ' . Request::escapeMarkdownV2($toSend->user->telegramSettings['trusted'] ? $child->name : $child->nameHidden)
                            . ' в группе *' . Request::escapeMarkdownV2($toSend->group->legal_name) . '*'
                            . Request::escapeMarkdownV2(" осталось {$toSend->parameters['paid_lessons']} " . WordForm::getLessonsForm($toSend->parameters['paid_lessons']) . '.')
                            . ' [' . PublicMain::PAY_ONLINE . '](' . PaymentComponent::getPaymentLink($child->id, $toSend->group_id)->url . ')';
                        break;
                }
                if ($message) {
                    $sendSms = false;
                    $push = new BotPush();
                    $push->chat_id = $toSend->user->tg_chat_id;
                    $push->messageArray = [
                        'text' => $message,
                        'parse_mode' => 'MarkdownV2',
                        'disable_web_page_preview' => true,
                    ];
                    if ($push->save()) {
                        $toSend->status = Notify::STATUS_SENT;
                        $toSend->sent_at = date('Y-m-d H:i:s');
                        $toSend->save();
                    } else {
                        ComponentContainer::getErrorLogger()->logError('notify/send', $push->getErrorsAsString(), true);
                        $toSend->status = Notify::STATUS_ERROR;
                        $toSend->save();
                    }
                }
            }

            if ($sendSms) {
                try {
                    $params = [];
                    switch ($toSend->template_id) {
                        case Notify::TEMPLATE_PUPIL_DEBT:
                            $params['group_name'] = $toSend->group->legal_name;
                            $params['debt'] = $toSend->parameters['debt'] . ' ' . WordForm::getLessonsForm($toSend->parameters['debt']);
                            $params['link'] = PaymentComponent::getPaymentLink($toSend->user_id, $toSend->group_id)->url;
                            break;
                        case Notify::TEMPLATE_PUPIL_LOW:
                            $params['group_name'] = $toSend->group->legal_name;
                            $params['paid_lessons'] = $toSend->parameters['paid_lessons'] . ' ' . WordForm::getLessonsForm($toSend->parameters['paid_lessons']);
                            $params['link'] = PaymentComponent::getPaymentLink($toSend->user_id, $toSend->group_id)->url;
                            break;
                        case Notify::TEMPLATE_PARENT_DEBT:
                            $child = User::findOne($toSend->parameters['child_id']);
                            $params['student_name'] = $child->name;
                            $params['group_name'] = $toSend->group->legal_name;
                            $params['debt'] = $toSend->parameters['debt'] . ' ' . WordForm::getLessonsForm($toSend->parameters['debt']);
                            $params['link'] = PaymentComponent::getPaymentLink($child->id, $toSend->group_id)->url;
                            break;
                        case Notify::TEMPLATE_PARENT_LOW:
                            $child = User::findOne($toSend->parameters['child_id']);
                            $params['student_name'] = $child->name;
                            $params['group_name'] = $toSend->group->legal_name;
                            $params['paid_lessons'] = $toSend->parameters['paid_lessons'] . ' ' . WordForm::getLessonsForm($toSend->parameters['paid_lessons']);
                            $params['link'] = PaymentComponent::getPaymentLink($child->id, $toSend->group_id)->url;
                            break;
                    }

                    if ($toSend->user->phone) {
                        ComponentContainer::getPaygramApi()
                            ->sendSms($toSend->template_id, substr($toSend->user->phone, -12, 12), $params);
                    }
                    $toSend->status = Notify::STATUS_SENT;
                    $toSend->sent_at = date('Y-m-d H:i:s');
                } catch (PaygramApiException $exception) {
                    $toSend->status = Notify::STATUS_ERROR;
                    ComponentContainer::getErrorLogger()
                        ->logError('notifier/send', $exception->getMessage(), true);
                }
                $toSend->save();
                $quantity++;
            }
        }
        return yii\console\ExitCode::OK;
    }

    /**
     * Create notifications when needed
     * @return int
     */
    public function actionCreate()
    {
        $monthLimit = new \DateTime('-30 days');

        /** @var GroupPupil[] $groupPupils */
        $groupPupils = GroupPupil::find()
            ->joinWith('user')
            ->andWhere([GroupPupil::tableName() . '.active' => GroupPupil::STATUS_ACTIVE])
            ->andWhere(['<', GroupPupil::tableName() . '.paid_lessons', 0])
            ->andWhere(['!=', User::tableName() . '.status', User::STATUS_LOCKED])
            ->with('group')
            ->all();
        foreach ($groupPupils as $groupPupil) {

            /*----------------------  TEMPLATE ID 1 ---------------------------*/
            /** @var Notify $queuedNotification */
            $queuedNotification = Notify::find()
                ->andWhere(['user_id' => $groupPupil->user_id, 'group_id' => $groupPupil->group_id, 'template_id' => Notify::TEMPLATE_PUPIL_DEBT])
                ->andWhere(['!=', 'status', Notify::STATUS_SENT])
                ->one();
            if (!$queuedNotification) {
                /** @var Notify[] $sentNotifications */
                $sentNotifications = Notify::find()
                    ->andWhere(['user_id' => $groupPupil->user_id, 'group_id' => $groupPupil->group_id, 'template_id' => Notify::TEMPLATE_PUPIL_DEBT])
                    ->andWhere(['status' => Notify::STATUS_SENT])
                    ->andWhere(['>', 'sent_at', $monthLimit->format('Y-m-d H:i:s')])
                    ->orderBy(['sent_at' => SORT_DESC])
                    ->all();
                $needSent = true;
                if (!empty($sentNotifications)) {
                    $lastNotification = reset($sentNotifications);
                    $needSent = (date_diff(new \DateTime('now'), $lastNotification->sentDate)->days >= pow(2, count($sentNotifications) - 1));
                }

                if ($needSent) {
                    $lessonDebt = GroupPupil::find()
                        ->andWhere(['user_id' => $groupPupil->user_id, 'group_id' => $groupPupil->group_id])
                        ->andWhere(['<', 'paid_lessons', 0])
                        ->select('SUM(paid_lessons)')
                        ->scalar();
                    ComponentContainer::getNotifyQueue()->add(
                        $groupPupil->user,
                        Notify::TEMPLATE_PUPIL_DEBT,
                        ['debt' => abs($lessonDebt)],
                        $groupPupil->group
                    );
                }
            }
            /*----------------------  END TEMPLATE ID 1 ---------------------------*/

            /*----------------------  TEMPLATE ID 2 ---------------------------*/
            if ($groupPupil->user->parent_id) {
                $parent = $groupPupil->user->parent;
                /** @var Notify[] $queuedNotificationsDraft */
                $queuedNotificationsDraft = Notify::find()
                    ->andWhere(['user_id' => $parent->id, 'group_id' => $groupPupil->group_id, 'template_id' => Notify::TEMPLATE_PARENT_DEBT])
                    ->andWhere(['!=', 'status', Notify::STATUS_SENT])
                    ->all();
                $queuedNotifications = [];
                foreach ($queuedNotificationsDraft as $notification) {
                    if ($notification->parameters['child_id'] == $groupPupil->user_id) $queuedNotifications[] = $notification;
                }

                if (empty($queuedNotifications)) {
                    /** @var Notify[] $sentNotificationsDraft */
                    $sentNotificationsDraft = Notify::find()
                        ->andWhere(['user_id' => $parent->id, 'group_id' => $groupPupil->group_id, 'template_id' => Notify::TEMPLATE_PARENT_DEBT])
                        ->andWhere(['status' => Notify::STATUS_SENT])
                        ->andWhere(['>', 'sent_at', $monthLimit->format('Y-m-d H:i:s')])
                        ->orderBy(['sent_at' => SORT_DESC])
                        ->all();
                    /** @var Notify[] $sentNotifications */
                    $sentNotifications = [];
                    foreach ($sentNotificationsDraft as $notification) {
                        if ($notification->parameters['child_id'] == $groupPupil->user_id) $sentNotifications[] = $notification;
                    }

                    $needSent = true;
                    if (!empty($sentNotifications)) {
                        $lastNotification = reset($sentNotifications);
                        $needSent = (date_diff(new \DateTime('now'), $lastNotification->sentDate)->days >= pow(2, count($sentNotifications) - 1));
                    }

                    if ($needSent) {
                        $lessonDebt = GroupPupil::find()
                            ->andWhere(['user_id' => $groupPupil->user_id, 'group_id' => $groupPupil->group_id])
                            ->andWhere(['<', 'paid_lessons', 0])
                            ->select('SUM(paid_lessons)')
                            ->scalar();
                        ComponentContainer::getNotifyQueue()->add(
                            $parent,
                            Notify::TEMPLATE_PARENT_DEBT,
                            ['debt' => abs($lessonDebt), 'child_id' => $groupPupil->user_id],
                            $groupPupil->group
                        );
                    }
                }
            }
            /*----------------------  END TEMPLATE ID 2 ---------------------------*/
        }

        $nextWeek = new \DateTime('+7 days');
        /** @var GroupPupil[] $groupPupils */
        $groupPupils = GroupPupil::find()
            ->joinWith('user')
            ->andWhere([GroupPupil::tableName() . '.active' => GroupPupil::STATUS_ACTIVE])
            ->andWhere(['BETWEEN', GroupPupil::tableName() . '.paid_lessons', 0, 2])
            ->andWhere(['!=', User::tableName() . '.status', User::STATUS_LOCKED])
            ->andWhere([
                'or',
                [GroupPupil::tableName() . '.date_end' => null],
                ['>', GroupPupil::tableName() . '.date_end', $nextWeek->format('Y-m-d')]
            ])
            ->with('group')
            ->all();
        foreach ($groupPupils as $groupPupil) {

            /*----------------------  TEMPLATE ID 3 ---------------------------*/
            $queuedNotification = Notify::find()
                ->andWhere([
                    'user_id' => $groupPupil->user_id,
                    'group_id' => $groupPupil->group_id,
                    'template_id' => [Notify::TEMPLATE_PUPIL_LOW, Notify::TEMPLATE_PUPIL_DEBT],
                ])
                ->andWhere(['!=', 'status', Notify::STATUS_SENT])
                ->one();
            if (!$queuedNotification) {
                /** @var Notify[] $sentNotifications */
                $sentNotifications = Notify::find()
                    ->andWhere([
                        'user_id' => $groupPupil->user_id,
                        'group_id' => $groupPupil->group_id,
                        'template_id' => [Notify::TEMPLATE_PUPIL_LOW, Notify::TEMPLATE_PUPIL_DEBT],
                    ])
                    ->andWhere(['status' => Notify::STATUS_SENT])
                    ->andWhere(['>', 'sent_at', $monthLimit->format('Y-m-d H:i:s')])
                    ->orderBy(['sent_at' => SORT_DESC])
                    ->all();
                $needSent = true;
                if (!empty($sentNotifications)) {
                    $lastNotification = reset($sentNotifications);
                    $needSent = (date_diff(new \DateTime('now'), $lastNotification->sentDate)->days >= pow(2, count($sentNotifications)));
                }

                if ($needSent) {
                    ComponentContainer::getNotifyQueue()->add(
                        $groupPupil->user,
                        Notify::TEMPLATE_PUPIL_LOW,
                        ['paid_lessons' => $groupPupil->paid_lessons],
                        $groupPupil->group
                    );
                }
            }
            /*----------------------  END TEMPLATE ID 3 ---------------------------*/

            /*----------------------  TEMPLATE ID 4 ---------------------------*/
            if ($groupPupil->user->parent_id) {
                $parent = $groupPupil->user->parent;
                /** @var Notify[] $queuedNotificationsDraft */
                $queuedNotificationsDraft = Notify::find()
                    ->andWhere([
                        'user_id' => $parent->id,
                        'group_id' => $groupPupil->group_id,
                        'template_id' => [Notify::TEMPLATE_PARENT_LOW, Notify::TEMPLATE_PARENT_DEBT],
                    ])
                    ->andWhere(['!=', 'status', Notify::STATUS_SENT])
                    ->all();
                $queuedNotifications = [];
                foreach ($queuedNotificationsDraft as $notification) {
                    if ($notification->parameters['child_id'] == $groupPupil->user_id) $queuedNotifications[] = $notification;
                }

                if (empty($queuedNotifications)) {
                    /** @var Notify[] $sentNotificationsDraft */
                    $sentNotificationsDraft = Notify::find()
                        ->andWhere([
                            'user_id' => $parent->id,
                            'group_id' => $groupPupil->group_id,
                            'template_id' => [Notify::TEMPLATE_PARENT_LOW, Notify::TEMPLATE_PARENT_DEBT],
                        ])
                        ->andWhere(['status' => Notify::STATUS_SENT])
                        ->andWhere(['>', 'sent_at', $monthLimit->format('Y-m-d H:i:s')])
                        ->orderBy(['sent_at' => SORT_DESC])
                        ->all();
                    /** @var Notify[] $sentNotifications */
                    $sentNotifications = [];
                    foreach ($sentNotificationsDraft as $notification) {
                        if ($notification->parameters['child_id'] == $groupPupil->user_id) $sentNotifications[] = $notification;
                    }
                    $needSent = true;
                    if (!empty($sentNotifications)) {
                        $lastNotification = reset($sentNotifications);
                        $needSent = (date_diff(new \DateTime('now'), $lastNotification->sentDate)->days >= pow(2, count($sentNotifications)));
                    }

                    if ($needSent) {
                        ComponentContainer::getNotifyQueue()->add(
                            $parent,
                            Notify::TEMPLATE_PARENT_LOW,
                            ['paid_lessons' => $groupPupil->paid_lessons, 'child_id' => $groupPupil->user_id],
                            $groupPupil->group
                        );
                    }
                }
            }
            /*----------------------  END TEMPLATE ID 4 ---------------------------*/
        }

        return yii\console\ExitCode::OK;
    }
}
