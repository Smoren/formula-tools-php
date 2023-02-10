<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2;

use Smoren\FormulaTools\V2\Exceptions\{
    EmptyStreamException,
    MissingOperandException,
    MissingOperatorException,
    OpAssociationException,
    UnclosedBracketException,
};
use Smoren\FormulaTools\V2\Interfaces\{
    IBinaryOpAcceptor,
    IBinaryOpFactory,
    IDialect,
    IOpDefinition,
    IParser,
    IUnaryOpAcceptor,
    IUnaryOpFactory,
};

/**
 * @template Token
 * @template Result
 * @implements IParser<Token, Result>
 */
class FormulaParser implements IParser
{
    /** @var IDialect<Token, Result> */
    protected IDialect $dialect;
    protected int $position = 0;
    /** @var Result[] */
    protected array $resultStack = [];
    /** @var (IOpDefinition | IUnaryOpAcceptor<Token, Result>)[] */
    protected array $opStack = [];
    /** @var (IUnaryOpAcceptor<Token, Result> | IBinaryOpAcceptor<Token, Result>)[] */
    protected array $acceptorStack = [];

    /**
     * @param IDialect<Token, Result> $dialect
     */
    public function __construct(IDialect $dialect)
    {
        $this->dialect = $dialect;
    }

    public function getDialect(): IDialect
    {
        return $this->dialect;
    }

