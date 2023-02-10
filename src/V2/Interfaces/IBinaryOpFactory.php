<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Interfaces;

/**
 * @template Result
 */
interface IBinaryOpFactory extends IOpDefinition
{
    /**
     * @param Result $lhs
     * @param Result $rhs
     * @return Result
     */
    public function create($lhs, $rhs);
}
