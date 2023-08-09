<?php

namespace App\Services\Parser;

use App\Contracts\Parser\ParserInterface;
use App\Models\Vacancy;
use DiDom\Document;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class WorkUaParser implements ParserInterface
{

    public function getHtmlPage(string $startUrl): string
    {
        $userAgents = config('user_agents');
        $userAgent = $userAgents[array_rand($userAgents)];

        $response = Http::withHeaders([
            'User-Agent' => $userAgent,
            'Referer' => $startUrl,
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->get($startUrl);
        return $response->body();
    }

    public function getNumberOfPages(Document $document): int
    {
        $paginationItems = $document->find('ul.pagination li');

        if (isset($paginationItems[5])) {
            $sixthElement = $paginationItems[5];
            $textContent = trim($sixthElement->text());

            if (is_numeric($textContent)) {
                return (int)$textContent;
            }
        }

        return 0;
    }

    public function getVacancies(Document $document): void
    {
        $vacancies = $document->find('#pjax-job-list h2 a');

        foreach ($vacancies as $vacancy) {

            $jobLink = $vacancy->getAttribute('href');

            $data = $this->getVacancyData($jobLink);

            $this->saveVacancyToDatabase($data);

            dump($jobLink);

            sleep(rand(1, 3));
        }
    }

    public function getVacancyData($jobLink): array
    {

        $domainName = 'https://www.work.ua';
        $vacancyDocument = new Document($domainName . $jobLink, true);

        $title = $vacancyDocument->find('h1#h1-name.add-top-sm')[0]->text();
        $type = trim($vacancyDocument->find('p.text-indent.add-top-sm')[2]->text());
        $description = $vacancyDocument->find('div#job-description');

        if ($description) {
            $content = '';
            foreach ($description[0]->find('p, ul') as $element) {
                $content .= $element->text() . "\n\n";
            }

        }

        $dataToHash = [
            'title' => $title,
            'type' => $type,
            'description' => $content,
        ];
        $vacancyHash = Hash::make(json_encode($dataToHash));

        return [
            'vacancy_hash' => $vacancyHash,
            'title' => $title,
            'type' => $type,
            'description' => $content,
        ];
    }

    public function saveVacancyToDatabase($data): void
    {
        Vacancy::create([
            'vacancy_hash' => $data['vacancy_hash'],
            'title' => $data['title'],
            'type' => $data['type'],
            'description' => $data['description'],
        ]);
    }

    public function parse(): void
    {
        $urls = config('parser.work_ua');

        foreach ($urls as $firstUrl) {
            dump('<==== ' . $firstUrl . ' ====>');
            $htmlPage = $this->getHtmlPage($firstUrl);

            $document = new Document();
            $document->loadHtml($htmlPage);

            $numPages = $this->getNumberOfPages($document);
            $startUrl = $firstUrl;
            $requestDelay = rand(1, 3);

            for ($i = 1; $i <= 3; $i++) {
                $url = ($i === 1) ? $startUrl : "{$startUrl}?page={$i}";

                $nextHtmlPage = $this->getHtmlPage($url);

                $nextDocument = new Document();
                $nextDocument->loadHtml($nextHtmlPage);

                $this->getVacancies($nextDocument);

                dump($i);

                if ($i < $numPages) {
                    sleep($requestDelay);
                }

            }
        }

        dump('<==== Done ====>');
    }

}
