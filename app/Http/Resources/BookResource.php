<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this -> id,
            'type_id' => $this -> type_id,
            'type_name' => $this -> type -> name,
            'name' => $this -> name,
            'writer' => $this -> writer,
            'publishdate' => $this -> publishdate,
            'summary' => $this -> summary,
            'created_at' => (string)$this->create_at,
            'updated_at' => (string)$this->updated_at,
        ];
    }
}
