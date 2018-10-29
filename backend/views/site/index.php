<?php

use \yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $admin \yii\web\User */

$this->title = 'Панель управления';
?>
<div class="row">
    <?php if ($admin->can('cashier')): ?>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <a class="btn btn-default btn-lg full-width" href="<?= Url::to('money/income'); ?>">
                <span class="fas fa-hand-holding-usd fa-3x"></span><hr>
                Принять оплату
            </a>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <a class="btn btn-default btn-lg full-width" href="<?= Url::to('contract/create'); ?>">
                <span class="fas fa-file-invoice-dollar fa-3x"></span><hr>
                Выдать договор
            </a>
        </div>
    <?php endif; ?>
    <?php if ($admin->can('scheduler')): ?>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <a class="btn btn-default btn-lg full-width" href="<?= Url::to('event/index'); ?>">
                <span class="far fa-calendar-alt fa-3x"></span><hr>
                Расписание
            </a>
        </div>
    <?php endif; ?>
</div>

<hr>

<div class="row">
    <div class="col-xs-12 col-md-9">
        <?php if ($admin->can('viewGroups') || $admin->can('content')): ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <?php if ($admin->can('viewGroups')): ?>
                            <div class="col-xs-12 col-sm-4 col-md-3">
                                <a class="btn btn-default btn-lg full-width" href="<?= Url::to('group/index'); ?>">
                                    <span class="fas fa-users fa-2x"></span><br>
                                    Группы
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if ($admin->can('manageTeachers')): ?>
                            <div class="col-xs-12 col-sm-4 col-md-3">
                                <a class="btn btn-default btn-lg full-width" href="<?= Url::to('teacher/index'); ?>">
                                    <span class="fas fa-user-tie fa-2x"></span><br>
                                    Учителя
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if ($admin->can('manageSubjectCategories')): ?>
                            <div class="col-xs-12 col-sm-4 col-md-3">
                                <a class="btn btn-default btn-lg full-width" href="<?= Url::to('subject-category/index'); ?>">
                                    <span class="fas fa-briefcase fa-2x"></span><br>
                                    Направления
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if ($admin->can('manageSubjects')): ?>
                            <div class="col-xs-12 col-sm-4 col-md-3">
                                <a class="btn btn-default btn-lg full-width" href="<?= Url::to('subject/index'); ?>">
                                    <span class="fas fa-chalkboard-teacher fa-2x"></span><br>
                                    Курсы
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <hr>
        <?php endif; ?>

        <?php if ($admin->can('support')):
            /* @var $orderCount int */
            /* @var $feedbackCount int */
            /* @var $reviewCount int */
            ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 col-md-3">
                            <a class="btn btn-default btn-lg full-width <?= $orderCount > 0 ? 'btn-warning' : ''; ?>" href="<?= Url::to('order/index'); ?>">
                                <span class="fas fa-book fa-2x"></span><br>
                                Заявки <?php if ($orderCount > 0): ?>(<?= $orderCount; ?>)<?php endif; ?>
                            </a>
                        </div>
                        <div class="col-xs-12 col-sm-4 col-md-3">
                            <a class="btn btn-default btn-lg full-width <?= $feedbackCount > 0 ? 'btn-warning' : ''; ?>" href="<?= Url::to('feedback/index'); ?>">
                                <span class="fas fa-book fa-2x"></span><br>
                                Обратная связь <?php if ($feedbackCount > 0): ?>(<?= $feedbackCount; ?>)<?php endif; ?>
                            </a>
                        </div>
                        <div class="col-xs-12 col-sm-4 col-md-3">
                            <a class="btn btn-default btn-lg full-width <?= $reviewCount > 0 ? 'btn-warning' : ''; ?>" href="<?= Url::to('review/index'); ?>">
                                <span class="fas fa-book fa-2x"></span><br>
                                Отзывы <?php if ($reviewCount > 0): ?>(<?= $reviewCount; ?>)<?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
        <?php endif; ?>
    </div>
    <div class="col-xs-12 col-md-3">
        <div class="panel panel-default">
            <ul class="list-group">
                <?php if ($admin->can('manageUsers')): ?>
                    <a class="list-group-item" href="<?= Url::to(['user/index']); ?>"><span class="fas fa-user"></span> Пользователи</a>
                <?php endif; ?>

                <?php if ($admin->can('cashier')): ?>
                    <a class="list-group-item" href="<?= Url::to(['money/payment']); ?>"><span class="fas fa-dollar-sign"></span> Платежи</a>
                    <a class="list-group-item" href="<?= Url::to(['money/debt']); ?>"><span class="fas fa-dollar-sign"></span> Долги</a>
                    <a class="list-group-item" href="<?= Url::to(['money/actions']); ?>"><span class="fas fa-clipboard-list"></span> Действия</a>
                <?php endif; ?>

                <?php if ($admin->can('accountant')): ?>
                    <a class="list-group-item" href="<?= Url::to(['money/salary']); ?>"><span class="fas fa-money-bill-wave"></span> Зарплата</a>
                <?php endif; ?>

                <?php if ($admin->can('content')): ?>
                    <a class="list-group-item" href="<?= Url::to(['page/index']); ?>"><span class="fas fa-file"></span> Страницы</a>
                    <a class="list-group-item" href="<?= Url::to(['menu/index']); ?>"><span class="fas fa-bars"></span> Меню</a>
                    <a class="list-group-item" href="<?= Url::to(['widget-html/index']); ?>"><span class="fas fa-cog"></span> Блоки</a>
                    <li class="list-group-item"></li>
                    <a class="list-group-item" href="<?= Url::to(['high-school/index']); ?>"><span class="fas fa-graduation-cap"></span> ВУЗы</a>
                    <a class="list-group-item" href="<?= Url::to(['lyceum/index']); ?>"><span class="fas fa-landmark"></span> Лицеи</a>
                    <li class="list-group-item"></li>
                    <a class="list-group-item" href="<?= Url::to(['quiz/index']); ?>"><span class="fas fa-clipboard"></span> Тесты</a>
                    <a class="list-group-item" href="<?= Url::to(['quiz-result/index']); ?>"><span class="fas fa-clipboard-list"></span> Результаты тестов</a>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

    <?php
/*        <a href="<?= \yii\helpers\Url::to(['user/schedule']); ?>" class="btn btn-default btn-lg col-xs-12 col-sm-4 col-md-3 col-lg-2"><span class="glyphicon glyphicon-list-alt"></span> Дневники</a>*/
?>