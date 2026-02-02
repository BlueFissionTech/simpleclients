<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\WikiHowClient;
use BlueFission\SimpleClients\Tests\Support\DomStub;
use BlueFission\SimpleClients\Tests\Support\ElementStub;
use BlueFission\SimpleClients\Tests\Support\HtmlWebStub;
use PHPUnit\Framework\TestCase;

class WikiHowClientTest extends TestCase
{
    public function testSearchIncludesStepsAndRating(): void
    {
        $title = new ElementStub([], 'Make Tea');
        $description = new ElementStub([], 'A simple guide');
        $rating = new ElementStub([], '', '', 'width:80%');
        $link = new ElementStub([], '', '/Make-Tea');

        $searchResult = new ElementStub([
            '.result_title' => $title,
            '.result_description' => $description,
            '.search_rating_bar span' => $rating,
            '.result_title a' => $link,
        ]);

        $searchDom = new DomStub([
            '.searchresult' => [$searchResult],
        ]);

        $stepNum = new ElementStub([], '1');
        $stepText = new ElementStub([], 'Boil water');
        $stepLi = new ElementStub([
            '.step_num' => $stepNum,
            '.step' => $stepText,
        ]);

        $stepsList = new ElementStub([
            'li' => [$stepLi],
        ]);

        $stepsDom = new DomStub([
            '.steps_list_2' => $stepsList,
        ]);

        $htmlWeb = new HtmlWebStub([
            'https://www.wikihow.com/Special:Search?search=tea' => $searchDom,
            'https://www.wikihow.com/Make-Tea' => $stepsDom,
        ]);

        $client = new WikiHowClient($htmlWeb);
        $results = $client->search('tea');

        $this->assertCount(1, $results);
        $this->assertSame('Make Tea', $results[0]['title']);
        $this->assertSame(4.0, $results[0]['rating']);
        $this->assertSame('Boil water', $results[0]['steps'][0]['description']);
    }
}
