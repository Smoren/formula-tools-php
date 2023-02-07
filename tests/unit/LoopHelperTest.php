<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\Tests\Unit;

use Codeception\Test\Unit;
use Smoren\FormulaTools\Exceptions\InappropriateTokenException;
use Smoren\FormulaTools\Exceptions\InappropriateTokenPairException;
use Smoren\FormulaTools\Exceptions\InvalidTokenException;
use Smoren\FormulaTools\Helpers\LoopHelper;
use Smoren\FormulaTools\Validators\LogicFormulaValidator;

class LoopHelperTest extends Unit
{
    /**
     * @dataProvider dataProviderForPairwise
     * @param array $input
     * @param array $expected
     * @return void
     */
    public function testPairwise(array $input, array $expected): void
    {
        // Given
        $result = [];

        // When
        foreach (LoopHelper::pairwise($input) as $pair) {
            $result[] = $pair;
        }

        // Then
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array<array{array<string>, array<string>}>
     */
    public function dataProviderForPairwise(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                [1],
                [],
            ],
            [
                [1, 2],
                [[1, 2]],
            ],
            [
                [1, 2, 3],
                [[1, 2], [2, 3]],
            ],
            [
                [1, 2, 3, 4],
                [[1, 2], [2, 3], [3, 4]],
            ],
            [
                [1, 2, 3, 4, 5],
                [[1, 2], [2, 3], [3, 4], [4, 5]],
            ],
            [
                ['1', '2', '3', '4', '5'],
                [['1', '2'], ['2', '3'], ['3', '4'], ['4', '5']],
            ],
        ];
    }
}
