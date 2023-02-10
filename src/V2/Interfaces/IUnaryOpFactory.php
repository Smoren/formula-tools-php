<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Interfaces;

/**
 * @template Result
 */
interface IUnaryOpFactory extends IOpDefinition
{
    /**
     * @param Result $child
     * @return Result
     */
    public function create($child);
}
