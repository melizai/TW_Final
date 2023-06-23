<?php

include("BaseController.php");
include_once(dirname(__FILE__) . '/../Helpers/TheMovieDbHelper.php');

class StatisticsController extends BaseController
{
    public function getTopRated($params, $urlParams)
    {
        $data = [
            'movies' => TheMovieDbHelper::callStatisticsEndpoint(),
            'tv_shows' => TheMovieDbHelper::callStatisticsEndpoint('top_rated', 'tv'),
        ];

        if (isset($urlParams['export']) && isset($urlParams['type'])) {
            $this->exportToFile($urlParams['export'], $urlParams['type'], $data);

            return;
        }

        $this->returnJsonResponse($data, 200);
    }

    public function getPopular($params, $urlParams)
    {
        $data = [
            'movies' => TheMovieDbHelper::callStatisticsEndpoint('popular'),
            'tv_shows' => TheMovieDbHelper::callStatisticsEndpoint('popular', 'tv'),
        ];

        if (isset($urlParams['export']) && isset($urlParams['type'])) {
            $this->exportToFile($urlParams['export'], $urlParams['type'], $data);

            return;
        }

        $this->returnJsonResponse($data, 200);
    }

    private function exportToFile($export, $type, $data)
    {
        if ($type == 'movies' || $type == 'tv_shows') {
            switch ($export) {
                case 'cvs':
                {
                    $csvData = implode(',', $data[$type]);
                    $csvFile = 'export.csv';
                    file_put_contents($csvFile, $csvData);

                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="export.csv"');

                    readfile($csvFile);

                    unset($csvFile);
                }
                case "webp":
                {
                    //to be implemented
                }
                case "svg":
                {
                    //to be implemented
                }
            }
        }
    }

}