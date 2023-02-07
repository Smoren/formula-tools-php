<?php

namespace Smoren\FormulaTools\Helpers;

class LoopHelper
{
    /**
     * @template T
     * @param array<T> $input
     * @return \Generator<array{T, T}>
     */
    public static function pairwise(array $input): \Generator
    {
        foreach ($input as $currentValue) {
            if (!isset($prevValue)) {
                $prevValue = $currentValue;
                continue;
            }

            yield [$prevValue, $currentValue];

            $prevValue = $currentValue;
        }
    }
}
