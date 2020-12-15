<?php

namespace Mmc\Security\Doctrine\DQL\Postgre;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * CalendarDateToIntFunction ::=
 *     "CAST" "(" StringPrimary ", " StringPrimary ")".
 */
class Cast extends FunctionNode
{
    protected $expression;

    protected $type;

    /**
     * @var array
     */
    protected $supportedTypes = [
        'char',
        'string',
        'text',
        'date',
        'datetime',
        'time',
        'int',
        'integer',
        'decimal',
        'json',
        'bool',
        'boolean',
        'binary',
    ];

    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->expression = $parser->ArithmeticExpression();

        $parser->match(Lexer::T_AS);

        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $type = $lexer->token['value'];

        if ($lexer->isNextToken(Lexer::T_OPEN_PARENTHESIS)) {
            $parser->match(Lexer::T_OPEN_PARENTHESIS);
            /** @var Literal $parameter */
            $parameter = $parser->Literal();
            $parameters = [
                $parameter->value,
            ];
            if ($lexer->isNextToken(Lexer::T_COMMA)) {
                while ($lexer->isNextToken(Lexer::T_COMMA)) {
                    $parser->match(Lexer::T_COMMA);
                    $parameter = $parser->Literal();
                    $parameters[] = $parameter->value;
                }
            }
            $parser->match(Lexer::T_CLOSE_PARENTHESIS);
            $type .= '('.implode(', ', $parameters).')';
        }

        if (!$this->checkType($type)) {
            $parser->syntaxError(
                sprintf(
                    'Type unsupported. Supported types are: "%s"',
                    implode(', ', $this->supportedTypes)
                ),
                $lexer->token
            );
        }

        $this->type = $type;

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        /** @var Node $value */
        $value = $this->expression;
        $type = $this->type;

        $type = strtolower($type);
        if ('datetime' === $type) {
            $timestampFunction = new Timestamp(
                [SimpleFunction::PARAMETER_KEY => $value]
            );

            return $timestampFunction->getSql($sqlWalker);
        }

        if ('json' === $type && !$sqlWalker->getConnection()->getDatabasePlatform()->hasNativeJsonType()) {
            $type = 'text';
        }

        if ('bool' === $type) {
            $type = 'boolean';
        }

        if ('binary' === $type) {
            $type = 'bytea';
        }

        /*
         * The notations varchar(n) and char(n) are aliases for character varying(n) and character(n), respectively.
         * character without length specifier is equivalent to character(1). If character varying is used
         * without length specifier, the type accepts strings of any size. The latter is a PostgreSQL extension.
         * http://www.postgresql.org/docs/9.2/static/datatype-character.html
         */
        if ('string' === $type) {
            $type = 'varchar';
        }

        return 'CAST('.$this->getExpressionValue($value, $sqlWalker).' AS '.$type.')';
    }

    /**
     * Check that given type is supported.
     *
     * @param string $type
     *
     * @return bool
     */
    protected function checkType($type)
    {
        $type = strtolower(trim($type));
        foreach ($this->supportedTypes as $supportedType) {
            if (0 === strpos($type, $supportedType)) {
                return true;
            }
        }

        return false;
    }

    protected function getExpressionValue($expression, SqlWalker $sqlWalker)
    {
        if ($expression instanceof Node) {
            $expression = $expression->dispatch($sqlWalker);
        }

        return $expression;
    }
}
