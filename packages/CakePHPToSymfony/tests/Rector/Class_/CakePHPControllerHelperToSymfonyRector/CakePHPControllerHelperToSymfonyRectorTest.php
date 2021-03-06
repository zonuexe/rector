<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerHelperToSymfonyRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\Class_\CakePHPControllerHelperToSymfonyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPControllerHelperToSymfonyRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return CakePHPControllerHelperToSymfonyRector::class;
    }
}
