<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsersCustomerResource extends JsonResource
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
            'users_id' => $this->users_id,
            'users_email' => $this->users_email,
            'users_telepon' => $this->users_telepon,
            'users_nama' => $this->users_nama,
            'users_alamat' => $this->users_alamat,
            'users_password' => $this->users_password,
            'users_desc' => $this->users_desc,
            'users_saldo' => $this->users_saldo,
            'users_role' => $this->users_role,
            'users_status' => $this->users_status,
        ];
    }
}
