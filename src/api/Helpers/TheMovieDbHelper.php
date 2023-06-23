<?php

require_once(dirname(__FILE__) . "/../vendor/autoload.php");

class TheMovieDbHelper
{
    private const AUTHORIZATION_HEADER = 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI5YmExZTNmOGE3YWI4ZDUxMTY0OWVlMzdkZDE3YTFhYSIsInN1YiI6IjY0OTE5NTFmYzNjODkxMDBlYjM0NTI4YiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.TeK26Y_TjKAjcBjDTMhJarBV4W7lWsbV1stEysoQXcE';
    public static function callSearchEndpoint($title, $type = 'movie')
    {
        $client = new \GuzzleHttp\Client();

        $title = urlencode($title);

        $response = $client->request('GET', "https://api.themoviedb.org/3/search/$type?query=$title&include_adult=false&language=en-US&page=1", [
            'headers' => [
                'Authorization' => self::AUTHORIZATION_HEADER,
                'accept' => 'application/json',
            ],
        ]);

        $content = json_decode($response->getBody()->getContents(), true);

        if (isset($content['results'][0]['popularity'])) {
            return [
                'popularity' => $content['results'][0]['popularity'],
                'poster' => "https://themoviedb.org/t/p/w440_and_h660_face" . $content['results'][0]['poster_path'],
                'vote_average' => $content['results'][0]['vote_average'],
                'vote_count' => $content['results'][0]['vote_count'],
            ];
        }

        return null;
    }

    public static function callStatisticsEndpoint($statisticType = 'top_rated', $showType = 'movie')
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', "https://api.themoviedb.org/3/$showType/$statisticType?language=en-US&page=1", [
            'headers' => [
                'Authorization' => self::AUTHORIZATION_HEADER,
                'accept' => 'application/json',
            ],
        ]);

        $content = json_decode($response->getBody()->getContents(), true);

        $list = [];

        if (isset($content['results'])) {
            foreach ($content['results'] as $item) {
                $itemData = [];
                if ($showType == 'movie') {
                    $itemData['title'] = $item['title'];
                } else {
                    $itemData['title'] = $item['name'];
                }
                $itemData['popularity'] = $item['popularity'];
                $itemData['poster'] = "https://themoviedb.org/t/p/w440_and_h660_face" . $item['poster_path'];
                $itemData['vote_average'] = $item['vote_average'];
                $itemData['vote_count'] = $item['vote_count'];

                $list[] = $itemData;
            }

            return $list;
        }

        return null;
    }

    public static function callUpcomingMoviesEndpoint()
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://api.themoviedb.org/3/movie/upcoming?language=en-US&page=1', [
            'headers' => [
                'Authorization' => self::AUTHORIZATION_HEADER,
                'accept' => 'application/json',
            ],
        ]);

        $content = json_decode($response->getBody()->getContents(), true);

        $list = [];

        if (isset($content['results'])) {
            foreach ($content['results'] as $item) {
                $list[] = [
                    'title' => $item['title'],
                    'popularity' => $item['popularity'],
                    'poster' => "https://themoviedb.org/t/p/w440_and_h660_face" . $item['poster_path'],
                    'vote_average' => $item['vote_average'],
                    'vote_count' => $item['vote_count'],
                ];
            }

            return $list;
        }

        return null;
    }
    public static function callAiringTodayTvShowsEndpoint()
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://api.themoviedb.org/3/tv/airing_today?language=en-US&page=1', [
            'headers' => [
                'Authorization' => self::AUTHORIZATION_HEADER,
                'accept' => 'application/json',
            ],
        ]);

        $content = json_decode($response->getBody()->getContents(), true);

        $list = [];

        if (isset($content['results'])) {
            foreach ($content['results'] as $item) {
                $list[] = [
                    'title' => $item['name'],
                    'popularity' => $item['popularity'],
                    'poster' => "https://themoviedb.org/t/p/w440_and_h660_face" . $item['poster_path'],
                    'vote_average' => $item['vote_average'],
                    'vote_count' => $item['vote_count'],
                ];
            }

            return $list;
        }

        return null;
    }
}