<?php

namespace app\services;

use app\helpers\OperationServiceConfig;
use DateTime;
use DateTimeZone;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class ExportService
{
    public const CSV = 'csv';

    public const XLSX = 'xlsx';

    /**
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public static function actionExport($format, ActiveDataProvider $dataProvider): bool
    {
        ini_set('memory_limit', '512M');
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        foreach ($dataProvider->getModels() as $data) {

            $date = new DateTime($data->status_change_date);
            $date->setTimezone(new DateTimeZone('Asia/Almaty'));
            $formattedDate = $date->format('d.m.Y H:i');

            $type = OperationServiceConfig::isUlServiceId($data->service_id) ? 'UL' : 'FL';

            $sheet->setCellValue('A' . $row, $data->ext_id);
            $sheet->setCellValue('B' . $row, $formattedDate);
            $sheet->setCellValue('C' . $row, $data->payment_account);
            $sheet->setCellValue('D' . $row, $data->amount);
            $sheet->setCellValue('E' . $row, 0);
            $sheet->setCellValue('F' . $row, $type);
            $row++;
        }

        if ($format === self::XLSX) {
            $writer = new Xlsx($spreadsheet);
            $fileName = 'export.xlsx';
            $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif ($format === self::CSV) {
            $writer = new Csv($spreadsheet);
            $fileName = 'export.csv';
            $mimeType = 'text/csv';
            $writer->setDelimiter(';');
            $writer->setEnclosure('');
        } else {
            throw new BadRequestHttpException('Unsupported format');
        }

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->setDownloadHeaders($fileName, $mimeType);

        // Запись файла в поток
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $response->content = $content;

        return true;
    }
}
