<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Exceptions;

class MissingOperandException extends UnexpectedEofException
{
    public function __construct()
    {
        parent::__construct('Unexpected end of input; expecting a value', null);
    }
}
