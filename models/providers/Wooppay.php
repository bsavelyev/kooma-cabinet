<?php

namespace app\models\providers;

use yii\httpclient\Client;
use yii\web\ServerErrorHttpException;

class Wooppay
{
    private const SESSION_CACHE = 'wooppay';

    /**
     * @return mixed[][]
     */
    public static function getServices($service_ids): array
    {
        $client = new Client();
        $session = self::getSession();
        $services = [];
        $notFound = [];

        foreach ($service_ids as $service_id) {
            $response = $client->get(
                \Yii::$app->params['wooppayUrl'].'service/'.trim($service_id).'?expand=fields.validations',
                null,
                ['content-type' => 'application/json', 'authorization' => $session]
            )->send();

            if ($response->isOk && is_array($response->data)) {
                $services[$service_id] = $response->data;
            } else {
                if (401 === $response->getData()['status']) {
                    $cache = \Yii::$app->cache;
                    $cache->delete(self::SESSION_CACHE);
                }

                $notFound[] = $service_id;
                // Можно логировать или просто продолжить
                \Yii::warning(sprintf('Сервис с ID %s не найден или ошибка ответа', $service_id), __METHOD__);

                continue;
            }
        }

        if ($notFound !== []) {
            \Yii::$app->session->addFlash('warning', 'Не удалось получить сервисы: '.implode(', ', $notFound));
        }

        return $services;
    }

    private static function getSession()
    {
        $client = new Client();
        $cache = \Yii::$app->cache;
        if ($cache->exists(self::SESSION_CACHE)) {
            return $cache->get(self::SESSION_CACHE);
        }

        if (YII_ENV_PROD) {
            $request = $client->post(
                \Yii::$app->params['wooppayUrl'].'auth/pseudo',
                ['login' => \Yii::$app->params['username'], 'subject_type' => '5019',
                    ['content-type' => 'application/json']]
            );
        } else {
            $request = $client->post(
                \Yii::$app->params['wooppayUrl'].'auth/mobile',
                ['login' => '77761235646', 'password' => 'XXXXXXXXXXX'],
                ['content-type' => 'application/json', 'X-Application-Key' => 'XXXXXXXXXXXXX']
            );
        }

        $response = $client->send($request);
        if ($response->isOk) {
            $session = $response->data['token'];
            $cache->set(self::SESSION_CACHE, $session, 3500);

            return $session;
        }

        throw new ServerErrorHttpException('Failed to get Wooppay session.');
    }
}
