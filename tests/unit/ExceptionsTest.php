<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\Tests\Unit;

use Codeception\Test\Unit;
use Smoren\FormulaTools\Exceptions\InappropriateTokenException;
use Smoren\FormulaTools\Exceptions\InappropriateTokenPairException;
use Smoren\FormulaTools\Exceptions\InvalidTokenException;
use Smoren\FormulaTools\Exceptions\TokenException;
use Smoren\FormulaTools\Helpers\LoopHelper;
use Smoren\FormulaTools\Validators\LogicFormulaValidator;

class ExceptionsTest extends Unit
{
    public function testTokenException(): void
    {
        // When
        $result = new TokenException('some message', 'some token');

        // Then
        $this->assertSame('some message', $result->getMessage());
        $this->assertSame('some token', $result->getToken());
    }

    public function testInappropriateTokenPairException(): void
    {
        // When
        $result = new InappropriateTokenPairException('some message', 'curr token', 'prev token');

        // Then
        $this->assertSame('some message', $result->getMessage());
        $this->assertSame('curr token', $result->getToken());
        $this->assertSame('prev token', $result->getPrevToken());
    }
}
