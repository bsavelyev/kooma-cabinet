<?php

namespace app\services;

use app\enum\FeesTypeEnum;
use app\models\FeesModel;
use app\models\providers\Qiwi;
use app\models\providers\QiwiGetUIProvidersRequest;
use app\repositories\FeesRepository;
use app\repositories\ServiceImportRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\db\Expression;
use yii\helpers\Inflector;
use Yii;

class QiwiImportService
{
    /** @var ServiceImportRepository */
    private $repository;

    /** @var ServiceFieldSetupService */
    private $fieldSetupService;

    /** @var FeesRepository */
    private $feesRepository;

    /** @var ServiceImportMessageFormatter */
    private $messageFormatter;

    public function __construct(
        ServiceImportRepository $repository,
        ServiceFieldSetupService $fieldSetupService,
        FeesRepository $feesRepository,
        ServiceImportMessageFormatter $messageFormatter
    ) {
        $this->repository = $repository;
        $this->fieldSetupService = $fieldSetupService;
        $this->feesRepository = $feesRepository;
        $this->messageFormatter = $messageFormatter;
    }

    /**
     * @param int $userId
     * @param string $filePath
     * @return string[]
     */
    public function importFromFile($userId, $filePath)
    {
        try {
            return $this->doImport($userId, $filePath);
        } finally {
            if ($filePath !== '' && file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * @param int $userId
     * @param string $filePath
     * @return string[]
     */
    private function doImport($userId, $filePath)
    {
        $request = new QiwiGetUIProvidersRequest();
        $qiwi = new Qiwi();
        $xmlString = $qiwi->sendRequest($request);
        $providers = $xmlString['response']['providers']['getUIProviders']['provider'];

        $rows = $this->loadSpreadsheetRows($filePath);
        $result = [];
        $notFound = [];

        foreach ($rows as $row) {
            $matched = false;

            foreach ($providers as $provider) {
                $providerAttrs = (array) $provider['@attributes'];
                $sName = (string) $providerAttrs['sName'];

                if (stripos($sName, $row['A']) === false) {
                    continue;
                }

                $matched = true;
                $userName = isset($providerAttrs['tag']) ? (string) $providerAttrs['tag'] : '';
                $transliterated = Inflector::transliterate($sName);
                $alreadyExists = $this->repository->serviceExists($transliterated);
                $status = $alreadyExists ? 'exists' : 'created';

                Yii::$app->db->transaction(function () use (
                    $userId,
                    $transliterated,
                    $userName,
                    $sName,
                    $row,
                    $status,
                    &$result
                ) {
                    $date = $this->repository->getDbTimestamp();

                    $service = $this->repository->createService($transliterated, $userName, $date);
                    $serviceProvider = $this->repository->createProvider(
                        $service->id,
                        $userId,
                        $date,
                        $sName
                    );

                    $this->fieldSetupService->setupQiwiFields($serviceProvider);
                    $this->createFee($service->id, $serviceProvider->id, $row['C']);

                    $result[] = [
                        'id' => $service->id,
                        'system_name' => $transliterated,
                        'user_name' => $userName,
                        'status' => $status,
                    ];
                });
            }

            if (!$matched) {
                $notFound[] = $row['A'];
            }
        }

        return $this->messageFormatter->format($result, $notFound);
    }

    /**
     * @param string $filePath
     * @return array[]
     */
    private function loadSpreadsheetRows($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        foreach ($rows as $rowNumber => $row) {
            if (empty($row['A'])) {
                unset($rows[$rowNumber]);
            }
        }

        return $rows;
    }

    /**
     * @param int $serviceId
     * @param int $providerId
     * @param mixed $feeValue
     */
    private function createFee($serviceId, $providerId, $feeValue)
    {
        $now = (new \DateTime())->modify('-1 day')->format('Y-m-d H:i:s.u');
        $nowPlus4Years = (new \DateTime())->modify('+4 years')->format('Y-m-d H:i:s.u');

        $feeModel = new FeesModel();
        $feeModel->service_id = $serviceId;
        $feeModel->provider_id = $providerId;
        $feeModel->value = $feeValue;
        $feeModel->type_id = null;
        $feeModel->value_type = FeesTypeEnum::COMMISSION_TYPE_UPPER_PART;
        $feeModel->start_date = new Expression(
            "TO_TIMESTAMP(:string, 'YYYY-MM-DD HH24:MI:SS.US')",
            [':string' => $now]
        );
        $feeModel->end_date = new Expression(
            "TO_TIMESTAMP(:string, 'YYYY-MM-DD HH24:MI:SS.US')",
            [':string' => $nowPlus4Years]
        );
        $feeModel->min_value = 0;
        $feeModel->max_value = 999999;
        $feeModel->agent_id = Yii::$app->params['payment_service']['qiwi']['koomaID'];

        try {
            $this->feesRepository->createFee($feeModel);
        } catch (\Exception $e) {
            // ошибка комиссии не прерывает импорт
        }
    }
}
