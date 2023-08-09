<?php

namespace App\Contracts\Parser;

use DiDom\Document;

interface ParserInterface
{
    public function getHtmlPage(string $startUrl): string;

    public function getNumberOfPages(Document $document): int;

    public function getVacancies(Document $document): void;

    public function getVacancyData($jobLink): array;

    public function saveVacancyToDatabase($data): void;

    public function parse(): void;

}
