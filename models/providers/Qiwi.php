<?php

namespace app\models\providers;

use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;

class Qiwi
{
    private Client $client;

    private QiwiXmlBuilder $builder;

    public function __construct()
    {
        $params = \Yii::$app->params['payment_service']['qiwi'];

        $this->client = new Client([
            'baseUrl' => $params['baseUrl'],
            'requestConfig' => ['format' => Client::FORMAT_RAW_URLENCODED],
            'responseConfig' => ['format' => Client::FORMAT_XML],
        ]);

        $this->builder = new QiwiXmlBuilder([
            'terminal' => $params['terminal'],
            'login' => $params['login'],
            'sign' => $params['sign'],
        ]);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getUIProviders(): array
    {
        $model = new QiwiGetUIProvidersRequest();

        return $this->sendRequest($model);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function sendRequest($model): array
    {
        if (!$model->validate()) {
            return [
                'success' => false,
                'errors' => $model->getErrors(),
            ];
        }

        $bodyXml = $model->build();
        $finalXml = $this->builder->wrap($bodyXml);

        $response = $this->client->createRequest()
            ->setMethod('POST')
            ->setHeaders([
                'Content-Type' => 'text/xml; charset=windows-1251',
                'User-Agent' => 'Dealer v0',
            ])
            ->setContent($finalXml)
            ->send()
        ;

        if ($response->isOk) {
            return [
                'success' => true,
                'response' => $response->data,
            ];
        }

        return [
            'success' => false,
            'status' => $response->statusCode,
            'error' => $response->getContent(),
        ];
    }
}
