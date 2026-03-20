<?php

/*
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @link      http://phpdoc.org
 *
 */
declare (strict_types=1);
namespace Php_Documentor\Reflection\Doc_Block\Tags\Factory;

use Php_Documentor\Reflection\Doc_Block\Tag;
use Php_Documentor\Reflection\Doc_Block\Tags\Invalid_Tag;
use Php_Documentor\Reflection\Types\Context as TypeContext;
use Php_Stan\Php_Doc_Parser\Lexer\Lexer;
use Php_Stan\Php_Doc_Parser\Parser\Const_Expr_Parser;
use Php_Stan\Php_Doc_Parser\Parser\Parser_Exception;
use Php_Stan\Php_Doc_Parser\Parser\Php_Doc_Parser;
use Php_Stan\Php_Doc_Parser\Parser\Token_Iterator;
use Php_Stan\Php_Doc_Parser\Parser\Type_Parser;
use Php_Stan\Php_Doc_Parser\Parser_Config;
use function property_exists;
use function rtrim;
use RuntimeException;
use function str_replace;
use function trim;
/**
 * Factory class creating tags using phpstan's parser
 *
 * This class uses {@see PHPStanFactory} implementations to create tags
 * from the ast of the phpstan docblock parser.
 *
 * @internal This class is not part of the BC promise of this library.
 */
class Abstract_Php_Stan_Factory implements Factory
{
    private Php_Doc_Parser $parser;
    private Lexer $lexer;
    /** @var PHPStanFactory[] */
    private array $factories;
    public function __construct(Php_Stan_Factory ...$factories)
    {
        $config = new Parser_Config(['indexes' => true, 'lines' => true]);
        $this->lexer = new Lexer($config);
        $const_parser = new Const_Expr_Parser($config);
        $this->parser = new Php_Doc_Parser($config, new Type_Parser($config, $const_parser), $const_parser);
        $this->factories = $factories;
    }
    public function create(string $tag_line, ?Type_Context $context = null): Tag
    {
        try {
            $tokens = $this->tokenize_line($tag_line);
            $ast = $this->parser->parse_tag($tokens);
            if (property_exists($ast->value, 'description') === true) {
                $ast->value->set_attribute('description', rtrim($ast->value->description . $tokens->join_until(Lexer::TOKEN_END), "\n"));
            }
        } catch (Parser_Exception $e) {
            return Invalid_Tag::create($tag_line, '')->with_error($e);
        }
        if ($context === null) {
            $context = new Type_Context('');
        }
        try {
            foreach ($this->factories as $factory) {
                if ($factory->supports($ast, $context)) {
                    return $factory->create($ast, $context);
                }
            }
        } catch (RuntimeException $e) {
            return Invalid_Tag::create((string) $ast->value, 'method')->with_error($e);
        } catch (Parser_Exception $e) {
            return Invalid_Tag::create((string) $ast->value, $ast->name)->with_error($e);
        }
        return Invalid_Tag::create((string) $ast->value, $ast->name);
    }
    /**
     * Solve the issue with the lexer not tokenizing the line correctly
     *
     * This method is a workaround for the lexer that includes newline tokens with spaces. For
     * phpstan this isn't an issue, as it doesn't do a lot of things with the indentation of descriptions.
     * But for us is important to keep the indentation of the descriptions, so we need to fix the lexer output.
     */
    private function tokenize_line(string $tag_line): Token_Iterator
    {
        // Prefix continuation lines with "* ", which is consumed by the phpstan parser as TOKEN_PHPDOC_EOL.
        $tag_line = str_replace("\n", "\n* ", $tag_line);
        $tokens = $this->lexer->tokenize($tag_line . "\n");
        $fixed = [];
        foreach ($tokens as $token) {
            if ($token[Lexer::TYPE_OFFSET] === Lexer::TOKEN_PHPDOC_EOL) {
                // Strip "* " prefix (and other horizontal whitespace) again so it doesn't and up in the
                // description when we joinUntil() in create().
                $fixed[] = [Lexer::VALUE_OFFSET => trim($token[Lexer::VALUE_OFFSET], "* \t"), Lexer::TYPE_OFFSET => $token[Lexer::TYPE_OFFSET], Lexer::LINE_OFFSET => $token[Lexer::LINE_OFFSET] ?? 0];
                continue;
            }
            $fixed[] = $token;
        }
        return new Token_Iterator($fixed);
    }
}