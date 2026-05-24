<?php

namespace app\services;

class ServiceImportMessageFormatter
{
    /**
     * @param array[] $items
     * @param string[] $notFound
     * @return string[]
     */
    public function format(array $items, array $notFound = [])
    {
        $messages = [];

        if ($notFound !== []) {
            $messages[] = 'Нет в списки у Qiwi: ' . implode(', ', $notFound);
        }

        $created = array_filter($items, function (array $item) {
            return isset($item['status']) && $item['status'] === 'created';
        });

        $exists = array_filter($items, function (array $item) {
            return isset($item['status']) && $item['status'] === 'exists';
        });

        if ($created !== []) {
            $messages[] = 'Созданы сервисы: ' . implode(', ', array_column($created, 'user_name'));
        }

        if ($exists !== []) {
            $messages[] = 'Уже существующие сервисы: ' . implode(', ', array_column($exists, 'user_name'));
        }

        return $messages;
    }
}
