<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Exceptions;

class MissingOperatorException extends SyntaxException
{
    /**
     * @readonly
     * @var mixed
     */
    public $found;

    /**
     * @param mixed $token
     * @param int $position
     */
    public function __construct($token, int $position)
    {
        parent::__construct("Expected an operator, found {$this->stringify($token)}", $position);
        $this->found = $token;
    }
}
