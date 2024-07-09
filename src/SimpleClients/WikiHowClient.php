<?php
// WikiHowService.php
namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;

use simplehtmldom\HtmlWeb;

class WikiHowClient extends Service
{
    private $baseUrl = 'https://www.wikihow.com';

    public function search(string $query): array
    {
        $url = $this->baseUrl . '/Special:Search?search=' . urlencode($query);
        // $url = $this->baseUrl . 'wikiHowTo?search=' . urlencode($query);

        $html = (new HtmlWeb())->load($url);

        $results = [];
        foreach ($html->find('.searchresult') as $searchResult) {
            $title = $searchResult->find('.result_title', 0)->plaintext;
            $description = $searchResult->find('.result_description', 0)->plaintext;
            $rating = $this->extractRating($searchResult);

            $articleUrl = $searchResult->find('.result_title a', 0)->href;
            $steps = $this->getSteps($this->baseUrl . $articleUrl);

            $results[] = [
                'title' => $title,
                'description' => $description,
                'rating' => $rating,
                'url' => $articleUrl, // Add the full URL to the result
                'steps' => $steps
            ];
        }

        return $results;
    }

    private function extractRating($searchResult): float
    {
        $ratingElement = $searchResult->find('.search_rating_bar span', 0);
        if ($ratingElement) {
            $style = $ratingElement->style;
            preg_match('/width:(\d+(\.\d+)?)%/', $style, $matches);
            if (isset($matches[1])) {
                return (float)$matches[1] / 20; // Convert percentage to a rating out of 5
            }
        }
        return 0;
    }

    private function getSteps(string $url): array
    {
        $html = (new HtmlWeb())->load($this->baseUrl . '/' . $url);
        $stepsList = $html->find('.steps_list_2', 0);
        if (!$stepsList) {
            return [];
        }

        $steps = [];
        foreach ($stepsList->find('li') as $step) {
            $titleElement = $step->find('.step_num', 0);
            $descriptionElement = $step->find('.step', 0);

            if ($titleElement && $descriptionElement) {
                $steps[] = [
                    'title' => $titleElement->plaintext,
                    'description' => $descriptionElement->plaintext
                ];
            }
        }

        return $steps;
    }

    private function getStepsAsString(string $url): string
    {
        $steps = $this->getSteps($url);
        if (empty($steps)) {
            return '';
        }

        $stepsAsString = '';
        foreach ($steps as $step) {
            $stepsAsString .= "{$step['title']}:\n{$step['description']}\n\n";
        }

        return rtrim($stepsAsString);
    }

}
