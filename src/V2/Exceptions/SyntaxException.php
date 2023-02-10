<?php

declare(strict_types=1);

namespace Smoren\FormulaTools\V2\Exceptions;

class SyntaxException extends \ErrorException
{
    /** @readonly */
    public ?int $position;

    public function __construct(string $msg, ?int $position)
    {
        $this->position = $position;
        if ($position !== null) {
            $position++;
            $msg = "Token #$position: $msg";
        }
        parent::__construct($msg);
    }

    /**
     * @param mixed $x
     * @return string
     */
    protected function stringify($x): string
    {
        return json_encode(
            $x,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE |
            JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR,
        );
    }
}
