<?php

namespace App\Contracts\Parser;

use DiDom\Document;

interface ParserInterface
{

    public function parse(): void;

}
