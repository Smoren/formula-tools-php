<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Interfaces;

/**
 * @template Token
 * @template Result
 */
interface IParser
{
    public function getPosition(): int;

    /**
     * @return IDialect<Token, Result>
     */
    public function getDialect(): IDialect;

    /**
     * @param IDialect<Token, Result> $dialect
     * @return void
     */
    public function setDialect(IDialect $dialect);

    /**
     * @param iterable<Token> $tokens
     * @return Result
     */
    public function parse(iterable $tokens);
}
