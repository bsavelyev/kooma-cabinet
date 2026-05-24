<?php

namespace tests\unit\models;

use app\models\forms\ChangeOperationsStatusForm;
use Codeception\Test\Unit;

class ChangeOperationsStatusFormTest extends Unit
{
    public function testRequiredOperationsIds()
    {
        $model = new ChangeOperationsStatusForm();

        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('operations_ids');
    }

    public function testValidOperationsIds()
    {
        $model = new ChangeOperationsStatusForm([
            'operations_ids' => 'ext-1, ext-2',
        ]);

        verify($model->validate())->true();
    }
}
