<?php


include("BaseController.php");
include_once(dirname(__FILE__) . '/../Helpers/TheMovieDbHelper.php');

class ShowsController extends BaseController
{
    public function getAll($params, $queryParams)
    {
        $availableSortBy = ['name' => 'shows.title', 'year' => 'shows.release_year'];
        $availableSortDir = ['ASC', 'DESC'];
        $availableTypes = ['MOVIE', 'TV SHOW'];
        $limit = 20;
        $offset = 0;
        if (isset($queryParams['page'])) {
            $offset = $limit * (int)$queryParams['page'] - 20;
        }

        $sort_by = 'shows.id';
        $sort_dir = 'ASC';

        if (!empty($queryParams['sort_by']) && isset($availableSortBy[strtolower($queryParams['sort_by'])])) {
            $sort_by = $availableSortBy[strtolower($queryParams['sort_by'])];
        }
        if (!empty($queryParams['sort_dir']) && in_array(strtoupper($queryParams['sort_dir']), $availableSortDir)) {
            $sort_dir = strtoupper($queryParams['sort_dir']);
        }

        $whereClause = '';
        if (!empty($queryParams['genre']) && !empty($queryParams['rating'])) {
            $whereClause = 'WHERE UPPER(genres.name) = UPPER("' . $queryParams['genre'] . '") AND UPPER(shows.rating) = UPPER("' . $queryParams['rating'] . '")';
        } elseif (!empty($queryParams['genre']) && empty($queryParams['rating'])) {
            $whereClause = 'WHERE UPPER(genres.name) = UPPER("' . $queryParams['genre'] . '")';
        } elseif (empty($queryParams['genre']) && !empty($queryParams['rating'])) {
            $whereClause = 'WHERE UPPER(shows.rating) = UPPER("' . $queryParams['rating'] . '")';
        }
        if (!empty($queryParams['type']) && in_array(strtoupper($queryParams['type']), $availableTypes)) {
            if ($whereClause == '') {
                $whereClause = 'WHERE UPPER(shows.type) = UPPER("' . $queryParams['type'] . '")';
            } else {
                $whereClause .= ' AND UPPER(shows.type) = UPPER("' . $queryParams['type'] . '")';
            }
        }

        if (str_contains($whereClause, 'shows.rating') || str_contains($whereClause, 'genres.name')) {
            $whereClause = "INNER JOIN show_genres ON shows.id = show_genres.show_id
                            INNER JOIN genres ON show_genres.genre_id = genres.id " . $whereClause;
        }

        $selectAll = "SELECT shows.* ";
        $countSelect = "SELECT count(*) as number ";
        $sql = "
        FROM shows
        $whereClause
        ";
        $latterSql = "
        GROUP BY shows.id
        ORDER BY $sort_by $sort_dir
        LIMIT $limit
        OFFSET $offset";

        $shows = $this->db->executeRawQuery($selectAll . $sql . $latterSql);

        $count = $this->db->executeRawQuery($countSelect . $sql, true);

        if (empty($shows)) {
            $this->returnJsonResponse(['errors' => 'There are no shows available!'], 404);
            return;
        } else {
            $this->returnJsonResponse([
                'data' => $shows,
                'count' => $count['number'],
                'first_page' => 1,
                'last_page' => (int)((int)$count['number'] / $limit) + 1,
            ], 200);
        }
    }

    public function search($params, $queryParams)
    {
        $whereClause = '';
        if (isset($queryParams['q'])) {
            $whereClause = 'WHERE UPPER(shows.title) LIKE "%' . strtoupper($queryParams['q']) . '%" ';
        }

        $sql = " SELECT * FROM shows 
        $whereClause
        ORDER BY shows.title ASC
        LIMIT 20";

        $shows = $this->db->executeRawQuery($sql);

        if (empty($shows)) {
            $this->returnJsonResponse(['errors' => 'There are no shows available!'], 404);
            return;
        } else {
            $this->returnJsonResponse([
                'data' => $shows,
            ], 200);
        }
    }

