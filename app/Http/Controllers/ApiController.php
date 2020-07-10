<?php

namespace App\Http\Controllers;

use Response;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    protected $statusCode =  200;

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function respond($data, $headers = [])
    {
        return Response::json($data, $this->getStatusCode(), $headers);
    }

    public function respondWithError($message)
    {
        return $this->respond([
            'error' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }

    public function respondNotFound($message = 'Not Found!')
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    public function respondUnprocessable($message = 'Existing Data!')
    {
        return $this->setStatusCode(422)->respondWithError($message);
    }

    public function respondInternalServerError($message = 'Internal Server Error!')
    {
        return $this->setStatusCode(500)->respondWithError($message);
    }

    /* With Success Message */
    public function respondWithSuccess($message, $data = [])
    {
        return $this->respond([
            'success' => [
                'message' => $message,
                'data' => $data,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }

    public function respondSuccessful($message = 'Success!')
    {
        return $this->respondWithSuccess($message);
    }

    public function respondSuccessfulWithData($message = 'Success!', $data = [])
    {
        return $this->respondWithSuccess($message, $data);
    }
}
