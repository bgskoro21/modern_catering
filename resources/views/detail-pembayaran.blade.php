<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        table{
            width: 60%;
        }
        .label{
            width: 15%;
        }
        .detail{
            width: 20%;
        }
        .dot{
            width: 2%;
        }

        .detail-container{
            border-top: 2px dashed rgb(255, 79, 79);
            border-bottom: 2px dashed rgb(255, 79, 79);
            padding: 10px 0;
            margin-top:20px; 
        }
    </style>
</head>
<body>
    <h1 style="margin-bottom: 30px; text-align:center;">DETAIL PEMBAYARAN</h1>
    <table>
        <tr>
            <td class="label">No. Pesanan</td>
            <td class="dot">:</td>
            <td class="detail">{{ $order->id }}</td>
        </tr>
        <tr>
            <td class="label">Nama Pemesan</td>
            <td class="dot">:</td>
            <td class="detail">{{ $order->nama_pemesan }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Pesan</td>
            <td class="dot">:</td>
            <td class="detail">{{ \Carbon\Carbon::parse($order->tanggal_pemesanan)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}</td>
        </tr>
        <tr>
            <td class="label">Status Pesanan</td>
            <td class="dot">:</td>
            <td class="detail">{{ $order->status }}</td>
        </tr>
    </table>
    @foreach ($order->payments as $item)
    <div class="detail-container">
        <h3 style="margin:0 0 10px 0; ">Pembayaran {{ $loop->iteration }} - {{ $item->jenis_pembayaran }}</h3>
        <table>
            <tr>
                <td class="label">Metode Pembayaran</td>
                <td class="dot">:</td>
                <td class="detail">{{ $item->metode_pembayaran }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Bayar</td>
                <td class="dot">:</td>
                <td class="detail">{{ \Carbon\Carbon::parse($item->tanggal_pembayaran)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}</td>
            </tr>
            <tr>
                <td class="label">Jumlah Bayar</td>
                <td class="dot">:</td>
                <td class="detail">@currency($item->jumlah_bayar)</td>
            </tr>
            <tr>
                <td class="label">Sisa Tagihan</td>
                <td class="dot">:</td>
                <td class="detail">@currency($item->sisa)</td>
            </tr>
        </table>
    </div>
    @endforeach

</body>
</html>