<?php

namespace Smoren\FormulaTools\Exceptions;

class InappropriateTokenPairException extends TokenException
{
    /**
     * @var string
     */
    protected string $prevToken;

    /**
     * @param string $message
     * @param string $token
     * @param string $prevToken
     */
    public function __construct(string $message, string $token, string $prevToken)
    {
        parent::__construct($message, $token);
        $this->prevToken = $prevToken;
    }

    /**
     * @return string
     */
    public function getPrevToken(): string
    {
        return $this->prevToken;
    }
}
