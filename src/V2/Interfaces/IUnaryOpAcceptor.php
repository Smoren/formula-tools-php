<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Interfaces;

/**
 * @template Token
 * @template Result
 */
interface IUnaryOpAcceptor
{
    /**
     * @param Token $token
     * @return ?callable(Result): Result
     */
    public function accept($token): ?callable;
}
