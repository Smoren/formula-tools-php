<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Exceptions;

use Smoren\FormulaTools\V2\Interfaces\IOpDefinition;

class OpAssociationException extends SyntaxException
{
    /** @readonly */
    public IOpDefinition $op;

    public function __construct(IOpDefinition $op, int $position)
    {
        parent::__construct('Operator `'.get_class($op).'` is non-associative', $position);
        $this->op = $op;
    }
}
