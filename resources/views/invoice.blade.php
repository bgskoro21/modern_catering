<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice->order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .row-invoice-order {
        background-color: rgb(250, 213, 213);
        }
        table{
            width: 100%;
        }
        h1{
            margin-bottom: 0;
            color: rgb(175, 0, 0)
        }

        .invoice-order{
            width: 30%;
        }

        .mt-4{
            margin-top: 10px;
        }

        .table-invoice-order{
            padding: 10px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="invoice-order">
                <h1>Invoice</h1>
            </td>
            <td class="qr" align="right">
                <img src="{{ public_path().'/storage/qr_code/qrcode'.$invoice->order->id.'.png' }}" alt="Qr Code" style="height: 120px; width:120px">
            </td>
        </tr>
    </table>
    <div style="margin-top: 15px;">
        <span>KEPADA</span>
        <br />
        <div class="fw-bold mt-2">{{ $invoice->order->nama_pemesan }}</div>
        <span>{{ $invoice->order->no_telp_pemesan }}</span>
        <br />
        <div style="max-width: 200px;">{{ $invoice->order->alamat_pemesan }}</div>
    </div>
    <table class="row-invoice-order mt-4 table-invoice-order">
        <tr>
            <td style="width: 50%;">Pesanan</td>
            <td style="width=20%">Harga</td>
            <td style="width=20%">Jumlah</td>
            <td style="width=10%;" align="right">Total</td>
        </tr>
    </table>
    <table class="table-invoice-order">
        @foreach($invoice->order->transactions as $item)
        <tr>
            <td style="width: 50%;">
                <span style="font-size: 12px;">{{ $item->paket_prasmanan->kategori->nama_kategori }} : {{ $item->paket_prasmanan->nama_paket }}</span><br>
                <span style="font-size: 12px;">{!! $item->menu !!}</span>
            </td>
            <td style="width=20%">@currency($item->paket_prasmanan->harga)</td>
            <td style="width=20%">{{ $item->jumlah_pesanan }} porsi</td>
            <td style="width=10%;" align="right">@currency($item->total_harga)</td>
        </tr>
        @endforeach
    </table>
    <table class="row-invoice-order mt-4 table-invoice-order">
        <tr>
            <td style="width: 80%;">TOTAL KESELURUHAN</td>
            <td style="width=20%;" align="right">@currency($invoice->order->total)</td>
        </tr>
    </table>
    <div class="mt-4">
        <span>DIBAYARKAN KEPADA</span>
        <br />
        <div class="fw-bold mt-4">Chandra Abdullah A.N</div>
        <span>Pembayaran menggunakan Bank BCA</span>
      </div>
    </div>
    <div class="mt-4">
        <span style="font-size:12px;">*Catatan</span>
        <br />
        <ol style="font-size:12px; margin:0;padding:0 20px;">
            <li>Pembayaran baru bisa dilakukan setelah pesanan disetujui!</li>
            <li>Pembayaran DP setengah dari total belanja!</li>
            <li>Pembayaran DP paling lambat 3 hari!</li>
        </ol>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>