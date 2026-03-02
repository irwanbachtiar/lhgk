<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Trafik</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body{background:#f8f9fa}
    td.uraian .uraian-label::before{content:'•';display:inline-block;width:1rem;margin-right:.25rem}
    td.uraian.level-1 .uraian-label{color:#0d6efd;font-weight:700}
    td.uraian.level-2 .uraian-label{color:#198754;font-weight:600}
    .numeric{text-align:right}
    .table-fixed thead th{position:sticky;top:0;background:#fff}
  </style>
</head>
<body>
  <nav class="navbar navbar-dark mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow"></i> Trafik</span>
      <div>
        <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2"><i class="bi bi-house"></i> Dashboard</a>
        <a href="{{ route('dashboard.operasional') }}" class="btn btn-light btn-sm me-2">Operasional</a>
        <a href="{{ route('monitoring.nota') }}" class="btn btn-light btn-sm">Monitoring Nota</a>
      </div>
    </div>
  </nav>

  <div class="container">
    <div class="row mb-4">
      <div class="col-12">
        <div class="period-filter" style="background:white;padding:15px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.06);">
          <form id="trafikFilterForm" method="GET" action="{{ url()->current() }}" class="row g-2 align-items-center">
            <div class="col-md-2">
              <label class="form-label"><i class="bi bi-funnel"></i> Filter</label>
            </div>
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-building"></i> Cabang</label>
              <select name="cabang" class="form-select">
                <option value="all" {{ ($selectedBranch ?? 'all') == 'all' ? 'selected' : '' }}>Semua Cabang</option>
                @if(!empty($regionalGroups))
                  @foreach($regionalGroups as $group => $branches)
                    @if(count($branches) > 0)
                      <optgroup label="{{ $group }}">
                        @foreach($branches as $b)
                          <option value="{{ $b }}" {{ ($selectedBranch ?? '') == $b ? 'selected' : '' }}>{{ Str::limit($b, 60) }}</option>
                        @endforeach
                      </optgroup>
                    @endif
                  @endforeach
                @endif
                @if(!empty($otherBranches))
                  <optgroup label="Lainnya">
                    @foreach($otherBranches as $b)
                      <option value="{{ $b }}" {{ ($selectedBranch ?? '') == $b ? 'selected' : '' }}>{{ Str::limit($b, 60) }}</option>
                    @endforeach
                  </optgroup>
                @endif
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label"><i class="bi bi-calendar-range"></i> Periode</label>
              <select name="periode" class="form-select">
                <option value="all" {{ ($selectedPeriode ?? 'all') == 'all' ? 'selected' : '' }}>Semua Periode</option>
                @if(!empty($periods))
                  @foreach($periods as $period)
                    <option value="{{ $period }}" {{ ($selectedPeriode ?? '') == $period ? 'selected' : '' }}>{{ $period }}</option>
                  @endforeach
                @endif
              </select>
            </div>

            <div class="col-md-3 d-flex align-items-end justify-content-end">
              <div class="me-2">
                @if(($selectedPeriode ?? 'all') != 'all' || ($selectedBranch ?? 'all') != 'all')
                  <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
                @endif
              </div>
              <div>
                <button type="submit" class="btn btn-primary">Tampilkan</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Sample Rows removed per request -->

    @if(isset($rows) && is_iterable($rows) && count($rows) > 0)
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">Data Trafik — Periode: {{ $selectedPeriode }} · Cabang: {{ $selectedBranch }}</h5>
          <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  @foreach(array_keys((array)collect($rows)->first()) as $col)
                    <th>{{ $col }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach($rows as $r)
                  <tr>
                    @foreach((array)$r as $v)
                      <td>{{ $v }}</td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @elseif(isset($rows) && is_iterable($rows) && count($rows) == 0)
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">Demo Trafik (Data contoh — tidak ada data dari server)</h5>
          <div class="table-responsive">
            <table id="trafik-table-empty" class="table table-bordered table-sm">
              <thead style="background:#f1f1f1">
                <tr>
                  <th rowspan="2">URAIAN</th>
                  <th rowspan="2">SATUAN</th>
                  <th colspan="3" class="text-center">ANGGARAN</th>
                  <th colspan="2" class="text-center">REALISASI</th>
                  <th rowspan="2" class="text-center">REALISASI THN LALU</th>
                  <th colspan="4" class="text-center">TREND REALISASI</th>
                </tr>
                <tr>
                  <th class="text-end">1 TAHUN (1)</th>
                  <th class="text-end">SD BULAN INI (2)</th>
                  <th class="text-end">BULAN INI (3)</th>
                  <th class="text-end">SD BULAN INI (4)</th>
                  <th class="text-end">BULAN INI (5)</th>
                  <th class="text-end">7 = 4:2</th>
                  <th class="text-end">8 = 4:1</th>
                  <th class="text-end">9 = 5:3</th>
                  <th class="text-end">10 = 4:6</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td rowspan="2">Kunjungan Kapal Berdasarkan Lokasi Sandar, Jenis Pelayaran dan Jenis Muatan Kapal</td>
                  <td>Call</td>
                  <td class="text-end">1,200.00</td>
                  <td class="text-end">900.00</td>
                  <td class="text-end">100.00</td>
                  <td class="text-end">950.00</td>
                  <td class="text-end">110.00</td>
                  <td class="text-end">800.00</td>
                  <td class="text-end">1.06</td>
                  <td class="text-end">0.79</td>
                  <td class="text-end">1.10</td>
                  <td class="text-end">1.19</td>
                </tr>
                <tr>
                  <td>GT</td>
                  <td class="text-end">60,000.00</td>
                  <td class="text-end">45,000.00</td>
                  <td class="text-end">5,000.00</td>
                  <td class="text-end">48,000.00</td>
                  <td class="text-end">5,200.00</td>
                  <td class="text-end">40,000.00</td>
                  <td class="text-end">1.07</td>
                  <td class="text-end">0.80</td>
                  <td class="text-end">0.96</td>
                  <td class="text-end">1.20</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @else
      <!-- Fallback demo editable grid when controller data not provided -->
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">Demo Trafik (Data contoh)</h5>
          <div class="table-responsive">
            <table id="trafik-table" class="table table-bordered table-sm">
              <thead style="background:#f1f1f1">
                <tr>
                  <th rowspan="2">URAIAN</th>
                  <th rowspan="2">SATUAN</th>
                  <th colspan="3" class="text-center">ANGGARAN</th>
                  <th colspan="2" class="text-center">REALISASI</th>
                  <th rowspan="2" class="text-center">REALISASI THN LALU</th>
                  <th colspan="4" class="text-center">TREND REALISASI</th>
                </tr>
                <tr>
                  <th class="text-end">1 TAHUN (1)</th>
                  <th class="text-end">SD BULAN INI (2)</th>
                  <th class="text-end">BULAN INI (3)</th>
                  <th class="text-end">SD BULAN INI (4)</th>
                  <th class="text-end">BULAN INI (5)</th>
                  <th class="text-end">7 = 4:2</th>
                  <th class="text-end">8 = 4:1</th>
                  <th class="text-end">9 = 5:3</th>
                  <th class="text-end">10 = 4:6</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td rowspan="2">Kunjungan Kapal Berdasarkan Lokasi Sandar, Jenis Pelayaran dan Jenis Muatan Kapal</td>
                  <td>Call</td>
                  <td class="text-end">1,200.00</td>
                  <td class="text-end">900.00</td>
                  <td class="text-end">100.00</td>
                  <td class="text-end">950.00</td>
                  <td class="text-end">110.00</td>
                  <td class="text-end">800.00</td>
                  <td class="text-end">1.06</td>
                  <td class="text-end">0.79</td>
                  <td class="text-end">1.10</td>
                  <td class="text-end">1.19</td>
                </tr>
                <tr>
                  <td>GT</td>
                  <td class="text-end">60,000.00</td>
                  <td class="text-end">45,000.00</td>
                  <td class="text-end">5,000.00</td>
                  <td class="text-end">48,000.00</td>
                  <td class="text-end">5,200.00</td>
                  <td class="text-end">40,000.00</td>
                  <td class="text-end">1.07</td>
                  <td class="text-end">0.80</td>
                  <td class="text-end">0.96</td>
                  <td class="text-end">1.20</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
  (function(){
    function num(v){ if(v===undefined||v===null||v==='') return 0; v=String(v).replace(/[^0-9,.-]/g,'').replace(/,/g,'.'); var n=Number(v); return isNaN(n)?0:n; }
    function div(a,b){ b=num(b); if(b===0) return 0; return num(a)/b; }
    function computeRow(row){ var ang1=num($('[data-row="'+row+'"][data-field="ang_1"]').val()); var ang2=num($('[data-row="'+row+'"][data-field="ang_2"]').val()); var ang3=num($('[data-row="'+row+'"][data-field="ang_3"]').val()); var r4=num($('[data-row="'+row+'"][data-field="real_4"]').val()); var r5=num($('[data-row="'+row+'"][data-field="real_5"]').val()); var r6=num($('[data-row="'+row+'"][data-field="real_thn_lalu_6"]').val()); $('[data-row="'+row+'"][data-field="t7"]').val((div(r4,ang2)||0).toLocaleString('id-ID',{maximumFractionDigits:2})); $('[data-row="'+row+'"][data-field="t8"]').val((div(r4,ang1)||0).toLocaleString('id-ID',{maximumFractionDigits:2})); $('[data-row="'+row+'"][data-field="t9"]').val((div(r5,ang3)||0).toLocaleString('id-ID',{maximumFractionDigits:2})); $('[data-row="'+row+'"][data-field="t10"]').val((div(r4,r6)||0).toLocaleString('id-ID',{maximumFractionDigits:2})); }
    function computeAll(){ var rows={}; $('.grid').each(function(){ rows[$(this).data('row')]=true; }); Object.keys(rows).forEach(computeRow); }
    $(document).on('input change','.grid',function(){ computeAll(); });
    $('#reset-demo').on('click',function(){ location.reload(); });
    $('#export-json').on('click',function(){ var out=[]; $('.grid').each(function(){ var r=$(this).data('row'); var f=$(this).data('field'); if(!out[r]) out[r]={row:r}; out[r][f]=$(this).val(); }); var arr=Object.values(out); var b=new Blob([JSON.stringify(arr,null,2)],{type:'application/json'}); var a=document.createElement('a'); a.href=URL.createObjectURL(b); a.download='trafik-demo.json'; a.click(); });
    // initial compute
    setTimeout(computeAll,100);
  })();
  </script>
</body>
</html>