    public function import()
    {
        $user = $this->checkAuthentication();
        if ($user === null) {
            $this->returnJsonResponse(['errors' => 'Wrong access token!'], 401);
            return;
        }
        if ($user['type'] != 'admin') {
            $this->returnJsonResponse(['errors' => 'Only admins can run this request!'], 401);
            return;
        }
        // Check if the form is submitted and a file is uploaded
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $tmpFilePath = $_FILES['file']['tmp_name'];

            // Check if the uploaded file is a CSV file
            $fileType = mime_content_type($tmpFilePath);
            if ($fileType === 'text/csv' || $fileType === 'application/vnd.ms-excel') {
                $csvData = array();
                if (($handle = fopen($tmpFilePath, 'r')) !== false) {
                    $headers = fgetcsv($handle, 1000, ','); // Get the headers as the first row
                    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                        $rowData = array_combine($headers, $data); // Combine headers with row data
                        $csvData[] = $rowData;
                    }
                    fclose($handle);

                    foreach ($csvData as $show) {
                        $this->addNewShow(
                            [
                                'type' => $show['type'],
                                'title' => $show['title'],
                                'director' => $show['director'],
                                'cast' => $show['cast'],
                                'country' => explode(',', $show['country'])[0],
                                'release_year' => $show['release_year'],
                                'rating' => $show['rating'],
                                'duration' => $show['duration'],
                                'genres' => $show['listed_in'],
                                'description' => $show['description'],
                            ]
                        );
                    }

                } else {
                    $this->returnJsonResponse(['errors' => 'Error opening the CSV file.'], 400);
                    return;
                }
            } else {
                $this->returnJsonResponse(['errors' => 'Invalid file format. Please upload a CSV file.'], 400);
                return;

            }
        } else {
            $this->returnJsonResponse(['errors' => 'No file uploaded or an error occurred during file upload.'], 400);
            return;

        }

        $this->returnJsonResponse(['message' => 'File uploaded successfully!'], 200);
    }

    public function getById($params)
    {
        $show = $this->db->select('shows', '*', ' id = "' . $params['id'] . '"', [], true);
        if (empty($show)) {
            $this->returnJsonResponse(['errors' => 'Show with this id does not exists in our db!'], 404);
            return;
        } else {
            $showId = $params['id'];
            $genres = $this->db->executeRawQuery("
                SELECT genres.name from show_genres
                INNER JOIN genres on genres.id = show_genres.genre_id
                INNER JOIN shows on show_genres.show_id = shows.id
                WHERE shows.id = '$showId';");
            $genreList = [];
            foreach ($genres as $genre) {
                $genreList[] = $genre['name'];
            }
            if ($show['type'] != 'Movie') {
                $dbMovieData = TheMovieDbHelper::callSearchEndpoint($show['title'], 'tv');
            } else {
                $dbMovieData = TheMovieDbHelper::callSearchEndpoint($show['title']);
            }

            $show['genres'] = $genreList;

            $this->returnJsonResponse(['data' => $show, 'external' => $dbMovieData], 200);
        }
    }

    protected function getRequestHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $headerName = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    private function addNewShow($showData)
    {
        $title = str_replace("'", "\'", $showData['title']);
        $director = str_replace("'", "\'", $showData['director']);
        $showExists = $this->db->select('shows', '*', " title = '$title' and director = '$director'");

        if (empty($showExists)) {

            $genres = explode(',', str_replace(', ', ',', $showData['genres']));

            $genreIds = [];

            foreach ($genres as $genre) {
                $genreIds[] = $this->addNewGenre($genre);
            }
            //delete the key from array "genre" pt ca nu exista coloana in db
            unset($showData['genres']);

            $showId = $this->db->insert('shows', $showData);
            foreach ($genreIds as $genreId) {
                $this->db->insert('show_genres', ['show_id' => $showId, 'genre_id' => $genreId]);
            }
        };
    }

    private function addNewGenre($genre)
    {
        $genreEntry = $this->db->select('genres', '*', 'name = "' . $genre . '"', [], true);

        if ($genreEntry) {
            return $genreEntry['id'];
        }

        return $this->db->insert('genres', ['name' => $genre]);
    }

    public function add()
    {
        $user = $this->checkAuthentication();
        if ($user === null) {
            $this->returnJsonResponse(['errors' => 'Wrong access token!'], 401);
            return;
        }
        if ($user['type'] != 'admin') {
            $this->returnJsonResponse(['errors' => 'Only admins can run this request!'], 401);
            return;
        }

        $body = $this->getRequestBody();

        if ($body === NULL) {
            $this->returnJsonResponse(['errors' => 'Corrupt JSON !!'], 400);
            return;
        }

        if (!isset($body['type']) || !isset($body['title']) || !isset($body['director']) || !isset($body['cast']) ||
            !isset($body['country']) || !isset($body['release_year']) || !isset($body['rating']) ||
            !isset($body['duration']) || !isset($body['description'])) {
            $this->returnJsonResponse(['errors' => 'Missing field !!'], 400);
            return;
        }

        if($body['type'] != "Movie" && $body['type'] != "TV Show"){
            $this->returnJsonResponse(['errors' => 'Type should be Movie/TV Show !!'], 400);
            return;
        }

        $showData = [
            'type' => $body['type'],
            'title' => $body['title'],
            'director' => $body['director'],
            'cast' => $body['cast'],
            'country' => $body['country'],
            'release_year' => $body['release_year'],
            'rating' => $body['rating'],
            'duration' => $body['duration'],
            'description' => $body['description'],
        ];

        $showExists = $this->db->select('shows', '*', ' title = "' . $body['title'] . '" AND director = "' . $body['director'] . '" AND type = "' . $body['type'] .'"');

        if (empty($showExists)) {
            $result = $this->db->insert('shows', $showData);
        } else {
            $this->returnJsonResponse(['errors' => 'Show already exists in our db'], 400);

            return;
        }

        $this->returnJsonResponse(['message' => 'Show created!'], 200);
    }


    public function delete($params)
    {
        $user = $this->checkAuthentication();
        if ($user === null) {
            $this->returnJsonResponse(['errors' => 'Wrong access token!'], 401);
            return;
        }

        if ($user['type'] != 'admin') {
            $this->returnJsonResponse(['errors' => 'Only admins can run this request!'], 401);
            return;
        }

        $show = $this->db->select('shows', '*', ' id = "' . $params['id'] . '"', [], true);
        if (empty($show)) {
            $this->returnJsonResponse(['errors' => 'This show does not exist!'], 401);
            return;
        }

        $this->db->delete('shows', ' id = "' . $params['id'] . '"');
        $this->returnJsonResponse(['message' => 'This show has been deleted!'], 200);
    }

    public function update($params)
    {
        $user = $this->checkAuthentication();
        if ($user === null) {
            $this->returnJsonResponse(['errors' => 'Wrong access token!'], 401);
            return;
        }
        if ($user['type'] != 'admin') {
            $this->returnJsonResponse(['errors' => 'Only admins can run this request!'], 401);
            return;
        }

        $show = $this->db->select('shows', '*', " id = '" . $params['id'] . "'", [], true);

        if (empty($show)) {
            $this->returnJsonResponse(['errors' => 'This show doesn\'t exist in the database!'], 404);
        }

        $body = $this->getRequestBody();

        if (isset($body['title'])) {
            $show['title'] = $body['title'];
        }
        if (isset($body['director'])) {
            $show['director'] = $body['director'];
        }
        if (isset($body['cast'])) {
            $show['cast'] = $body['cast'];
        }
        if (isset($body['country'])) {
            $show['country'] = $body['country'];
        }
        if (isset($body['country'])) {
            $show['country'] = $body['country'];
        }
        if (isset($body['release_year'])) {
            $show['release_year'] = $body['release_year'];
        }
        if (isset($body['description'])) {
            $show['description'] = $body['description'];
        }

        $result = $this->db->update('shows', $show, " id = '" . $params['id'] . "'");

        if ($result > 0) {
            $this->returnJsonResponse(['message' => 'Show updated successfully!'], 200);
        } else {
            $this->returnJsonResponse(['errors' => 'The show wasn\'t be updated!'], 400);
        }
    }

    public function getFilters()
    {
        $genres = $this->db->select('genres', 'DISTINCT(name)');
        $types = $this->db->select('shows', 'DISTINCT(type)');
        $ratings = $this->db->select('shows', 'DISTINCT(rating)');

        $genresResult = [];
        $typesResult = [];
        $ratingsResult = [];
        foreach ($genres as $genre) {
            $genresResult[] = $genre['name'];
        }
        foreach ($types as $type) {
            $typesResult[] = $type['type'];
        }
        foreach ($ratings as $rating) {
            $ratingsResult[] = $rating['rating'];
        }


        $this->returnJsonResponse(['data' => ['genres' => $genresResult, 'types' => $typesResult, 'ratings' => $ratingsResult]], 200);
    }
}