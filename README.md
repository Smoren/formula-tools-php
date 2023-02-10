# PHP Formula Tools

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/smoren/formula-tools)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Smoren/formula-tools-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Smoren/formula-tools-php/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/Smoren/formula-tools-php/badge.svg?branch=master)](https://coveralls.io/github/Smoren/formula-tools-php?branch=master)
![Build and test](https://github.com/Smoren/formula-tools-php/actions/workflows/test_master.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)


## Overview

Formula Tools offer a highly configurable syntactic analyzer that parses _formulae_ — token streams
consisting of values, operators, parentheses, etc. Akin to [JSEP][jsep] but in PHP.

[jsep]: https://ericsmekens.github.io/jsep/

```php
final class PlusFactory implements IBinaryOpFactory
{
    function getLeftPrecedence():  int { return 0; }
    function getRightPrecedence(): int { return 1; }
    function create($lhs, $rhs) { return ['plus', $lhs, $rhs]; }
}

final class TimesFactory implements IBinaryOpFactory
{
    function getLeftPrecedence():  int { return 2; }
    function getRightPrecedence(): int { return 3; }
    function create($lhs, $rhs) { return ['times', $lhs, $rhs]; }
}

final class CalculatorDialect implements IDialect
{
    function acceptPostfixOrInfixOp($token, IParser $parser)
    {
        static $ops;
        $ops ??= [
            '+' => new PlusFactory(),
            '*' => new TimesFactory(),
        ];
        return $ops[$token] ?? null;
    }

    function acceptPrefixOrCircumfixOp($token, IParser $parser) { }
    function getJuxtapositionOp(IParser $parser) { }

    function parseTerm($token, IParser $parser)
    {
        return $token;
    }
}

$parser = new FormulaParser(new CalculatorDialect());
$ast = $parser->parse(['1', '+', '2', '*', '3', '+', '4']);
// => ['plus', ['plus', '1', ['times', '2', '3']], '4']
```


## How to install to your project

```sh
composer require smoren/formula-tools
```


## Guide

When using Formula Tools, you will deal with three kinds of entities: results of parsing, operator
definitions, and dialect definition.

You have complete control over what you will receive from `FormulaParser::parse`, be it
an `array<mixed>`, like in example above, a single `int` holding the result of the calculation,
or a typed abstract syntax tree like below:

```php
interface IMyAst { }

trait UnaryOpTrait
{
    /** @readonly */
    public IMyAst $child;

    function __construct(IMyAst $child)
    {
        $this->child = $child;
    }
}

trait BinaryOpTrait
{
    /** @readonly */
    public IMyAst $lhs, $rhs;

    function __construct(IMyAst $lhs, IMyAst $rhs)
    {
        $this->lhs = $lhs;
        $this->rhs = $rhs;
    }
}

final class OrNode implements IMyAst
{
    use BinaryOpTrait;
}

final class AndNode implements IMyAst
{
    use BinaryOpTrait;
}

final class NotNode implements IMyAst
{
    use UnaryOpTrait;
}

final class TermNode implements IMyAst
{
    /** @readonly */
    public string $value;

    function __construct(string $value)
    {
        $this->value = $value;
    }
}
```

Whilst S-exp-like representation may be attractive for its simplicity, you are encouraged to define
proper AST hierarchies (and enjoy static typing!) rather than detect shapes of your objects via
string comparisons.

An _operator definition_, or factory, declares _precedence_ of the operator (sometimes called
a _priority_, indicating how tightly it binds from each side — the higher, the better)
and _constructs_ the resulting values from the operands:

```php
use Smoren\FormulaTools\Interfaces\IBinaryOpFactory;

/** @implements IBinaryOpFactory<IMyAst> */
final class OrFactory implements IBinaryOpFactory
{
    function getLeftPrecedence():  int { return 0; }
    function getRightPrecedence(): int { return 10; }

    /**
     * @param IMyAst $lhs
     * @param IMyAst $rhs
     * @return IMyAst
     */
    function create($lhs, $rhs): IMyAst
    {
        return new OrNode($lhs, $rhs);
        // Or `return ['OR', $lhs, $rhs]`.
        // Or `return $lhs || $rhs` if interpreting right away.
        // Or whatever.
    }
}

// Similar for other operators.
```

Finally, a _dialect_ connects everything together: it tells which _operators_ are allowed in which
position:

```php
use Smoren\FormulaTools\Interfaces\{IDialect, IParser};

// `string` is the type of tokens, `IMyAst` is the type of the result.
/** @implements IDialect<string, IMyAst> */
final class LogicDialect implements IDialect
{
    private array $prefix;
    private array $infix;

    function __construct()
    {
        $this->prefix = [
            '!' => new NotFactory(),
        ];
        $this->infix = [
            '||' => new OrFactory(),
            '&&' => new AndFactory(),
        ];
    }

    /**
     * @param string $token
     * @return ?IUnaryOpFactory<IMyAst>
     */
    function acceptPrefixOrCircumfixOp($token, IParser $parser)
    {
        // Should return a factory if `token` is recognized as an operator and `null` otherwise.
        return $this->prefix[$token] ?? null;
    }

    /**
     * @param string $token
     * @return ?IBinaryOpFactory<IMyAst>
     */
    function acceptPostfixOrInfixOp($token, IParser $parser)
    {
        return $this->infix[$token] ?? null;
    }

    /**
     * @param string $token
     * @return IMyAst
     */
    function parseTerm($token, IParser $parser)
    {
        // Here you can do validation and throw an exception if `token` fails it.
        return new TermNode($token);
    }

    function getJuxtapositionOp(IParser $parser) { } // Will be explained shortly.
}
```

```php
use Smoren\FormulaTools\FormulaParser;

$parser = new FormulaParser(new LogicDialect());
$ast = $parser->parse(['a', '&&', '!', '!', 'b', '&&', 'c']);
/* =>
new AndNode(
    new AndNode(
        new TermNode('a'),
        new NotNode(new NotNode(new TermNode('b'))),
    ),
    new TermNode('c'),
)
*/
```

You may notice it is becoming tedious to declare all those classes by hand, so Formula Tools provide
two simple factories to ease the job:

```php
use Smoren\FormulaTools\Helpers\{BinaryOpFactory, UnaryOpFactory};

final class LogicDialect implements IDialect
{
    function __construct()
    {
        $this->infix = [
            '||' => new BinaryOpFactory(0,  10, OrNode::class),
            '&&' => new BinaryOpFactory(20, 30, AndNode::class),
        ];
        $this->prefix = [
            '!' => new UnaryOpFactory(50, 40, NotNode::class),
        ];
    }

    // ...
}
```


### Understanding precedence

As we’ve seen, precedence of an operator is represented by a plain integer. Any number is allowed
**except** `PHP_INT_MIN`, which is reserved by the library for internal usage. You might
be surprised though that there are, in fact, _two_ integers. This is because we need to deal
with associativity.

Take a look on the precedence of an operator. If `left < right`, then the operator is called
_left-associative_. If `left > right`, it is called _right-associative_. If they are equal,
the operator is called _non-associative_.

Suppose there is a `+` infix operator with `left=20, right=30` and we would like to parse
`a + b + c`. When two operators meet in the expression, the parser checks their priorities
_pointing to each other_ and chooses the operator with greater value:

     a     +     b     +     c
       <-20 30=>   <=20 30->

Here, the first `+` wins (30 vs 20) and takes over the `b` operand. Thus we say the addition
operation is left-associative.

    (a     +     b)    +     c

Another example: `**` with `left=90, right=80`:

    a     **     b     **     c
      <-90  80=>   <=90  80->

    a     **    (b     **     c)

So this exponentiation operator is right-associative.

And the last case. Suppose we would like to have an operator `=~` that searches through its
left-hand-side operand, interpreting its right-hand-side operand as a regex pattern. It returns
a boolean value, and, clearly, neither `(a =~ b) =~ c` nor `a =~ (b =~ c)` make any sense: you
cannot search in a bool (bools are not strings) and you cannot search with a bool (bools are
not regexes either). So, instead of defining `a =~ b =~ c` to mean one of the above meaningless
constructs, we would like to forbid chaining it at all. We can achieve that by declaring its left
and right precedence equal:

    a     =~     b     =~     c
      <-60  60=>   <=60  60->

The parser cannot decide how it should interpret that expression, thus it throws an exception
indicating `=~` is non-associative.

So far, we’ve been inspecting infix operators. Somewhat counterintuitive, prefix operators do have
two precedence values as well. Of course, their left precedence cannot affect grouping, but you can
make an operator non-associative by defining `left=right`:

        !          !     a
    <-85 85=>  <=85 85->

In the example above, we disallow `!!a` notation. `!(!a)` will still work (provided you have
registered the `()` operator; see below).

Please note that **prefix** operators should have **maximal left precedence** and **postfix**
operators should have **maximal right precedence**. Otherwise, really weird things will
be happening.


### Kinds of operators

So far, we’ve seen ordinary prefix and infix operators everyone is familiar with. However, Formula
Tools have support for more exotic ones as well. Here is the full list.

* **Prefix unary**: `&x`, `@x`, etc. Declared in `Dialect::acceptPrefixOrCircumfixOp`; their
  definition must implement `IUnaryOpFactory<Result>`.
* **Postfix unary**: `x!`, `x...`. Declared in `Dialect::acceptPostfixOrInfixOp`; their definition
  must implement `IUnaryOpFactory<Result>`.
* **Infix binary**: `x >>= y`, `x <*> y`. Declared in `Dialect::acceptPostfixOrInfixOp`; their
  definition must implement `IBinaryOpFactory<Result>`.
* **Circumfix unary**: `(x)`, `|x|`, `[=[x]=]`. Declared in `Dialect::acceptPrefixOrCircumfixOp`;
  their definition must implement `IUnaryOpAcceptor<Token, Result>`. Please note they have
  no precedence at all since there is only one way to read them.
* **Postcircumfix binary**: `x(y)`, `x[y]`, `x->{y}`. Declared in `Dialect::acceptPostfixOrInfixOp`;
  their definition must implement `IBinaryOpAcceptor<Token, Result>`.
* **Juxtaposition binary**: `x y`.  Declared in `Dialect::getJuxtapositionOp`; its definition must
  implement `IBinaryOpFactory<Result>`.

Let’s have a closer look at \*circumfix ones. They differ from others in that they have two
components, e.g., `->{` and `}`. So we need to determine not only where such operator occurs
but where it ends as well. Here’s how you implement them:

```php
/** @implements IUnaryOpAcceptor<string, IMyAst> */
final class ParenthesesAcceptor implements IUnaryOpAcceptor
{
    /**
     * @param string $token
     * @return ?callable(IMyAst): IMyAst
     */
    function accept($token): ?callable
    {
        // This method will be called to check if `token` closes the operator.
        if ($token === ')') {
            // We can define `(x)` as a completely transparent operator, i.e., one that does not
            // produce an AST node for itself. We do this by just passing its child back instead
            // of constructing something.
            return fn ($x) => $x;
            // If we did want a node for it, we would do that:
            // return fn ($x) => new ParenthesesNode($x);
        }
        return null;
    }
}

final class Dialect implements IDialect
{
    function acceptPrefixOrCircumfixOp($token, IParser $parser)
    {
        static $ops;
        $ops ??= [
            '(' => new ParenthesesAcceptor(),
            // ...
        ];
        return $ops[$token] ?? null;
    }

    // ...
}
```

```php
/** @implements IBinaryOpAcceptor<string, IMyAst> */
final class IndexAcceptor implements IBinaryOpAcceptor
{
    function getLeftPrecedence():  int { return 100; }
    function getRightPrecedence(): int { return 110; }
    // Remember, right precedence of postfix operators should be maximal in your grammar,
    // and that includes postcircumfix operators as well.

    /**
     * @param string $token
     * @return ?callable(IMyAst, IMyAst): IMyAst
     */
    function accept($token): ?callable
    {
        return $token === ']' ? fn ($lhs, $rhs) => new IndexNode($lhs, $rhs) : null;
    }
}

final class Dialect implements IDialect
{
    function acceptPostfixOrInfixOp($token, IParser $parser)
    {
        static $ops;
        $ops ??= [
            '[' => new IndexAcceptor(),
            // ...
        ];
        return $ops[$token] ?? null;
    }

    // ...
}
```

_Juxtaposition_ operator is special: it does not have textual representation. It is applied when two
terms are written next to each other, with no operators in between, as in `a b`. Aside from that, it
behaves like a normal binary operator. If you would like to have it in your dialect, return it
from `getJuxtapositionOp` method. If you would not, return `null` instead.

```php
/** @implements IBinaryOpFactory<IMyAst> */
final class ConcatFactory implements IBinaryOpFactory
{
    function getLeftPrecedence():  int { return 70; }
    function getRightPrecedence(): int { return 80; }

    /**
     * @param IMyAst $lhs
     * @param IMyAst $rhs
     * @return IMyAst
     */
    function create($lhs, $rhs): IMyAst
    {
        return new ConcatNode($lhs, $rhs);
    }
}

final class Dialect implements IDialect
{
    function getJuxtapositionOp(IParser $parser)
    {
        static $op;
        $op ??= new ConcatFactory();
        return $op;
    }

    // ...
}
```


### Tokens

They can be anything at all. Up until now, we used strings for simplicity. However, Formula Tools
make no assumptions about the type of a token. They just pass it to your `accept*` methods and ask
you if it is the token we are looking for. So you can encode tokens as strings, integral IDs,
classes, or a mix of them.

It is also worth noting that `FormulaParser::parse` takes an `iterable<Token>`, the most generic
possible type, and does only a single pass over it. That means it works with anything that can be
iterated through, including non-rewindable iterators. You can write your lexer as a generator!


### Validation

You can use Formula Tools to just validate input, without constructing an AST:

```php
final class PlusFactory implements IBinaryOpFactory
{
    // function getLeftPrecedence()...

    function create($lhs, $rhs) { }
}
```

If your `create` methods return nothing (`null`, to be precise), you’ll get literally nothing from
`FormulaParser::parse`. Nothing — or an exception.


### Switching dialects during parsing

Just call `$parser->setDialect`, whenever you wish. New syntactical scope will be respected
immediately. Yes, it’s that simple.

**Hint:** Depending on what language you are developing, you might want to keep a _stack_
of dialects somewhere.

Alternatively, since your dialect is a regular PHP object, it can have state and return different
results depending on it.


### What is a term?

Formula Tools consider a term to be anything that cannot be an operator in the given context. It is
the most flexible approach since it allows _you_ to decide whether you want to accept something.
E.g., with no additional validation performed, `a + )` would be successfully parsed as
`new PlusNode(new TermNode('a'), new TermNode(')'))`. If you would like to reject it, go ahead
and do it:

```php
final class Dialect implements IDialect
{
    // ...

    function parseTerm($token, IParser $parser)
    {
        // Check for `acceptPrefixOrCircumfixOp` is unnecessary since it is performed by Formula
        // Tools. We would not get here if `token` was a prefix operator.
        if ($this->acceptPostfixOrInfixOp($token) !== null || $token === ')') {
            throw new SyntaxException("Unexpected `$token`", $parser->getPosition());
        }
        return new TermNode($token);
    }
}
```

On the other hand, this allows defining `x` to be cross-product operator and having `x x x` mean
“multiply the `x` variable by itself.”


### More examples

```php
final class Dialect implements IDialect
{
    function acceptPrefixOrCircumfixOp($token, IParser $parser)
    {
        static $ops;
        $ops ??= [
            '(' => new ParenthesesAcceptor(),
        ];
        return $ops[$token] ?? null;
    }

    function acceptPostfixOrInfixOp($token, IParser $parser)
    {
        static $ops;
        $ops ??= [
            ','  => new BinaryOpFactory(0,  10, CommaNode::class),
            '=>' => new BinaryOpFactory(30, 20, ArrowNode::class), // Right-associative.
            '+'  => new BinaryOpFactory(40, 50, PlusNode::class),
            '('  => new PostParenthesesAcceptor(), // Yes, '(' is both circumfix and postcircumfix.
        ];
        return $ops[$token] ?? null;
    }

    function parseTerm($token, IParser $parser)
    {
        return new TermNode($token);
    }

    function getJuxtapositionOp(IParser $parser) { }
}

$parser = new FormulaParser(new Dialect());
print_r($parser->parse(explode(' ', '( ( a , b ) => a + b ) ( 2 , 3 )')));
print_r($parser->parse(explode(' ', '( a => b => a + b ) ( 2 ) ( 3 )')));
```

```php
final class Dialect implements IDialect
{
    function acceptPostfixOrInfixOp($token, IParser $parser)
    {
        static $ops;
        $ops ??= [
            ',' => new BinaryOpFactory(0,  10, CommaNode::class),
            ':' => new BinaryOpFactory(20, 20, ColonNode::class),
        ];
        return $ops[$token] ?? null;
    }

    function acceptPrefixOrCircumfixOp($token, IParser $parser)
    {
        static $ops;
        $ops ??= [
            '{' => new BraceAcceptor(),
        ];
        return $ops[$token] ?? null;
    }

    function parseTerm($token, IParser $parser)
    {
        return new TermNode($token);
    }

    function getJuxtapositionOp(IParser $parser) { }
}

$parser = new FormulaParser(new Dialect());
print_r($parser->parse(explode(' ', '{ "Voila" : "now" , "we" : "have" , "JSON" : "!" }')));
```

```php
final class TagAcceptor implements IUnaryOpAcceptor
{
    private string $endTag;

    function __construct(private string $tag)
    {
        $this->endTag = "</$tag>";
    }

    function accept($token): ?callable
    {
        return $token === $this->endTag ? fn ($x) => ["<$this->tag>", $x] : null;
    }
}

final class Dialect implements IDialect
{
    function acceptPrefixOrCircumfixOp($token, IParser $parser)
    {
        if (preg_match('/^<(\w+)>$/DX', $token, $m)) {
            return new TagAcceptor($m[1]); // A dynamically created circumfix operator.
        }
    }

    function getJuxtapositionOp(IParser $parser)
    {
        static $op;
        $op ??= new class implements IBinaryOpFactory {
            function getLeftPrecedence():  int { return 1; }
            function getRightPrecedence(): int { return 0; }
            function create($lhs, $rhs) { return [$lhs, $rhs]; }
        };
        return $op;
    }

    function parseTerm($token, IParser $parser)
    {
        return $token;
    }

    function acceptPostfixOrInfixOp($token, IParser $parser) { }
}

$parser = new FormulaParser(new Dialect());
print_r($parser->parse(explode(' ',
'<html> <body> <h1> Hello from HTML! </h1> <div> It <div> works. </div> </div> </body> </html>'
)));
```


### Limitations

As has been demonstrated, this library is able to construct parsers for quite many useful languages.
However, it does have its limits. Some of them might be lifted in the future.

* Ternary operators (`a ? b : c`) are not supported. You can emulate them with a pair of binary
  operators.
* Precircumfix operators (`λ a . a + 1`) are not supported. Again, emulatable.
* Empty brackets (`()`) are not supported. You can handle them in the lexer and register `()`
  as a postfix operator instead, for example.
* Operator chaining is not supported. In some languages, `a < b < c` actually means
  `a < b && b < c`. In Formula Tools, it can only mean either `(a < b) < c` or `a < (b < c)` or be
  a syntax error.
* You cannot have the same operator as both infix (`a + b`) and postfix (`a+`) in a single dialect.
  If you are able to partition your grammar into two regions such that one has only infix `+` and
  the other — only postfix, then you can have them both in a single formula by switching dialects
  dynamically. But for arbitrary grammars this is not possible.
* Anything beyond simple formulae consisting of operators and operands is not supported. Use a right
  tool for your job.


## Unit testing

```sh
composer install
composer test-init
composer test
```


## Standards

PHP Formula Tools conform to the following standards:

* PSR-1 — [Basic coding standard](https://www.php-fig.org/psr/psr-1/)
* PSR-4 — [Autoloader](https://www.php-fig.org/psr/psr-4/)
* PSR-12 — [Extended coding style guide](https://www.php-fig.org/psr/psr-12/)


## License

PHP Formula Tools are licensed under the MIT License.
