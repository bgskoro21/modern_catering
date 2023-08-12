<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        #customers {
          font-family: Arial, Helvetica, sans-serif;
          border-collapse: collapse;
          width: 100%;
        }
        
        #customers td, #customers th {
          border: 1px solid #ddd;
          padding: 8px;
        }
        
        #customers tr:nth-child(even){background-color: #f2f2f2;}
        
        #customers tr:hover {background-color: #ddd;}
        
        #customers th {
          padding-top: 12px;
          padding-bottom: 12px;
          text-align: center;
          background-color: #9c0000;
          color: white;
        }
        </style>
</head>
<body>
    <section>
        <h1 class="text-center">MODERN CATERING</h1>
        <h1 class="text-center">LAPORAN PELANGGAN</h1>
        <p class="text-center">Jl. Tirtasari Gg. Damai No. 7 Way Huwi, Lampung Selatan</p>
        <div class="table-wrapper mt-4">
            <table id="customers">
                <thead>
                    <tr>
                      <th>No</th>
                      <th>Nama Pelanggan</th>
                      <th>Email Pelanggan</th>
                      <th>Total Transaksi</th>
                      <th>Total Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                        $total2 = 0;
                    @endphp
                    @if ($customers->count())
                    @foreach ($customers as $customer)
                    <tr>
                      <td class="text-center">{{ $loop->iteration }}</td>
                      <td>{{ $customer->name }}</td>
                      <td>{{ $customer->email }}</td>
                      <td class="text-center">{{ $customer->total_pemesanan }}</td>
                      <td>@currency($customer->total_biaya)</td>
                    </tr>
                    @php
                        $total += $customer->total_pemesanan;
                        $total2 += $customer->total_biaya
                    @endphp
                    @endforeach
                    <tr>
                        <td colspan="3" class="text-center"><h2>TOTAL</h2></td>
                        <td class="fw-bold text-center">{{ $total }}x</td>
                        <td class="fw-bold">@currency($total2)</td>
                    </tr>
                    @else
                    <tr>
                       <td colspan="5" class="text-center">Belum ada pelanggan yang beli!</td>
                    </tr>
                    @endif
                </tbody>
              </table>
        </div>
        <div class="ttd-wrapper mt-4" style="position: absolute; right:0;">
            <div class="keterangan">
                <span>
                    Bandar Lampung, {{ \Carbon\Carbon::parse(\Carbon\Carbon::now())->locale('id')->isoFormat('D MMMM YYYY') }}
                </span><br>
                <span>Pemilik,</span>
            </div>
            <div style="margin: 40px 0"></div>
            <div class="nama fw-bold">Chandra Husada A.N.</div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
</body>
</html>