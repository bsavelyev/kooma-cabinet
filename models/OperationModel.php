<?php

namespace app\models;

use app\helpers\DateTimeHelper;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "kooma.operation".
 *
 * @property string $id
 * @property int|string $sender_id
 * @property int $receiver_id
 * @property int $status
 * @property string $from_create_date
 * @property string $to_create_date
 * @property string|null $done_date
 * @property string $from_status_change_date
 * @property string $to_status_change_date
 * @property float $amount
 * @property float|null $fee_amount
 * @property int $service_id
 * @property string|null $ext_id
 * @property string|null $billing_id
 * @property string|null $descr
 * @property string|null $payment_account
 * @property int $type_id
 * @property int $sender_account_type
 */
class OperationModel extends ActiveRecord
{
    public const NULL_VALUE = null;
    public const TYPE_CASHIN = 0;
    public const TYPE_PAYMENT = 1;
    public const TYPE_CASHOUT = 2;

    /** @deprecated use DateTimeHelper::TIMEZONE_UTC */
    public const UTC = DateTimeHelper::TIMEZONE_UTC;

    /** @deprecated use DateTimeHelper::TIMEZONE_ALMATY */
    public const Asia_Almaty = DateTimeHelper::TIMEZONE_ALMATY;

    public static function tableName()
    {
        return 'kooma.operation';
    }

    public function rules()
    {
        return [
            [['sender_id', 'receiver_id'], 'required'],
            [['sender_id', 'receiver_id', 'status', 'service_id', 'type_id', 'sender_account_type'], 'default', 'value' => null],
            [['service_id'], 'default', 'value' => ['']],
            [['sender_id', 'receiver_id', 'status', 'type_id', 'sender_account_type'], 'integer'],
            [['from_create_date', 'to_create_date', 'done_date', 'to_status_change_date', 'from_status_change_date', 'amount'], 'safe'],
            [['ext_id', 'billing_id', 'descr', 'payment_account'], 'string'],
            ['sender_account_type', 'safe'],
            [['billing_id', 'receiver_id'], 'unique', 'targetAttribute' => ['billing_id' => 'receiver_id']],
            [['ext_id', 'sender_id'], 'unique', 'targetAttribute' => ['ext_id' => 'sender_id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => ServiceModel::class, 'targetAttribute' => ['service_id' => 'id']],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['sender_id' => 'id']],
            [['receiver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['receiver_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sender_id' => 'Отправитель',
            'receiver_id' => 'Получатель',
            'status' => 'Статус',
            'create_date' => 'Дата создания',
            'done_date' => 'Дата проведения',
            'status_change_date' => 'Дата изменения статуса',
            'amount' => 'сумма',
            'fee_amount' => 'сумма комиссии',
            'service_id' => 'Сервис',
            'ext_id' => 'Внешний ID',
            'billing_id' => 'Биллинг ID',
            'descr' => 'описание',
            'payment_account' => 'Счет получателя',
            'type_id' => 'Тип Операции',
            'sender_account_type' => 'Тип счета отправителя',
        ];
    }

    public function getSender()
    {
        return $this->hasOne(User::class, ['id' => 'sender_id']);
    }

    public function getReceiver()
    {
        return $this->hasOne(User::class, ['id' => 'receiver_id']);
    }

    public function getService()
    {
        return $this->hasOne(ServiceModel::class, ['id' => 'service_id']);
    }

    public function getOperationStatusHistory()
    {
        return $this->hasOne(OperationStatusHistoryModel::class, ['operation_id' => 'id']);
    }

    /**
     * @return array
     */
    public static function values()
    {
        return [
            self::NULL_VALUE => '',
            self::TYPE_CASHIN => 'Эмиссия',
            self::TYPE_PAYMENT => 'Оплата',
            self::TYPE_CASHOUT => 'Гашение',
        ];
    }

    public function afterFind()
    {
        parent::afterFind();

        if (isset($this->status_change_date)) {
            $this->status_change_date = DateTimeHelper::convert(
                $this->status_change_date,
                DateTimeHelper::TIMEZONE_UTC,
                DateTimeHelper::TIMEZONE_ALMATY
            );
        }

        if (isset($this->create_date)) {
            $this->create_date = DateTimeHelper::convert(
                $this->create_date,
                DateTimeHelper::TIMEZONE_UTC,
                DateTimeHelper::TIMEZONE_ALMATY
            );
        }

        if (isset($this->done_date)) {
            $this->done_date = DateTimeHelper::convert(
                $this->done_date,
                DateTimeHelper::TIMEZONE_UTC,
                DateTimeHelper::TIMEZONE_ALMATY
            );
        }

        return $this;
    }
}
