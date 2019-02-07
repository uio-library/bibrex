<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            [
                'type' => 'thing',
                'id' => $this->id,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at->toDateTimeString(),
                'items' => ItemResource::collection($this->whenLoaded('items')),
                'image' => $this->image,
            ],
            $this->properties->toArray()
        );
    }
}
