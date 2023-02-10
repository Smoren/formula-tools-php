<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Exceptions;

class EmptyStreamException extends UnexpectedEofException
{
    public function __construct()
    {
        parent::__construct('Cannot parse an empty stream', null);
    }
}
