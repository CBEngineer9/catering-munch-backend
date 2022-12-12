<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PemesananResource extends JsonResource
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
            'pemesanan_id' => $this->pemesanan_id,
            'users_provider' => $this->UsersProvider,
            'users_customer' => $this->UsersCustomer,
            'pemesanan_jumlah' => $this->pemesanan_jumlah,
            'pemesanan_total' => $this->pemesanan_total,
            'pemesanan_status' => $this->pemesanan_status,
            'pemesanan_rating' => $this->pemesanan_rating,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'detail_pemesanan' => DetailPemesananResource::collection($this->DetailPemesanan->sortBy('detail_tanggal')),
        ];
    }
}
