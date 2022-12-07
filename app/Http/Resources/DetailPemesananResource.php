<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailPemesananResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'detail_id' => $this->detail_id,
            'pemesanan_id' => $this->pemesanan_id,
            'menu_id' => $this->menu_id,
            'detail_jumlah' => $this->detail_jumlah,
            'detail_total' => $this->detail_total,
            'detail_tanggal' => $this->detail_tanggal,
            'detail_status' => $this->detail_status,
        ];
    }
}
