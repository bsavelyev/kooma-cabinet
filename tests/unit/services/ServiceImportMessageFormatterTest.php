<?php

namespace tests\unit\services;

use app\services\ServiceImportMessageFormatter;
use Codeception\Test\Unit;

class ServiceImportMessageFormatterTest extends Unit
{
    /** @var ServiceImportMessageFormatter */
    private $formatter;

    protected function _before()
    {
        $this->formatter = new ServiceImportMessageFormatter();
    }

    public function testFormatEmpty()
    {
        verify($this->formatter->format([]))->equals([]);
    }

    public function testFormatNotFound()
    {
        $messages = $this->formatter->format([], ['svc-a', 'svc-b']);

        $this->assertCount(1, $messages);
        $this->assertStringContainsString('svc-a', $messages[0]);
        $this->assertStringContainsString('Нет в списки у Qiwi', $messages[0]);
    }

    public function testFormatCreatedAndExists()
    {
        $messages = $this->formatter->format([
            ['status' => 'created', 'user_name' => 'New Service'],
            ['status' => 'exists', 'user_name' => 'Old Service'],
        ]);

        $this->assertCount(2, $messages);
        $this->assertStringContainsString('New Service', $messages[0]);
        $this->assertStringContainsString('Old Service', $messages[1]);
    }
}
