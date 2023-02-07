<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\Validators;

use InvalidArgumentException;
use Smoren\FormulaTools\Exceptions\InappropriateTokenException;
use Smoren\FormulaTools\Exceptions\InappropriateTokenPairException;
use Smoren\FormulaTools\Exceptions\InvalidTokenException;
use Smoren\FormulaTools\Exceptions\SyntaxException;
use Smoren\FormulaTools\Helpers\LoopHelper;

class LogicFormulaValidator
{
    /**
     * @var array<string>
     */
    protected array $unaryOperators;
    /**
     * @var array<string>
     */
    protected array $binaryOperators;

    /**
     * @param array<string> $unaryOperators
     * @param array<string> $binaryOperators
     */
    public function __construct(array $unaryOperators, array $binaryOperators)
    {
        $this->unaryOperators = $unaryOperators;
        $this->binaryOperators = $binaryOperators;
    }

    /**
     * @param array<string> $tokens
     * @return void
     *
     * @throws InvalidArgumentException
     * @throws SyntaxException
     */
    public function validate(array $tokens): void
    {
        if (!count($tokens)) {
            return;
        }

        $bracketsCount = 0;

        foreach ($tokens as $token) {
            if (!is_string($token)) {
                throw new InvalidArgumentException("Token must be string");
            }

            switch (true) {
                case $this->isOpeningBracket($token):
                    $bracketsCount++;
                    break;
                case $this->isClosingBracket($token):
                    $bracketsCount--;
                    break;
                case !$this->isValidToken($token):
                    throw new InvalidTokenException("Token is invalid", $token);
            }

            if ($bracketsCount < 0) {
                throw new InappropriateTokenException("Brackets error", $token);
            }
        }

        $lastToken = $tokens[count($tokens) - 1];

        if ($bracketsCount !== 0) {
            throw new InappropriateTokenException(
                "Brackets error",
                $lastToken
            );
        }

        if ($this->isOperator($lastToken)) {
            throw new InappropriateTokenException(
                "The last token '{$lastToken}' cannot be operator",
                $lastToken
            );
        }

        /**
         * @var string $lhs
         * @var string $rhs
         */
        foreach (LoopHelper::pairwise($tokens) as [$lhs, $rhs]) {
            switch (true) {
                case $this->isOperand($lhs)
                    && ($this->isOperand($rhs) || $this->isOpeningBracket($rhs) || $this->isUnaryOperator($rhs)):
                    throw new InappropriateTokenPairException(
                        "Inappropriate token '{$rhs}' after operand '{$lhs}'",
                        $rhs,
                        $lhs
                    );
                case $this->isOpeningBracket($lhs)
                    && ($this->isClosingBracket($rhs) || $this->isBinaryOperator($rhs)):
                    throw new InappropriateTokenPairException(
                        "Inappropriate token '{$rhs}' after opening bracket",
                        $rhs,
                        $lhs
                    );
                case $this->isClosingBracket($lhs)
                    && ($this->isOpeningBracket($rhs) || $this->isOperand($rhs) || $this->isUnaryOperator($rhs)):
                    throw new InappropriateTokenPairException(
                        "Inappropriate token '{$rhs}' after closing bracket",
                        $rhs,
                        $lhs
                    );
                case $this->isUnaryOperator($lhs)
                    && ($this->isOperator($rhs) || $this->isClosingBracket($rhs)):
                    throw new InappropriateTokenPairException(
                        "Inappropriate token '{$rhs}' after unary operator '{$lhs}'",
                        $rhs,
                        $lhs
                    );
                case $this->isBinaryOperator($lhs)
                    && ($this->isBinaryOperator($rhs) || $this->isClosingBracket($rhs)):
                    throw new InappropriateTokenPairException(
                        "Inappropriate token '{$rhs}' after binary operator '{$lhs}'",
                        $rhs,
                        $lhs
                    );
            }
        }
    }

    protected function isBracket(string $token): bool
    {
        return $this->isOpeningBracket($token) || $this->isClosingBracket($token);
    }

    protected function isOpeningBracket(string $token): bool
    {
        return $token === '(';
    }

    protected function isClosingBracket(string $token): bool
    {
        return $token === ')';
    }

    protected function isOperator(string $token): bool
    {
        return $this->isUnaryOperator($token) || $this->isBinaryOperator($token);
    }

    protected function isUnaryOperator(string $token): bool
    {
        return in_array($token, $this->unaryOperators);
    }

    protected function isBinaryOperator(string $token): bool
    {
        return in_array($token, $this->binaryOperators);
    }

    protected function isOperand(string $token): bool
    {
        return !$this->isBracket($token) && !$this->isOperator($token);
    }

    private function isValidToken(string $token): bool
    {
        return $this->isBracket($token) || !preg_match('/[\(\)]/', $token);
    }
}
