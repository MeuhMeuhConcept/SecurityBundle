<?php

namespace AppBundle\Doctrine\DQL\PostgreSQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * CalendarDateToIntFunction ::=
 *     "CAST" "(" StringPrimary ", " StringPrimary ")".
 */
class Cast extends FunctionNode
{
    protected $field;
    protected $type;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->field = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->type = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('CAST(%s AS %s)', $this->field->dispatch($sqlWalker), $this->type->dispatch($sqlWalker));
    }
}
