<?php

namespace App\Http\Controllers\Api;

class ResponseInterface
{

    public int $statusCode;
    public bool $success;
    public string $msg;
    public array $datas;

    public function __construct(int $statusCode, bool $success, string $msg, array $datas = [])
    {
        $this->statusCode = $statusCode;
        $this->success = $success;
        $this->msg = $msg;
        $this->datas = $datas;
    }

    public function toArray(): array
    {
        $response['statusCode'] = $this->statusCode;
        $response['success'] = $this->success;
        $response['msg'] = $this->msg;

        foreach ($this->datas as $key => $value) {
            $response[$key] = $value;
        }

        return $response;
    }
}