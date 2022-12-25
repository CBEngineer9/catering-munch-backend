<?php

namespace App\Exports;

use App\Models\DetailPemesanan;
use App\Models\HistoryPemesanan;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PenjualanTerbanyakReport implements FromCollection, WithHeadings
{
    /** @var Int $provider_id the related provider */
    protected $provider_id = null;

    /**
     * Constructor
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     **/
    public function __construct($provider_id)
    {
        $this->provider_id = $provider_id;
    }

    /**
     * Excel headers
     *
     * @return array
     **/
    public function headings(): array
    {
        return [
            'Menu',
            'Jumlah terjual',
            'total income'
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DetailPemesanan::whereHas('HistoryPemesanan',function(Builder $query) {
            $query->where('pemesanan_status','selesai')
                ->where('users_provider',$this->provider_id);
                ;
        })
        ->with("Menu:menu_id,menu_nama")
        ->addSelect(DB::raw('menu_id,sum(detail_jumlah) as total_terjual, sum(detail_total) as total_penjualan'))
        ->groupBy(['menu_id'])
        ->orderBy('total_penjualan')
        ->get()
        ->map(function($item){
            return [
                "menu_nama" => $item->menu->menu_nama,
                "total_terjual" => $item->total_terjual,
                "total_penjualn" => $item->total_penjualan,
            ];
        })
        ;
    }
}
