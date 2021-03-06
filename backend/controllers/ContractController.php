<?php

namespace backend\controllers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use common\components\ComponentContainer;
use common\models\Company;
use common\models\Contract;
use common\models\ContractSearch;
use common\models\Group;
use common\models\GroupParam;
use common\models\GroupPupil;
use common\models\User;
use common\components\Action;
use yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

/**
 * ContractController implements contracts management.
 */
class ContractController extends AdminController
{
    protected $accessRule = 'contractManagement';

     /**
     * Print Contract.
     * @param int $id
     * @return mixed
     */
    public function actionPrint($id)
    {
        $contract = $this->findModel($id);
        $pupilType = 'individual';
        if ($contract->user->parent_id && $contract->user->parent->role == User::ROLE_COMPANY) {
            $pupilType = 'company';
        }

        $this->layout = 'print';
        return $this->render("/contract/print/$pupilType", ['contract' => $contract]);
    }

    /**
     * @param $id
     * @return yii\web\Response
     */
    public function actionBarcode($id)
    {
        $contract = $this->findModel($id);
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        return \Yii::$app->response->sendContentAsFile(
            $generator->getBarcode($contract->number, $generator::TYPE_CODE_128),
            "barcode.svg",
            ['mimeType' => 'image/svg+xml', 'inline' => true]
        );
    }

    public function actionQr()
    {
        $link = 'https://t.me/fiveplus_public_bot?start=account';
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'addQuietzone' => false,
        ]);
        $generator = new QRCode($options);

        return \Yii::$app->response->sendContentAsFile(
            $generator->render($link),
            "qrcode.svg",
            ['mimeType' => 'image/svg+xml', 'inline' => true]
        );
    }

    /**
     * Monitor all Contracts.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ContractSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        /** @var User[] $students */
        $students = User::find()->where(['role' => User::ROLE_PUPIL])->orderBy(['name' => SORT_ASC])->all();
        $studentMap = [null => 'Все'];
        foreach ($students as $student) $studentMap[$student->id] = $student->name;

        /** @var Group[] $groups */
        $groups = Group::find()->orderBy(['active' => SORT_DESC, 'name' => SORT_ASC])->all();
        $groupMap = [null => 'Все'];
        foreach ($groups as $group) $groupMap[$group->id] = $group->name;

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'studentMap' => $studentMap,
            'groupMap' => $groupMap,
        ]);
    }

    /**
     * @return yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionFind()
    {
        if (!Yii::$app->request->isAjax) throw new BadRequestHttpException('Request is not AJAX');

        $jsonData = self::getJsonOkResult();
        $contractNum = preg_replace('#\D#', '', Yii::$app->request->post('number', ''));

        if (empty($contractNum)) $jsonData = self::getJsonErrorResult('Неверный номер договора');
        else {
            $contract = Contract::findOne(['number' => $contractNum]);
            if (!$contract) $jsonData = self::getJsonErrorResult('Договор не найден');
            elseif ($contract->status != Contract::STATUS_NEW) $jsonData = self::getJsonErrorResult('Договор уже оплачен!');
            else {
                $jsonData['id'] = $contract->id;
                $jsonData['user_name'] = $contract->user->name;
                $jsonData['group_name'] = $contract->group->name;
                $jsonData['amount'] = $contract->amount;
                $jsonData['discount'] = $contract->discount;
                $jsonData['create_date'] = $contract->createDate->format('d.m.Y');
                $jsonData['company_name'] = $contract->company->second_name;
                /** @var GroupPupil $groupPupil */
                $groupPupil = GroupPupil::find()->andWhere(['user_id' => $contract->user_id, 'group_id' => $contract->group_id, 'active' => GroupPupil::STATUS_ACTIVE])->one();
                if (!$groupPupil) $jsonData['group_pupil_id'] = 0;
                else {
                    $jsonData['group_pupil_id'] = $groupPupil->id;
                    $jsonData['date_start'] = $groupPupil->startDateObject->format('d.m.Y');
                    $jsonData['date_charge_till'] = $groupPupil->chargeDateObject ? $groupPupil->chargeDateObject->format('d.m.Y') : '';
                }
            }
        }

        return $this->asJson($jsonData);
    }

    /**
     * Create new contract
     * @return mixed
     */
    public function actionCreate()
    {
        if (\Yii::$app->request->isPost) {
            $userId = Yii::$app->request->post('user_id');
            $groupId = Yii::$app->request->post('group_id');
            $companyId = Yii::$app->request->post('company_id');
            $amount = Yii::$app->request->post('amount');
            $discount = boolval(Yii::$app->request->post('discount', 0));

            if (!$userId) \Yii::$app->session->addFlash('error', 'No user');
            elseif (!$groupId) \Yii::$app->session->addFlash('error', 'No group');
            elseif ($amount <= 0) \Yii::$app->session->addFlash('error', 'Wrong amount');
            else {
                $user = User::findOne($userId);
                $group = Group::findOne($groupId);
                $company = Company::findOne($companyId);
                if (!$user || $user->role != User::ROLE_PUPIL) \Yii::$app->session->addFlash('error', 'Wrong pupil');
                elseif (!$group || $group->active != Group::STATUS_ACTIVE) \Yii::$app->session->addFlash('error', 'Wrong group');
                elseif (!$company) \Yii::$app->session->addFlash('error', 'Не выбран учебный центр');
                else {
                    $contract = new Contract();
                    $contract->created_admin_id = Yii::$app->user->id;
                    $contract->user_id = $user->id;
                    $contract->group_id = $group->id;
                    $contract->company_id = $company->id;
                    $contract->amount = $amount;
                    $contract->discount = $discount ? Contract::STATUS_ACTIVE : Contract::STATUS_INACTIVE;
                    $contract->created_at = date('Y-m-d H:i:s');

                    $groupParam = GroupParam::findByDate($group, new \DateTime());

                    if ($contract->discount == Contract::STATUS_ACTIVE
                        && (($groupParam && $amount < $groupParam->price3Month) || (!$groupParam && $amount < $group->price3Month))) {
                        \Yii::$app->session->addFlash('error', 'Wrong payment amount');
                    } else {
                        if (!$contract->save()) \Yii::$app->session->addFlash('error', 'Не удалось создать договор: ' . $contract->getErrorsAsString());
                        else {
                            Yii::$app->session->addFlash(
                                'success',
                                'Договор ' . $contract->number . ' зарегистрирован '
                                . '<a target="_blank" href="' . yii\helpers\Url::to(['contract/print', 'id' => $contract->id]) . '">Распечатать</a>'
                            );
                            ComponentContainer::getActionLogger()->log(
                                Action::TYPE_CONTRACT_ADDED,
                                $user,
                                $contract->amount,
                                $group
                            );
                        }
                    }
                }
            }
        }

        $params = [
            'companies' => Company::find()->orderBy(['second_name' => SORT_ASC])->all(),
        ];
        $userId = Yii::$app->request->get('user');
        if ($userId) {
            $user = User::findOne($userId);
            if ($user) $params['user'] = $user;
        }
        return $this->render('create', $params);
    }

    /**
     * Finds the Contract model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Contract the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Contract::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
