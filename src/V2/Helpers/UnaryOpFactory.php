<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Helpers;

use Smoren\FormulaTools\V2\Interfaces\IUnaryOpFactory;

/**
 * @template T
 * @template C of T
 * @implements IUnaryOpFactory<T>
 */
class UnaryOpFactory implements IUnaryOpFactory
{
    use OpDefinitionTrait {
        OpDefinitionTrait::__construct as private construct0;
    }
    /** @use UnaryOpFactoryTrait<T, C> */
    use UnaryOpFactoryTrait {
        UnaryOpFactoryTrait::__construct as private construct1;
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
