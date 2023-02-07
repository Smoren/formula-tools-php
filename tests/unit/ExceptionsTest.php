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
        $this->assertEquals('some message', $result->getMessage());
        $this->assertEquals('some token', $result->getToken());
    }

    public function testInappropriateTokenPairException(): void
    {
        // When
        $result = new InappropriateTokenPairException('some message', 'curr token', 'prev token');

        // Then
        $this->assertEquals('some message', $result->getMessage());
        $this->assertEquals('curr token', $result->getToken());
        $this->assertEquals('prev token', $result->getPrevToken());
    }
}
