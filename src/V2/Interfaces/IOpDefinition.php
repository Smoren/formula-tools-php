<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Interfaces;

interface IOpDefinition
{
    public function getLeftPrecedence(): int;
    public function getRightPrecedence(): int;
}
