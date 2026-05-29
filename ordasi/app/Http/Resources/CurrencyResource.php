<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class CurrencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'iso' => $this->iso, 'name' => $this->name, 'symbol' => $this->symbol];
    }
}
