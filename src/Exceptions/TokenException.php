<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\Exceptions;

class TokenException extends SyntaxException
{
    /**
     * @var string $token
     */
    protected string $token;

    /**
     * @param string $message
     * @param string|null $token
     */
    public function __construct(string $message, string $token)
    {
        parent::__construct($message);
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
