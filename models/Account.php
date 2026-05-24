<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "kooma.account".
 *
 * @property int $id
 * @property int $subject_id
 * @property float $amount
 * @property int $status
 */
class Account extends ActiveRecord
{
    public const STATUS_BLOCKED = 1;

    public const STATUS_ACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'kooma.account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subject_id'], 'required'],
            [['subject_id', 'status'], 'default', 'value' => null],
            [['subject_id', 'status'], 'integer'],
            [['amount'], 'number'],
            [['status'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_BLOCKED, self::STATUS_ACTIVE]],
            [['subject_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['subject_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'subject_id' => 'Subject ID',
            'amount' => 'Amount',
            'status' => 'Status',
        ];
    }

    public function getSubject()
    {
        return $this->hasOne(User::class, ['id' => 'subject_id']);
    }
}
