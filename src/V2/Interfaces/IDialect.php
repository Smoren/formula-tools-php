<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Interfaces;

/**
 * @template Token
 * @template Result
 */
interface IDialect
{
    /**
     * @param Token $token
     * @param IParser<Token, Result> $parser
     * @return ?(IUnaryOpFactory<Result> | IUnaryOpAcceptor<Token, Result>)
     */
    public function acceptPrefixOrCircumfixOp($token, IParser $parser);

    /**
     * @param Token $token
     * @param IParser<Token, Result> $parser
     * @return ?(IUnaryOpFactory<Result> | IBinaryOpAcceptor<Token, Result> | IBinaryOpFactory<Result>)
     */
    public function acceptPostfixOrInfixOp($token, IParser $parser);

    /**
     * @param IParser<Token, Result> $parser
     * @return ?IBinaryOpFactory<Result>
     */
    public function getJuxtapositionOp(IParser $parser);

    /**
     * @param Token $token
     * @param IParser<Token, Result> $parser
     * @return Result
     */
    public function parseTerm($token, IParser $parser);
}
