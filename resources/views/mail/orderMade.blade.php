<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    A customer just ordered your menu
    {{-- TODO --}}
    <table>
        <tbody>
            <tr>
                <td>Customer</td>
                <td></td>
            </tr>
            <tr>
                <td>Jumlah pemesanan :</td>
                <td>{{ $historyPemesanan->pemesanan_jumlah }}</td>
            </tr>
            <tr>
                <td>Total pemesanan :</td>
                <td>{{ $historyPemesanan->pemesanan_total }}</td>
            </tr>
        </tbody>
    </table>

    <table border="1">
        <thead>
            <tr>
                <td>Menu</td>
                <td>Jumlah</td>
                <td>Subtotal</td>
                <td>Tanggal pengiriman</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($historyPemesanan->DetailPemesanan as $detail)
                <tr>
                    <td>{{$detail->Menu->menu_nama}}</td>
                    <td>{{$detail->detail_jumlah}}</td>
                    <td>{{$detail->detail_total}}</td>
                    {{-- <td>{{dump($detail->detail_tanggal)}}</td> --}}
                    <td>{{date_format(date_create($detail->detail_tanggal),"d/m/Y H:i:s")}}</td>
                    {{-- @dump($detail->Menu) --}}
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>