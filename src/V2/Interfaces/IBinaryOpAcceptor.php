<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Interfaces;

/**
 * @template Token
 * @template Result
 */
interface IBinaryOpAcceptor extends IOpDefinition
{
    /**
     * @param Token $token
     * @return ?callable(Result $lhs, Result $rhs): Result
     */
    public function accept($token): ?callable;
}
