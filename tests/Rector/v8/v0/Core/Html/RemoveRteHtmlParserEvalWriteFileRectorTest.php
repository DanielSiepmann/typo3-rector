<?php

declare(strict_types=1);

namespace Ssch\TYPO3Rector\Tests\Rector\v8\v0\Core\Html;

use Iterator;
use Ssch\TYPO3Rector\Tests\AbstractRectorWithConfigTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Ssch\TYPO3Rector\Rector\v8\v0\RemoveRteHtmlParserEvalWriteFileRector
 * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-72384-RemovedDeprecatedCodeFromHtmlParser.html
 */
final class RemoveRteHtmlParserEvalWriteFileRectorTest extends AbstractRectorWithConfigTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideDataForTest(): Iterator
    {
        yield [new SmartFileInfo(__DIR__ . '/Fixture/remove_rte_html_parser_eval_write_file.php.inc')];
    }
}
