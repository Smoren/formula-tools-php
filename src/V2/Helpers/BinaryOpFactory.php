<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Helpers;

use Smoren\FormulaTools\V2\Interfaces\IBinaryOpFactory;

/**
 * @template T
 * @template C of T
 * @implements IBinaryOpFactory<T>
 */
class BinaryOpFactory implements IBinaryOpFactory
{
    use OpDefinitionTrait {
        OpDefinitionTrait::__construct as private construct0;
    }
    /** @use BinaryOpFactoryTrait<T, C> */
    use BinaryOpFactoryTrait {
        BinaryOpFactoryTrait::__construct as private construct1;
    }

    /**
     * @param int $leftPrecedence
     * @param int $rightPrecedence
     * @param class-string<C> $class
     */
    public function __construct(int $leftPrecedence, int $rightPrecedence, string $class)
    {
        $this->construct0($leftPrecedence, $rightPrecedence);
        $this->construct1($class);
    }
}
