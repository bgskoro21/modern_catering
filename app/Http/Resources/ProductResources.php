<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResources extends JsonResource
{
    public $status, $messages;

    public function __construct($status, $messages, $resource)
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->messages = $messages;
    }

    public function toArray($request)
    {
        return [
            'status' => $this->status,
            'messages' => $this->messages,
            'data' => $this->resource
        ];
    }
}
