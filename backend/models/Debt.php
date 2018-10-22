<?php

namespace backend\models;

use \common\components\extended\ActiveRecord;

/**
 * This is the model class for table "{{%debt}}".
 *
 * @property int $user_id
 * @property int $group_id
 * @property double $amount
 * @property string $comment
 *
 * @property User $user
 * @property Group $group
 */
class Debt extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%debt}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'group_id', 'amount'], 'required'],
            [['user_id', 'group_id'], 'integer'],
            [['amount'], 'number'],
            [['user_id', 'group_id'], 'unique', 'targetAttribute' => ['user_id', 'group_id'], 'message' => 'Debt already exists.'],
            [['user_id'], 'exist', 'targetRelation' => 'user'],
            [['group_id'], 'exist', 'targetRelation' => 'group'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Должник',
            'group_id' => 'группа',
            'amount' => 'Сумма задолженности',
            'comment' => 'Комментарий',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }
}