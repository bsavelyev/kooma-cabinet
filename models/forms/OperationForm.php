<?php

namespace app\models\forms;

use app\models\OperationModel;
use yii\base\Model;

/**
 * Форма фильтрации операций (не AR).
 */
class OperationForm extends Model
{
    public $id;
    public $sender_id;
    public $receiver_id;
    public $status;
    public $from_create_date;
    public $to_create_date;
    public $from_status_change_date;
    public $to_status_change_date;
    public $service_id;
    public $amount;
    public $billing_id;
    public $payment_account;
    public $type_id;
    public $descr;
    public $ext_id;
    public $sender_account_type;

    public function rules()
    {
        return [
            [['sender_id', 'receiver_id'], 'required'],
            [['sender_id', 'receiver_id', 'status', 'service_id', 'type_id', 'sender_account_type'], 'default', 'value' => null],
            [['service_id'], 'default', 'value' => ['']],
            [['id', 'sender_id', 'receiver_id', 'status', 'type_id', 'sender_account_type'], 'integer'],
            [['from_create_date', 'to_create_date', 'to_status_change_date', 'from_status_change_date', 'amount'], 'safe'],
            [['ext_id', 'billing_id', 'descr', 'payment_account'], 'string'],
            ['sender_account_type', 'safe'],
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

    /**
     * @return array
     */
    public static function typeLabels()
    {
        return OperationModel::values();
    }
}
