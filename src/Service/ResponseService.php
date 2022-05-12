<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ResponseService
{
    protected $statusCode = 200;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function response($data, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    public function respondWithErrors($errors, $headers = []): JsonResponse
    {
        return $this->response([
            'status'    => $this->getStatusCode(),
            'errors'    => $errors
        ], $headers);
    }

    public function respondWithSuccess($success, $headers = []): JsonResponse
    {
        return $this->response([
            'status'    => $this->getStatusCode(),
            'success'   => $success,
        ], $headers);
    }

    public function respondUnauthorized($message = 'Not authorized!'): JsonResponse
    {
        return $this->setStatusCode(401)->respondWithErrors($message);
    }

    public function respondValidationError($message = 'Validation errors'): JsonResponse
    {
        return $this->setStatusCode(422)->respondWithErrors($message);
    }

    public function respondNotFound($message = 'Not found!'): JsonResponse
    {
        return $this->setStatusCode(404)->respondWithErrors($message);
    }

    public function respondCreated($data = []): JsonResponse
    {
        return $this->setStatusCode(201)->response($data);
    }

    public function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }

}
