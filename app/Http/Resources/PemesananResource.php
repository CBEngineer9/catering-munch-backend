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
            'users_provider' => $this->users_provider,
            'users_customer' => $this->users_customer->UsersCustomer->users_nama,
            'pemesanan_jumlah' => $this->pemesanan_jumlah,
            'pemesanan_total' => $this->pemesanan_total,
            'pemesanan_status' => $this->pemesanan_status,
            'pemesanan_rating' => $this->pemesanan_rating,
            'detail_pemesanan' => DetailPemesananResource::collection($this->DetailPemesanan),
        ];
    }
}
