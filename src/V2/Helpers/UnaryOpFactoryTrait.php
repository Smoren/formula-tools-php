<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Helpers;

/**
 * @template T
 * @template C of T
 */
trait UnaryOpFactoryTrait
{
    /** @var class-string<C> */
    protected string $class;

    /**
     * @param class-string<C> $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param T $child
     * @return C
     */
    public function create($child) { return new $this->class($child); }
}