    public function setDialect(IDialect $dialect)
    {
        $this->dialect = $dialect;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param IOpDefinition | IUnaryOpAcceptor<Token, Result> $op
     * @param int $followingPrecedence
     * @return bool
     * @phpstan-assert-if-true IUnaryOpFactory<Result> | IBinaryOpFactory<Result> $op
     */
    protected function shouldReduce($op, int $followingPrecedence): bool
    {
        if (!($op instanceof IUnaryOpFactory || $op instanceof IBinaryOpFactory)) {
            return false;
        }
        $prec = $op->getRightPrecedence();
        if ($prec !== $followingPrecedence) {
            return $prec > $followingPrecedence;
        }
        // An attempt to reduce a non-associative operator.
        throw new OpAssociationException($op, $this->position);
    }

    protected function reduce(int $followingPrecedence): void
    {
        $i = count($this->resultStack) - 1; // `i` always equals the index of the last node.
        for ($opIndex = count($this->opStack); $opIndex--; ) {
            $op = $this->opStack[$opIndex];
            if (!$this->shouldReduce($op, $followingPrecedence)) {
                return; // Reduction stops here.
            }

            // Perform one step of reduction.
            array_pop($this->opStack);
            if ($op instanceof IUnaryOpFactory) {
                // Reduce a unary operator.
                $this->resultStack[$i] = $op->create($this->resultStack[$i]);
            } else {
                // Reduce a binary operator.
                assert(count($this->resultStack) >= 2);
                $rhs = array_pop($this->resultStack);
                $i--;
                $this->resultStack[$i] = $op->create($this->resultStack[$i], $rhs);
            }
        }
    }

    /**
     * @param Token $token
     * @return bool
     */
    protected function acceptTerm($token): bool
    {
        $op = $this->dialect->acceptPrefixOrCircumfixOp($token, $this);
        if ($op === null) {
            // Not an operator - try to parse a term and push it onto the result stack.
            // This call may throw.
            $this->resultStack[] = $this->dialect->parseTerm($token, $this);
            return true;
        }

        if ($op instanceof IUnaryOpFactory) {
            $this->reduce($op->getLeftPrecedence()); // Check for non-associative operators.
        } else {
            $this->acceptorStack[] = $op; // Push the circumfix operator onto its stack.
        }
        // Push the unary/circumfix operator onto the operator stack.
        $this->opStack[] = $op;
        return false;
    }

    /**
     * @param Token $token
     * @return bool
     */
    protected function finishCircumfixOp($token): bool
    {
        if (!(bool)$this->acceptorStack) {
            return false;
        }
        // Check the token with the most deeply nested operator.
        $factory = $this->acceptorStack[count($this->acceptorStack) - 1]->accept($token);
        if ($factory === null) {
            return false;
        }

        // `token` is the second part of the *circumfix operator seen earlier.
        $this->reduce(PHP_INT_MIN); // Reduce the contained operand.
        $op = array_pop($this->opStack);
        array_pop($this->acceptorStack);
        if ($op instanceof IBinaryOpAcceptor) {
            assert(count($this->resultStack) >= 2);
            $rhs = array_pop($this->resultStack);
            $this->reduce($op->getLeftPrecedence()); // Reduce the left-hand-side operand.
            // We cannot reduce the postcircumfix operator right now because we need to check that
            // the operator that follows it respects associativity rules. So we push
            // the right-hand-side operand back onto the stack and create a stub binary operator
            // that remembers our right precedence.
            $this->resultStack[] = $rhs;
            $this->opStack[] = new class($op->getRightPrecedence(), $factory) implements IBinaryOpFactory {
                private int $precedence;
                /** @var callable(Result, Result): Result */
                private $factory;

                /**
                 * @param int $precedence
                 * @param callable(Result, Result): Result $factory
                 */
                public function __construct(int $precedence, callable $factory)
                {
                    $this->precedence = $precedence;
                    $this->factory = $factory;
                }

                public function getLeftPrecedence(): int {
                    throw new \LogicException("Unreachable");
                }

                public function getRightPrecedence(): int { return $this->precedence; }
                public function create($lhs, $rhs) { return ($this->factory)($lhs, $rhs); }
            };
        } else {
            // A circumfix operator does not have precedence. Reduce it right away.
            $i = count($this->resultStack) - 1;
            /** @var callable(Result): Result $factory */
            $this->resultStack[$i] = $factory($this->resultStack[$i]);
        }
        return true;
    }

    /**
     * @param Token $token
     * @return bool
     */
    protected function acceptBinaryOp($token): bool
    {
        // The second part of a *circumfix operator should be prioritized over a binary or postfix
        // operator, otherwise the former would be irreducible at all.
        if ($this->finishCircumfixOp($token)) {
            return false;
        }

        $op = $this->dialect->acceptPostfixOrInfixOp($token, $this);
        if ($op !== null) {
            $this->reduce($op->getLeftPrecedence()); // Reduce its left-hand-side operand.
            $this->opStack[] = $op;
            if ($op instanceof IBinaryOpAcceptor) {
                $this->acceptorStack[] = $op;
                return true; // It is binary.
            }
            return $op instanceof IBinaryOpFactory;
        }

        // We failed to recognize a binary operator in this dialect. Try juxtaposition if defined.
        $op = $this->dialect->getJuxtapositionOp($this);
        if ($op !== null) {
            $this->reduce($op->getLeftPrecedence()); // Reduce its left-hand-side operand.
            $this->opStack[] = $op;
            // If `token` is a term, then we should be waiting for another binary operator,
            // and vice versa.
            return !$this->acceptTerm($token);
        }
        throw new MissingOperatorException($token, $this->position);
    }

    /**
     * @param iterable<Token> $tokens
     * @return Result
     */
    public function parse(iterable $tokens)
    {
        assert(!(bool)$this->resultStack); // We are not reenterable.
        assert(!(bool)$this->opStack);
        $waitingForTerm = true;
        try {
            foreach ($tokens as $this->position => $token) {
                if ($waitingForTerm) {
                    if ($this->acceptTerm($token)) {
                        $waitingForTerm = false;
                    }
                } elseif ($this->acceptBinaryOp($token)) {
                    $waitingForTerm = true;
                }
            }

            if ($waitingForTerm) {
                throw count($this->resultStack) === 0 ? (
                    new EmptyStreamException()
                ) : new MissingOperandException();
            }
            $this->reduce(PHP_INT_MIN);
            if ((bool)$this->opStack) {
                throw new UnclosedBracketException();
            }
        } catch (\Throwable $e) {
            $this->resultStack = []; // Break a possible reference cycle.
            $this->opStack = [];
            $this->acceptorStack = [];
            throw $e;
        }

        assert(count($this->resultStack) === 1);
        return array_pop($this->resultStack);
    }
}
