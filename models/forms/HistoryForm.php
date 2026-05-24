<?php

namespace app\models\forms;

use common\components\tps_generated\enums\OperationStatus;
use frontend\models\operations\enums\AbstractOperationType;
use frontend\models\operations\enums\ReportEnum;
use frontend\models\operations\helpers\OperationHelper;
use frontend\models\service\ServiceHandler;
use yii\base\Model;

/**
 * Class OperationForm
 * @package frontend\models\history
 */
class HistoryForm extends Model
{
    /** @var string */
    public $operationId;


    public const SCENARIO_SEARCH_PAYMENT = 'search_payment';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_SEARCH_PAYMENT] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }
}
