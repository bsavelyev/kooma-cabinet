<?php

namespace app\models\forms;

use app\enum\OperationStatusEnum;
use app\models\ServiceModel;
use app\models\User;
use Yii;
use yii\base\Model;

/**
 * Форма для операций (не AR)
 */
class OperationForm extends Model
{
    public const PROD_UL_SERVICES = [53, 52, 51];
    public const PROD_FL_SERVICES = [57, 56, 55, 54];
    public const TEST_UL_SERVICES = [2797, 2796, 2798];
    public const TEST_FL_SERVICES = [2792, 2793, 2797, 2795];
    public const NULL = null;
    public const TYPE_CASHIN = 0;
    public const TYPE_PAYMENT = 1;
    public const TYPE_CASHOUT = 2;
    public const UTC = 'UTC';
    public const Asia_Almaty = 'Asia/Almaty';

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
            [['from_create_date', 'to_create_date', 'done_date', 'to_status_change_date', 'from_status_change_date', 'amount'], 'safe'],
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

    public static function values(): array
    {
        return [
            self::NULL => '',
            self::TYPE_CASHIN => 'Эмиссия',
            self::TYPE_PAYMENT => 'Оплата',
            self::TYPE_CASHOUT => 'Гашение',
        ];
    }

    public function changeDate($date, $from = 'UTC', $to = 'Asia/Almaty'): string
    {
        $date = new \DateTime($date, new \DateTimeZone($from));
        $date->setTimezone(new \DateTimeZone($to));
        return $date->format('Y-m-d H:i:s.u');
    }
}
