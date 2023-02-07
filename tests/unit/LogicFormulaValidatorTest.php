<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\Tests\Unit;

use Codeception\Test\Unit;
use Smoren\FormulaTools\Exceptions\InappropriateTokenException;
use Smoren\FormulaTools\Exceptions\InappropriateTokenPairException;
use Smoren\FormulaTools\Exceptions\InvalidTokenException;
use Smoren\FormulaTools\Validators\LogicFormulaValidator;

class LogicFormulaValidatorTest extends Unit
{
    /**
     * @dataProvider dataProviderForValid
     * @param array<string> $unaryOperators
     * @param array<string> $binaryOperators
     * @param array<string> $tokens
     * @return void
     */
    public function testValid(array $unaryOperators, array $binaryOperators, array $tokens): void
    {
        // Given
        $validator = new LogicFormulaValidator($unaryOperators, $binaryOperators);

        // When
        $validator->validate($tokens);

        // Then
        $this->once();
    }

    /**
     * @return array<array{array<string>, array<string>, array<string>}>
     */
    public function dataProviderForValid(): array
    {
        return [
            [
                ['!'],
                ['|', '&'],
                [],
            ],
            [
                ['!'],
                ['|', '&'],
                ['a'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '|', 'b'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '&', 'b'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '|', 'b', '|', 'c'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '&', 'b', '|', 'c'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '|', 'b', '&', 'c'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '&', 'b', '&', 'c'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', 'a', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '(', 'a', ')', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', 'a', '&', 'b', '&', 'c', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', 'a', '&', 'b', ')', '&', 'c'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '(', 'a', '&', 'b', ')', '&', 'c', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '(', 'a', '|', 'b', ')', '&', 'c', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '(', 'a', '|', 'b', ')', '&', 'c', ')', '|', 'd'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['!', '(', '(', 'a', '|', 'b', ')', '&', 'c', ')', '|', 'd'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['!', '(', '!', '(', 'a', '|', 'b', ')', '&', 'c', ')', '|', 'd'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['!', 'a'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['!', '(', 'a', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['!', '(', '!', 'a', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'OR', 'b'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'AND', 'b'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'OR', 'b', 'OR', 'c'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'AND', 'b', 'OR', 'c'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'OR', 'b', 'AND', 'c'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'AND', 'b', 'AND', 'c'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'a', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', '(', 'a', ')', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'a', 'AND', 'b', 'AND', 'c', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'a', 'AND', 'b', ')', 'AND', 'c'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', '(', 'a', 'AND', 'b', ')', 'AND', 'c', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', '(', 'a', 'OR', 'b', ')', 'AND', 'c', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', '(', 'a', 'OR', 'b', ')', 'AND', 'c', ')', 'OR', 'd'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['NOT', '(', '(', 'a', 'OR', 'b', ')', 'AND', 'c', ')', 'OR', 'd'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['NOT', '(', 'NOT', '(', 'a', 'OR', 'b', ')', 'AND', 'c', ')', 'OR', 'd'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['NOT', 'a'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['NOT', '(', 'a', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['NOT', '(', 'NOT', 'a', ')'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForInvalidArgumentException
     * @param array<string> $unaryOperators
     * @param array<string> $binaryOperators
     * @param array<string> $tokens
     * @return void
     */
    public function testInvalidArgumentException(array $unaryOperators, array $binaryOperators, array $tokens): void
    {
        // Given
        $validator = new LogicFormulaValidator($unaryOperators, $binaryOperators);

        // Then
        $this->expectException(\InvalidArgumentException::class);

        // When
        $validator->validate($tokens);
    }

    /**
     * @return array<array{array<string>, array<string>, array<string>}>
     */
    public function dataProviderForInvalidArgumentException(): array
    {
        return [
            [
                ['!'],
                ['|', '&'],
                [1],
            ],
            [
                ['!'],
                ['|', '&'],
                [1.1],
            ],
            [
                ['!'],
                ['|', '&'],
                [true],
            ],
            [
                ['!'],
                ['|', '&'],
                [false],
            ],
            [
                ['!'],
                ['|', '&'],
                [null],
            ],
            [
                ['!'],
                ['|', '&'],
                [NAN],
            ],
            [
                ['!'],
                ['|', '&'],
                [[]],
            ],
            [
                ['!'],
                ['|', '&'],
                [['a']],
            ],
            [
                ['!'],
                ['|', '&'],
                [(object)['a']],
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '&', '(', 0, '|', 'b', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '!', '(', 0, 'c', 'b', ')', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [1],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [1.1],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [true],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [false],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [null],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [NAN],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [[]],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [['a']],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [(object)['a']],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'AND', '(', 0, 'OR', 'b', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'NOT', '(', 0, 'c', 'b', ')', ')'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForInvalidTokenException
     * @param array<string> $unaryOperators
     * @param array<string> $binaryOperators
     * @param array<string> $tokens
     * @return void
     */
    public function testInvalidTokenException(array $unaryOperators, array $binaryOperators, array $tokens): void
    {
        // Given
        $validator = new LogicFormulaValidator($unaryOperators, $binaryOperators);

        // Then
        $this->expectException(InvalidTokenException::class);

        // When
        $validator->validate($tokens);
    }

    /**
     * @return array<array{array<string>, array<string>, array<string>}>
     */
    public function dataProviderForInvalidTokenException(): array
    {
        return [
            [
                ['!'],
                ['|', '&'],
                ['(a)'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['!', '(', '!', '(', '(a)', '|', 'b', ')', '&', 'c', ')', '|', 'd'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(a)'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['NOT', '(', 'NOT', '(', '(a)', 'OR', 'b', ')', 'AND', 'c', ')', 'OR', 'd'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForBracketsError
     * @param array<string> $unaryOperators
     * @param array<string> $binaryOperators
     * @param array<string> $tokens
     * @return void
     */
    public function testBracketsError(array $unaryOperators, array $binaryOperators, array $tokens): void
    {
        // Given
        $validator = new LogicFormulaValidator($unaryOperators, $binaryOperators);

        // Then
        $this->expectException(InappropriateTokenException::class);
        $this->expectExceptionMessage('Brackets error');

        // When
        $validator->validate($tokens);
    }

    /**
     * @return array<array{array<string>, array<string>, array<string>}>
     */
    public function dataProviderForBracketsError(): array
    {
        return [
            [
                ['!'],
                ['|', '&'],
                ['('],
            ],
            [
                ['!'],
                ['|', '&'],
                [')'],
            ],
            [
                ['!'],
                ['|', '&'],
                [')', 'a'],
            ],
            [
                ['!'],
                ['|', '&'],
                [')', '('],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '(', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', ')', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', ')', '(', ')', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                [')', 'a', '('],
            ],
            [
                ['!'],
                ['|', '&'],
                [')', '&', '(', 'a', ')', '|', '('],
            ],
            [
                ['!'],
                ['|', '&'],
                [')', '&', '(', 'a', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '(', 'a', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', 'a', ')', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', 'a', ')', '(', 'a', ')', ')'],
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', 'a', ')', '&', '(', 'a', ')', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['('],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [')', 'a'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [')', '('],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', '(', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', ')', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', ')', '(', ')', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [')', 'a', '('],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [')', 'AND', '(', 'a', ')', 'OR', '('],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                [')', 'AND', '(', 'a', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', '(', 'a', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'a', ')', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'a', ')', '(', 'a', ')', ')'],
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'a', ')', 'AND', '(', 'a', ')', ')'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForLastTokenOperatorError
     * @param array<string> $unaryOperators
     * @param array<string> $binaryOperators
     * @param array<string> $tokens
     * @param string $badToken
     * @return void
     */
    public function testLastTokenOperatorError(array $unaryOperators, array $binaryOperators, array $tokens, string $badToken): void
    {
        // Given
        $validator = new LogicFormulaValidator($unaryOperators, $binaryOperators);

        // Then
        $this->expectException(InappropriateTokenException::class);
        $this->expectExceptionMessage("The last token '{$badToken}' cannot be operator");

        // When
        $validator->validate($tokens);
    }

    /**
     * @return array<array{array<string>, array<string>, array<string>, string}>
     */
    public function dataProviderForLastTokenOperatorError(): array
    {
        return [
            [
                ['!'],
                ['|', '&'],
                ['&'],
                '&',
            ],
            [
                ['!'],
                ['|', '&'],
                ['|'],
                '|',
            ],
            [
                ['!'],
                ['|', '&'],
                ['!'],
                '!',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '|', 'b', '&'],
                '&',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '&', 'b', '|'],
                '|',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '|', '!'],
                '!',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['AND'],
                'AND',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['OR'],
                'OR',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['NOT'],
                'NOT',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'OR', 'b', 'AND'],
                'AND',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'AND', 'b', 'OR'],
                'OR',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'OR', 'NOT'],
                'NOT',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForInappropriateTokenAfterOperand
     * @param array<string> $unaryOperators
     * @param array<string> $binaryOperators
     * @param array<string> $tokens
     * @param string $badToken
     * @param string $previousToken
     * @return void
     */
    public function testInappropriateTokenAfterOperand(array $unaryOperators, array $binaryOperators, array $tokens, string $badToken, string $previousToken): void
    {
        // Given
        $validator = new LogicFormulaValidator($unaryOperators, $binaryOperators);

        // Then
        $this->expectException(InappropriateTokenPairException::class);
        $this->expectExceptionMessage("Inappropriate token '{$badToken}' after operand '{$previousToken}'");

        // When
        $validator->validate($tokens);
    }

    /**
     * @return array<array{array<string>, array<string>, array<string>, string, string}>
     */
    public function dataProviderForInappropriateTokenAfterOperand(): array
    {
        return [
            [
                ['!'],
                ['|', '&'],
                ['a', 'b'],
                'b',
                'a',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '|', 'b', 'c'],
                'c',
                'b',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '|', 'b', 'c', '&', 'd'],
                'c',
                'b',
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', 'a', '|', '(', 'b', 'c', ')', '&', 'd', ')', 'e'],
                'c',
                'b',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '(', ')'],
                '(',
                'a',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '(', 'b', ')'],
                '(',
                'a',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '|', 'b', '(', 'c', ')'],
                '(',
                'b',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '|', 'b', '(', 'c', '&', 'd', ')'],
                '(',
                'b',
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', 'a', '|', 'b', '(', 'c', ')', '&', 'd', ')', 'e'],
                '(',
                'b',
            ],
            [
                ['!', '<>'],
                ['|', '&'],
                ['a', '!', 'b'],
                '!',
                'a',
            ],
            [
                ['!', '<>'],
                ['|', '&'],
                ['a', '<>', 'b'],
                '<>',
                'a',
            ],
            [
                ['!', '<>'],
                ['|', '&'],
                ['a', '&', 'b', '!', 'c'],
                '!',
                'b',
            ],
            [
                ['!', '<>'],
                ['|', '&'],
                ['a', '|', '<>', 'b', '!', 'c'],
                '!',
                'b',
            ],
            [
                ['!', '<>'],
                ['|', '&'],
                ['(', 'a', '|', '<>', 'b', '!', ')', 'c'],
                '!',
                'b',
            ],
            [
                ['!', '<>'],
                ['|', '&'],
                ['(', '(', 'a', '|', '<>', 'b', '!', ')', 'c', ')'],
                '!',
                'b',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'b'],
                'b',
                'a',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'OR', 'b', 'c'],
                'c',
                'b',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'OR', 'b', 'c', 'AND', 'd'],
                'c',
                'b',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'a', 'OR', '(', 'b', 'c', ')', 'AND', 'd', ')', 'e'],
                'c',
                'b',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', '(', ')'],
                '(',
                'a',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', '(', 'b', ')'],
                '(',
                'a',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'OR', 'b', '(', 'c', ')'],
                '(',
                'b',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'OR', 'b', '(', 'c', 'AND', 'd', ')'],
                '(',
                'b',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'a', 'OR', 'b', '(', 'c', ')', 'AND', 'd', ')', 'e'],
                '(',
                'b',
            ],
            [
                ['NOT', '<>'],
                ['OR', 'AND'],
                ['a', 'NOT', 'b'],
                'NOT',
                'a',
            ],
            [
                ['NOT', '<>'],
                ['OR', 'AND'],
                ['a', '<>', 'b'],
                '<>',
                'a',
            ],
            [
                ['NOT', '<>'],
                ['OR', 'AND'],
                ['a', 'AND', 'b', 'NOT', 'c'],
                'NOT',
                'b',
            ],
            [
                ['NOT', '<>'],
                ['OR', 'AND'],
                ['a', 'OR', '<>', 'b', 'NOT', 'c'],
                'NOT',
                'b',
            ],
            [
                ['NOT', '<>'],
                ['OR', 'AND'],
                ['(', 'a', 'OR', '<>', 'b', 'NOT', ')', 'c'],
                'NOT',
                'b',
            ],
            [
                ['NOT', '<>'],
                ['OR', 'AND'],
                ['(', '(', 'a', 'OR', '<>', 'b', 'NOT', ')', 'c', ')'],
                'NOT',
                'b',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForInappropriateTokenAfterOpeningBracket
     * @param array<string> $unaryOperators
     * @param array<string> $binaryOperators
     * @param array<string> $tokens
     * @param string $badToken
     * @return void
     */
    public function testInappropriateTokenAfterOpeningBracket(array $unaryOperators, array $binaryOperators, array $tokens, string $badToken): void
    {
        // Given
        $validator = new LogicFormulaValidator($unaryOperators, $binaryOperators);

        // Then
        $this->expectException(InappropriateTokenPairException::class);
        $this->expectExceptionMessage("Inappropriate token '{$badToken}' after opening bracket");

        // When
        $validator->validate($tokens);
    }

    /**
     * @return array<array{array<string>, array<string>, array<string>, string}>
     */
    public function dataProviderForInappropriateTokenAfterOpeningBracket(): array
    {
        return [
            [
                ['!'],
                ['|', '&'],
                ['(', ')'],
                ')',
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '(', ')', ')'],
                ')',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '&', '(', ')', '|', 'c'],
                ')',
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', 'a', '&', '(', ')', ')', '|', 'c'],
                ')',
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '&', ')'],
                '&',
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '&', 'a', ')'],
                '&',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '&', '(', '&', 'a', ')'],
                '&',
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '|', ')'],
                '|',
            ],
            [
                ['!'],
                ['|', '&'],
                ['(', '|', 'a', ')'],
                '|',
            ],
            [
                ['!'],
                ['|', '&'],
                ['a', '|', '(', '|', 'a', ')'],
                '|',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', ')'],
                ')',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', '(', ')', ')'],
                ')',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'AND', '(', ')', 'OR', 'c'],
                ')',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'a', 'AND', '(', ')', ')', 'OR', 'c'],
                ')',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'AND', ')'],
                'AND',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'AND', 'a', ')'],
                'AND',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'AND', '(', 'AND', 'a', ')'],
                'AND',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'OR', ')'],
                'OR',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['(', 'OR', 'a', ')'],
                'OR',
            ],
            [
                ['NOT'],
                ['OR', 'AND'],
                ['a', 'OR', '(', 'OR', 'a', ')'],
                'OR',
            ],
        ];
    }
}
