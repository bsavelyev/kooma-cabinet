<?php

namespace app\models;

use app\enum\OperationStatusEnum;
use DateTime;
use DateTimeZone;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "kooma.operation".
 *
 * @property string $id
 * @property int $sender_id
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

    public static function values(): array
    {
        return [
            self::NULL => '',
            self::TYPE_CASHIN => 'Эмиссия',
            self::TYPE_PAYMENT => 'Оплата',
            self::TYPE_CASHOUT => 'Гашение',
        ];
    }

    public function changeStatus($id)
    {
        $result = Yii::$app->db->createCommand("SELECT * FROM operations.change_status(:id, :status)", [
           ':id' => $id,
           ':status' => OperationStatusEnum::STATUS_NEW,
        ])->queryScalar();
        return $result > 0 ? true : $result;
    }

    public static function createOperation($sender, $recipient, $service_id, $amount, $descr, $payment_account, $type_id, $account_type): int
    {
        return Yii::$app->db->createCommand("SELECT * FROM operations.create(:sender, :recipient, :service_id, :amount, :ext_id, :descr, :payment_account, :type_id, :account_type )", [
            ':sender' => $sender,
            ':recipient' => $recipient,
            ':service_id' => $service_id,
            ':amount' => $amount,
            ':ext_id' => null,
            ':descr' => $descr,
            ':payment_account' => $payment_account,
            ':type_id' => $type_id,
            ':account_type' => $account_type,
        ])->queryScalar();
    }

    public function search(array $params): Query
    {
        $query = self::find()->alias('o')
            ->joinWith(['sender sn', 'receiver rc', 'service s'], false);

        $query->andFilterWhere([
            'o.id' => $params['id'],
            'o.sender_id' => $params['sender_id'] == '0' ? '' : $params['sender_id'],
            'o.receiver_id' => $params['receiver_id'] == '0' ? '' : $params['receiver_id'],
            'o.status' => $params['status'],
            'o.amount' => $params['amount'],
            'o.billing_id' => $params['billing_id'],
            'o.payment_account' => $params['payment_account'],
            'o.type_id' => $params['type_id'],
            'o.ext_id' => $params['ext_id']
        ]);

        $ul_service = strpos($_SERVER['SERVER_NAME'], 'cabinet.koomapay.kz') !== false ? self::PROD_UL_SERVICES : self::TEST_UL_SERVICES;
        $fl_service = strpos($_SERVER['SERVER_NAME'], 'cabinet.koomapay.kz') !== false ? self::PROD_FL_SERVICES : self::TEST_FL_SERVICES;

        $service_id = $params["service_id"][0] ?? 0;

        if ($service_id == 'UL') {
            $query->andFilterWhere(['in', 'o.service_id', $ul_service]);
        } elseif ($service_id == 'FL') {
            $query->andFilterWhere(['in', 'o.service_id', $fl_service]);
        } else {
            $query->andFilterWhere(['o.service_id' => $service_id == '0' ? '' : $params['service_id'][0]]);
        }

        if (!empty($params['from_create_date'])) {
            $from_create_date = self::changeDate($params['from_create_date'], self::Asia_Almaty, self::UTC);
            $to_create_date = self::changeDate($params['to_create_date'], self::Asia_Almaty, self::UTC);
        }
        
        if (!empty($params['from_status_change_date'])) {
            $from_status_change_date = self::changeDate($params['from_status_change_date'], self::Asia_Almaty, self::UTC);
            $to_status_change_date = self::changeDate($params['to_status_change_date'], self::Asia_Almaty, self::UTC);
        }
        
        $query->andFilterWhere(['between', 'o.create_date', $from_create_date ?? '', $to_create_date ?? '']);
        $query->andFilterWhere(['between', 'o.status_change_date', $from_status_change_date ?? '', $to_status_change_date ?? '']);
        
        $query->select([
            'o.id',
            'sender_login' => 'sn.username',
            'receiver_login' => 'rc.username',
            'o.amount',
            'o.create_date',
            'o.status',
            'o.create_date',
            'o.sender_id',
            'o.receiver_id',
            'o.done_date',
            'o.descr',
            'o.billing_id',
            'o.service_id',
            'o.ext_id',
            'o.payment_account',
            'o.type_id',
            'o.sender_account_type',
            'o.upper_fee_amount',
            'o.status_change_date'
        ]);
        return $query;
    }

    public static function changeDate($date, $from = 'UTC', $to = 'Asia/Almaty'): string
    {
        $date = new DateTime($date, new DateTimeZone($from));
        $date->setTimezone(new DateTimeZone($to));
        return $date->format('Y-m-d H:i:s.u');
    }

    public function afterFind()
    {
        parent::afterFind();

        if (isset($this->status_change_date)) {
            $this->status_change_date = self::changeDate($this->status_change_date, self::UTC, self::Asia_Almaty);
        }

        if (isset($this->create_date)) {
            $this->create_date = self::changeDate($this->create_date, self::UTC, self::Asia_Almaty);
        }

        if (isset($this->done_date)) {
            $this->done_date = self::changeDate($this->done_date, self::UTC, self::Asia_Almaty);
        }

        return $this;
    }

    public function getOperationStatusHistory()
    {
        return $this->hasOne(OperationStatusHistoryModel::class, ['operation_id' => 'id']);
    }

    /**
     * Получить ActiveQuery для операций
     */
    public static function getQuery(): \yii\db\ActiveQuery
    {
        return self::find();
    }
}
