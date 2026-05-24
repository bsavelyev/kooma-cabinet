<?php

namespace app\models;

use app\enum\SubjectEnum;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property string $username
 * @property string $password_hash
 * @property integer $status
 * @property integer $create_date
 * @property integer $update_date
 *
 * @property string $password write-only password
 * @property Account $account
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const SCENARIO_UPDATE = 'update';

    public static function tableName()
    {
        return 'kooma.subject';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password_hash'], 'required'],
            [['username', 'password_hash'], 'string', 'skipOnEmpty' => false],
            [['id', 'create_date', 'update_date'], 'safe'],
//            [['username'], 'unique'],
            [['status'], 'integer'],
            ['status', 'default', 'value' => SubjectEnum::STATUS_ACTIVE],
            ['status', 'in', 'range' => [SubjectEnum::STATUS_BLOCKED, SubjectEnum::STATUS_ACTIVE]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Имя пользователя',
            'password_hash' => 'Зашифрованный пароль',
            'create_date' => 'Create Date',
            'update_date' => 'Update Date',
            'status' => 'Статус',
        ];
    }

    public function scenarios()
    {
        $scenarios =  parent::scenarios();
        return array_merge($scenarios, [
            self::SCENARIO_UPDATE => ['id', 'username', 'create_date', 'update_date', 'status']]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => SubjectEnum::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    public function getPasswordHash(): string
    {
        return $this->password_hash;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $date = new Expression('NOW()');
            $date = (new \yii\db\Query())->select($date)->scalar();
            if ($this->scenario !== self::SCENARIO_UPDATE) {
                $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($this->password_hash);
            }
            $this->create_date = $this->create_date ? $this->create_date : $date;
            $this->update_date = $date;
            return true;
        }

        return false;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getAccount()
    {
        return $this->hasOne(Account::class, ['subject_id' => 'id']);
    }

    public function getServiceProvider()
    {
        return $this->hasOne(ServiceProviderModel::class, ['provider_id' => 'id']);
    }

    public function getServiceProviderFields()
    {
        return $this->hasOne(ServiceProviderFieldsModel::class, ['service_provider_id' => 'id']);
    }

    public function getFees()
    {
        return $this->hasOne(FeesModel::class, ['provider_id' => 'id']);
    }

    public function getAssignment()
    {
        return $this->hasOne(AssignmentModel::class, ['user_id' => 'id']);
    }
}
