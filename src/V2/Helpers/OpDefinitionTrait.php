<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Helpers;

trait OpDefinitionTrait
{
    protected int $leftPrecedence, $rightPrecedence;

    public function __construct(int $leftPrecedence, int $rightPrecedence)
    {
        $this->leftPrecedence = $leftPrecedence;
        $this->rightPrecedence = $rightPrecedence;
    }

    public function getLeftPrecedence():  int { return $this->leftPrecedence; }
    public function getRightPrecedence(): int { return $this->rightPrecedence; }
}
