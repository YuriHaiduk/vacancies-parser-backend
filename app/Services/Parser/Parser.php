<?php

namespace App\Services\Parser;
use App\Contracts\Parser\ParserInterface;

class Parser
{
    protected $parsers = [];

    public function __construct(ParserInterface ...$parsers)
    {
        foreach ($parsers as $parser) {
            $this->parsers[get_class($parser)] = $parser;
        }
    }

    public function parseAllSources(): void
    {
        foreach ($this->parsers as $parser) {
            $parser->parse();
        }
    }
}
