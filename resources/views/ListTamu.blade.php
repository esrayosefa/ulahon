<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
    th { background: #f5f7fb; text-align: left; }
    h2 { margin-bottom: 10px; }
    .muted { color: #666; }
  </style>
</head>
<body>
  @php use Carbon\Carbon; @endphp
  <h2>Daftar Tamu</h2>
  <table>
    <thead>
      <tr>
        <th>Nama</th>
        <th>No. Whatsapp</th>
        <th>Email</th>
        <th>Asal Instansi</th>
        <th>Jenis Kelamin</th>
        <th>Waktu Kunjungan</th>
        <th>Alamat</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td>{{ $r->nama }}</td>
          <td>{{ $r->no_hp }}</td>
          <td>{{ $r->email }}</td>
          <td>{{ $r->asal_instansi }}</td>
          <td>{{ $r->jenis_kelamin }}</td>
          <td>
            {{ $r->waktu_kunjungan
                ? Carbon::parse($r->waktu_kunjungan)->timezone('Asia/Jakarta')->format('Y-m-d H:i')
                : '—' }}
          </td>
          <td>{{ $r->alamat }}</td>
        </tr>
      @empty
        <tr><td colspan="7" style="text-align:center">Tidak ada data.</td></tr>
      @endforelse
    </tbody>
  </table>

  <p class="muted">Diekspor: {{ now('Asia/Jakarta')->format('Y-m-d H:i') }} WIB</p>
</body>
</html>
