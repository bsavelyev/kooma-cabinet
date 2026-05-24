<?php

namespace app\helpers;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\base\Component;
use yii\helpers\Inflector;

class ExcelParser extends Component
{
    /**
     * Парсинг Excel-файла и возврат массива с транслитерированными ключами.
     *
     *
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws Exception
     */
    public function parseExcel(string $filePath): array
    {
        $result = [];

        // Загружаем Excel-файл
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Пропускаем заголовок и обрабатываем строки
        foreach ($rows as $rowNumber => $row) {
            if (1 === $rowNumber) {
                continue; // Пропускаем заголовок
            }

            // Получаем первый (A) и третий (C) столбцы
            $name = trim($row['A'] ?? '');
            $value = trim($row['C'] ?? '');
            // Пропускаем пустые строки
            if ($name === '' || $name === '0') {
                continue;
            }
            if ($value === '' || $value === '0') {
                continue;
            }

            // Транслитерируем название с помощью Inflector
            $translitKey = Inflector::transliterate($name);
            // Заменяем неалфавитные символы на подчеркивание и убираем лишние
            $translitKey = preg_replace('/[^a-zA-Z0-9]/', '_', $translitKey);
            $translitKey = preg_replace('/_+/', '_', $translitKey);
            $translitKey = trim($translitKey, '_');

            // Сохраняем в результирующий массив
            $result[$translitKey] = $value;
        }

        return $result;
    }
}
