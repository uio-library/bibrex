<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
            'type' => 'item',
            'id' => $this->id,
            'barcode' => $this->barcode,
            'note' => $this->note,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'thing' => ThingResource::make($this->whenLoaded('thing')),
            'library' => LibraryResource::make($this->whenLoaded('library')),

            // If 'loans' is eager-loaded, this will only require one query.
            'available' => count($this->loans) === 0,
        ];
    }
}
