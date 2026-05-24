<?php

namespace app\models\providers;

use yii\base\Model;

class QiwiGetUIProvidersRequest extends Model
{
    public function build(): string
    {
        return <<<XML
<providers>
  <getUIProviders/>
</providers>
XML;
    }
}
