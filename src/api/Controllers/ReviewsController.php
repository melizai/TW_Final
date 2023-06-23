<?php

include("BaseController.php");
class ReviewsController extends BaseController
{
    public function add(){  //merge
        $user = $this->checkAuthentication();
        if ($user === null) {
            $this->returnJsonResponse(['errors' => 'Wrong access token!'], 401);
            return;
        }

        $body = $this->getRequestBody();

        if ($body === NULL) {
            $this->returnJsonResponse(['errors' => 'Corrupt JSON !!'], 400);
        }

        if (!isset($body['text']) || !isset($body['stars']) || !isset($body['show_id'])) {
            $this->returnJsonResponse(['errors' => 'Missing field !!'], 400);
        }

        $reviewData = [
            'show_id' => $body['show_id'],
            'user_id' => $user['id'],
            'text' => $body['text'],
            'stars' => $body['stars'],
        ];

        $reviewExists = $this->db->select('reviews', '*', ' user_id = "' . $user['id'] . '" AND show_id = "' . $body['show_id'] . '"');

        if (empty($reviewExists)) {
            $result = $this->db->insert('reviews', $reviewData);
        } else {
            $this->returnJsonResponse(['errors' => 'Review already exists in our db'], 400);

            return;
        }

        $this->returnJsonResponse(['message' => 'Review created!'], 200);
    }

    public function getAll()
    {
        $reviews = $this->db->select('reviews', '*');
        if (empty($reviews)) {
            $this->returnJsonResponse(['errors' => 'There are no reviews available!'], 404);
            return;
        } else{
            $this->returnJsonResponse(['data' => $reviews ], 200);
        }
    }

    public function getByShowId($params)
    {
       $reviews = $this->db->executeRawQuery("
       SELECT reviews.id, reviews.text, reviews.stars, users.username
       from reviews
       inner join users on reviews.user_id = users.id
       where reviews.show_id = '" . $params['id'] . "'");

        if (empty($reviews)) {
            $this->returnJsonResponse(['errors' => 'This shows does not have reviews!'], 404);
            return;
        } else{
            $this->returnJsonResponse(['data' => $reviews ], 200);
        }
    }
    public function delete($params){
        $user = $this->checkAuthentication();

        if ($user === null) {
            $this->returnJsonResponse(['errors' => 'Wrong access token!'], 401);
            return;
        }
        $review = $this->db->select('reviews', '*', ' id = "' . $params['id'] . '"', [], true);
        if (empty($review)) {
            $this->returnJsonResponse(['errors' => 'This review does not exist!'], 401);
            return;
        }

        if ($user['type'] != 'admin' && $user['id'] != $review['user_id']) {
            $this->returnJsonResponse(['errors' => 'You\'re not allowed to call this request!'], 401);
            return;
        }

        $this->db->delete('reviews',  ' id = "' . $params['id'] . '"');
        $this->returnJsonResponse(['message' => 'This review has been deleted!'], 200);
    }

}