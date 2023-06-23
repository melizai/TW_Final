<?php

include("BaseController.php");

class ErrorController extends BaseController
{
    public function notFound()
    {
        $this->returnJsonResponse(['errors' => "Page not found"], 404);
    }
}