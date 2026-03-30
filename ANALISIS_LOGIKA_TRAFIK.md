# Analisis Logika Halaman Trafik

## Tanggal Analisis
10 Maret 2026

## Ringkasan Masalah

### 1. **MISMATCH FILTER: Wilayah vs Cabang**
**Lokasi:** `TrafikController@index` vs `trafik_simple.blade.php`

**Masalah:**
- View menggunakan filter `wilayah` (line 276-283 di trafik_simple.blade.php)
- Controller menggunakan filter `cabang` (line 124 di TrafikController.php)
- Ini menyebabkan filter wilayah tidak berfungsi sama sekali

**Impact:** 
- User memilih "Wilayah 1" tapi tidak ada efek karena controller mencari parameter `cabang`, bukan `wilayah`

**Solusi:**
```php
// Di TrafikController.php, ganti:
$selectedBranch = $request->get('cabang', 'all');

// Menjadi:
$selectedWilayah = $request->get('wilayah', 'all');
```

---

### 2. **MISMATCH KOLOM: cabang vs wilayah** 
**Lokasi:** `TrafikController@index` line 209-211

**Masalah:**
- Controller mencari kolom `cabang` di tabel
- Tabel `trafik_rkap_realisasi` memiliki kolom `wilayah`, BUKAN `cabang`
- Query `where('cabang', $selectedBranch)` akan FAIL atau return 0 rows

**Bukti dari debug script:**
```
Using table: trafik_rkap_realisasi
sample row keys: id, wilayah, periode, pelayaran, lokasi, Call, jenis, created_at, updated_at, GT
```

**Impact:**
- Filter berdasarkan wilayah tidak bekerja
- Semua query mengembalikan hasil kosong atau semua data

**Solusi:**
```php
// Di TrafikController.php, line 209-211, ganti:
if ($selectedBranch != 'all') {
    $rowsQuery->where('cabang', $selectedBranch);
}

// Menjadi:
if ($selectedWilayah != 'all') {
    $rowsQuery->where('wilayah', $selectedWilayah);
}
```

---

### 3. **DATA STRUKTUR TIDAK LENGKAP**
**Lokasi:** `TrafikController@index` return statement

**Masalah:**
- View mengharapkan variabel `$trafikData` (line 481 di trafik_simple.blade.php)
- Controller TIDAK menyediakan `$trafikData`
- View menampilkan data placeholder karena `$trafikData` kosong

**View expects:**
```php
foreach($trafikData ?? [] as $wil=>$wdata) {
    foreach(['dalam_negeri','luar_negeri'] as $k) {
        foreach($wdata[$k]['locations'] ?? [] as $lok=>$vals) {
            ...
```

**Controller provides:**
- `$rows` (raw data dari query)
- Tidak ada aggregasi berdasarkan wilayah/lokasi  
- Tidak ada struktur hierarkis dalam_negeri/luar_negeri

**Impact:**
- Tabel "Summary Hierarki" menampilkan data hardcoded/placeholder
- KPI Cards (Call, GT) menampilkan nilai 0

---

### 4. **KPI VALUES ALWAYS ZERO**
**Lokasi:** View line 345-357 (output HTML)

**Masalah:**
```html
<div class="kpi-value">Call: 0</div>
<div class="kpi-value text-primary">GT: 0</div>
```

**Root Cause:**
- View tidak mendapat data agregat untuk KPI
- Controller hanya query `$rows` tapi tidak menghitung total Call/GT
- Variable yang dibutuhkan tidak dikirim ke view

**Yang dibutuhkan view:**
- Total Call (sum dari kolom Call)
- Total GT (sum dari kolom GT)
- Aggregate per wilayah

**Solusi:**
Controller perlu menambahkan query agregasi seperti:
```php
$totalCall = $conn->table($table)
    ->selectRaw('SUM(Call) as total')
    ->when($selectedWilayah != 'all', function($q) use ($selectedWilayah) {
        return $q->where('wilayah', $selectedWilayah);
    })
    ->when($selectedPeriode != 'all', function($q) use ($selectedPeriode) {
        return $q->where('periode', $selectedPeriode);
    })
    ->value('total') ?? 0;

// Similar untuk GT, Produksi Pemanduan, Produksi Penundaan
```

---

### 5. **STRUKTUR TABEL trafik_rkap_realisasi**
**Berdasarkan debug output:**

Kolom yang tersedia:
- `id`
- `wilayah` (bukan cabang!)
- `periode` (format: MM-YYYY, contoh: 01-2026)
- `pelayaran`
- `lokasi`
- `Call` (dengan huruf kapital C)
- `GT` (dengan huruf kapital)
- `jenis`
- `created_at`
- `updated_at`

**Catatan penting:**
- Tidak ada kolom `bulan` dan `tahun` terpisah
- Periode disimpan langsung sebagai string MM-YYYY
- Kolom Call dan GT menggunakan huruf kapital

