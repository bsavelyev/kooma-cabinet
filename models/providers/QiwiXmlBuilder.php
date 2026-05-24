<?php

namespace app\models\providers;

use Yii;

class QiwiXmlBuilder
{
    private string $terminalId;

    private string $login;

    private string $sign;

    public function __construct(array $config)
    {
        $this->terminalId = $config['terminal'];
        $this->login = $config['login'];
        $this->sign = $config['sign'];
    }

    public function wrap(string $bodyXml): string
    {
        $headerXml = <<<XML
<client software="Dealer v0" terminal="{$this->terminalId}"/>
<auth login="{$this->login}" signAlg="MD5" sign="{$this->sign}"/>
XML;

        return <<<XML
<?xml version="1.0" encoding="windows-1251"?>
<request>
    {$headerXml}
    {$bodyXml}
</request>
XML;
    }
}
