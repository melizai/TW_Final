<?php
include("BaseController.php");
include_once(dirname(__FILE__) . '/../Helpers/TheMovieDbHelper.php');

class NewsController extends BaseController
{
    public function getAll(){
        $this->returnJsonResponse([
            'upcoming_movies' => TheMovieDbHelper::callUpcomingMoviesEndpoint(),
            'airing_today_tv_shows' => TheMovieDbHelper::callAiringTodayTvShowsEndpoint(),
        ], 200);
    }

}