---

### 6. **LOGIKA FILTER PERIODE**
**Lokasi:** `TrafikController@index` line 216-233

**Masalah:**
- Controller mencari kolom `bulan` dan `tahun` terpisah
- Tabel `trafik_rkap_realisasi` hanya punya kolom `periode` (MM-YYYY)
- Kondisi `$hasBulan` akan selalu FALSE untuk tabel ini

**Current logic:**
```php
$hasBulan = true;
try {
    $test = $conn->table($table)->select('bulan','tahun')->limit(1)->first();
    if (! $test) $hasBulan = false;
} catch (\Exception $e) {
    $hasBulan = false;
}
```

**Result:** `$hasBulan = false` selalu

**Impact:**
- Filter periode menggunakan branch else: `$rowsQuery->where('periode', $selectedPeriode);`
- Ini BENAR untuk tabel trafik_rkap_realisasi
- Tapi logika parsing `explode('-', $selectedPeriode)` untuk `$m` dan `$y` tidak terpakai
- Variabel `$m` dan `$y` tetap null, jadi `$endOfMonth` juga null
- Kolom "selisih (hari)" tidak akan pernah dihitung

---

## REKOMENDASI PERBAIKAN

### Priority 1 (Critical - Data tidak muncul):
1. ✅ Ganti semua `cabang` → `wilayah` di controller
2. ✅ Sesuaikan filter di view dan controller (konsisten gunakan `wilayah`)
3. ✅ Tambahkan query agregasi untuk KPI (total Call, GT)

### Priority 2 (Major - Data tidak lengkap):
4. ⚠️ Buat struktur data `$trafikData` yang sesuai ekspektasi view
5. ⚠️ Aggregate data berdasarkan wilayah, pelayaran (dalam/luar negeri), lokasi

### Priority 3 (Nice to have):
6. 🔧 Perbaiki perhitungan "selisih (hari)" untuk tabel tanpa bulan/tahun terpisah
7. 🔧 Tambahkan error handling yang lebih baik (tampilkan pesan jika tabel kosong)
8. 🔧 Tambahkan logging untuk debugging filter

---

## TEST CASE YANG HARUS PASS

### Test 1: Filter Periode Only
```
Input: periode=01-2026, wilayah=all
Expected: Menampilkan semua data dari periode 01-2026 (32 rows dari debug)
Current: PASS (setelah perbaikan fallback tabel)
```

### Test 2: Filter Wilayah Only  
```
Input: periode=all, wilayah=wilayah 1
Expected: Menampilkan semua data dari wilayah 1
Current: FAIL (parameter tidak digunakan)
```

### Test 3: Filter Kombinasi
```
Input: periode=01-2026, wilayah=wilayah 2
Expected: Menampilkan data periode 01-2026 DAN wilayah 2
Current: FAIL (wilayah filter tidak bekerja)
```

### Test 4: KPI Display
```
Expected: Total Call dan GT ditampilkan sesuai data real
Current: FAIL (selalu 0)
```

---

## CATATAN TAMBAHAN

### Tabel yang Digunakan
- Default: `trafik` (tidak ada)
- Fallback: `trafik_rkap_realisasi` ✅ (ada, 32 rows untuk 01-2026)
- Fallback2: `trafik_rekap_realisasi` (dicek, tidak digunakan)

### Sample Data (dari debug):
- Periode tersedia: 01-2026
- Count untuk periode=01-2026, wilayah=all: 32 rows
- Kolom tersedia: id, wilayah, periode, pelayaran, lokasi, Call, GT, jenis

### Regional Groups
Controller mendefinisikan regional groups (WILAYAH 1-4, JAI) tapi:
- Mapping ini untuk kolom `cabang` (tidak ada di tabel)
- Tidak digunakan untuk tabel `trafik_rkap_realisasi`
- Perlu disesuaikan dengan kolom `wilayah` yang ada

---

## STATUS SAAT INI
- ❌ Filter wilayah: TIDAK BERFUNGSI
- ✅ Filter periode: BERFUNGSI (setelah fallback tabel)
- ❌ KPI Cards: Menampilkan 0 (seharusnya ada nilai)
- ❌ Summary Hierarki: Data placeholder (tidak real)
- ✅ Charts: Render tapi dengan data trend dari controller (bukan raw rows)
- ✅ Koneksi DB: OK
- ✅ Tabel tersedia: trafik_rkap_realisasi (32 rows)

## KESIMPULAN
Controller dan view tidak sinkron. Controller didesain untuk tabel `trafik` dengan kolom `cabang`, `bulan`, `tahun`, sedangkan tabel actual (`trafik_rkap_realisasi`) punya struktur berbeda dengan kolom `wilayah` dan `periode`.

Perlu refactoring controller untuk menyesuaikan dengan struktur tabel yang ada dan ekspektasi view.
