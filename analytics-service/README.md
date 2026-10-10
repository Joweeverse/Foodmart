# Analytics Service (Foodmart)

Analytics Service adalah salah satu dari tujuh service pada proyek **Implement Foodmart** (mata kuliah II3160 Teknologi Sistem Terintegrasi). Service ini dibangun dengan **CodeIgniter 4** dan berperan sebagai *consumer*: ia tidak menyimpan data sendiri, melainkan mengambil data dari service lain melalui API, mengolahnya, lalu menyajikan hasil analitik dalam bentuk JSON.

- **Sub-domain:** 7 (Analytics Service)
- **Integrasi:** 5 (Analytics Service → semua service)
- **Penanggung jawab:** _(isi nama)_
- **Kelompok:** _(isi nama/nomor kelompok)_
- **URL deploy:** _(isi setelah service di-deploy)_

## Daftar isi

1. [Fitur](#1-fitur)
2. [Arsitektur singkat](#2-arsitektur-singkat)
3. [Service yang dipanggil](#3-service-yang-dipanggil)
4. [Dokumentasi endpoint](#4-dokumentasi-endpoint)
5. [Kontrak data dengan service lain](#5-kontrak-data-dengan-service-lain)
6. [Konfigurasi](#6-konfigurasi)
7. [Cara menjalankan](#7-cara-menjalankan)
8. [Struktur proyek](#8-struktur-proyek-bagian-yang-ditambahkan)
9. [Perilaku dan batasan](#9-perilaku-dan-batasan)
10. [Pengujian](#10-pengujian)
11. [Status integrasi](#11-status-integrasi)
12. [Tentang CodeIgniter 4](#12-tentang-codeigniter-4)

---

## 1. Fitur

| Fitur | Endpoint |
|---|---|
| Produk terlaris | `GET /analytics/top-products` |
| Pelanggan terbaik | `GET /analytics/top-customers` |
| Tren penjualan bulanan | `GET /analytics/sales-trend` |
| Pemeriksaan status semua service | `GET /analytics/health` |

---

## 2. Arsitektur singkat

```
                    +----------------------+
 Executive          |  Analytics Service   |
 Dashboard  ------> |  (CodeIgniter 4)     |
                    |                      |
                    |  Controller          |
                    |   -> Calculator      |   hitung agregasi
                    |   -> Gateway         |   panggil + terjemahkan data
                    +----------+-----------+
                               |  HTTP (JSON)
        +----------+-----------+-----------+-----------+----------+
        |          |           |           |           |          |
     Sales     Product     Customer      Store     Inventory  Promotion
```

Struktur kode mengikuti pemisahan tanggung jawab berikut:

- **ServiceClient**: klien HTTP umum (cURL) untuk memanggil service lain, dengan penanganan error dan timeout.
- **Gateway** (`SalesGateway`, `ProductGateway`, `CustomerGateway`): menerjemahkan response service lain ke format internal Analytics. Jika nama field di service lain berubah, hanya gateway yang disesuaikan (pola *Anti-Corruption Layer*).
- **AnalyticsCalculator**: logika agregasi murni (tanpa HTTP), mudah diuji.
- **AnalyticsController**: menerima request, memanggil gateway dan calculator, lalu mengembalikan JSON.

---

## 3. Service yang dipanggil

| Service | Endpoint yang dipanggil | Dipakai untuk |
|---|---|---|
| Sales | `GET /sales` | Sumber data utama ketiga endpoint analitik |
| Product | `GET /products/{id}` | Nama produk pada `top-products` |
| Customer | `GET /customers/{id}` | Nama pelanggan pada `top-customers` |
| Store | `GET /stores` | Pemeriksaan status (`health`) |
| Inventory | `GET /inventory` | Pemeriksaan status (`health`) |
| Promotion | `GET /promotions` | Pemeriksaan status (`health`) |

---

## 4. Dokumentasi endpoint

Semua endpoint memakai metode `GET` dan mengembalikan JSON.

### 4.1 `GET /analytics/top-products`

Produk dengan jumlah unit terjual terbanyak.

| Parameter | Tipe | Default | Keterangan |
|---|---|---|---|
| `year` | integer | `1997` | Tahun data penjualan |
| `limit` | integer | `10` | Jumlah produk (minimum 1, maksimum 100) |

Contoh request:

```
GET /analytics/top-products?year=1997&limit=2
```

Contoh response (`200 OK`; nilai nama hanya contoh):

```json
{
  "message": "success",
  "year": 1997,
  "data": [
    {
      "product_id": 952,
      "total_units": 267.0,
      "total_revenue": 776.97,
      "product_name": "Contoh Produk A"
    },
    {
      "product_id": 549,
      "total_units": 258.0,
      "total_revenue": 433.44,
      "product_name": "Contoh Produk B"
    }
  ]
}
```

Jika Product Service tidak dapat dihubungi, `product_name` bernilai `null`, sedangkan data lainnya tetap ditampilkan.

### 4.2 `GET /analytics/top-customers`

Pelanggan dengan total belanja terbesar.

| Parameter | Tipe | Default | Keterangan |
|---|---|---|---|
| `year` | integer | `1997` | Tahun data penjualan |
| `limit` | integer | `10` | Jumlah pelanggan (minimum 1, maksimum 100) |

Contoh request:

```
GET /analytics/top-customers?year=1997&limit=2
```

Contoh response (`200 OK`; nilai nama hanya contoh):

```json
{
  "message": "success",
  "year": 1997,
  "data": [
    {
      "customer_id": 4021,
      "total_spent": 1078.69,
      "total_orders": 146,
      "customer_name": "Contoh Pelanggan A"
    },
    {
      "customer_id": 8452,
      "total_spent": 980.96,
      "total_orders": 120,
      "customer_name": "Contoh Pelanggan B"
    }
  ]
}
```

`total_orders` adalah jumlah baris penjualan milik pelanggan tersebut, bukan jumlah transaksi unik. Jika Customer Service tidak dapat dihubungi, `customer_name` bernilai `null`.

### 4.3 `GET /analytics/sales-trend`

Ringkasan penjualan per bulan.

| Parameter | Tipe | Default | Keterangan |
|---|---|---|---|
| `year` | integer | `1997` | Tahun data penjualan |

Contoh request:

```
GET /analytics/sales-trend?year=1997
```

Contoh response (`200 OK`, dipotong dua bulan):

```json
{
  "message": "success",
  "year": 1997,
  "data": [
    {
      "period": "1997-01",
      "revenue": 45539.69,
      "cost": 18178.49,
      "units": 21628.0,
      "profit": 27361.2
    },
    {
      "period": "1997-02",
      "revenue": 44058.79,
      "cost": 17599.67,
      "units": 20957.0,
      "profit": 26459.12
    }
  ]
}
```

`profit` dihitung sebagai `revenue - cost`. Untuk tahun yang tidak memiliki data, `data` berupa array kosong.

### 4.4 `GET /analytics/health`

Memeriksa apakah keenam service dapat dijangkau.

Contoh response (`200 OK`):

```json
{
  "message": "success",
  "services": {
    "customer": "up",
    "product": "up",
    "store": "up",
    "sales": "up",
    "inventory": "up",
    "promotion": "up"
  }
}
```

Nilainya `up` bila service membalas dengan status 200 dan JSON yang valid, selain itu `down`.

### 4.5 Response error

| Status | Kondisi | Contoh body |
|---|---|---|
| `502` | Sales Service tidak dapat dihubungi atau membalas error | `{"message": "Sales Service unavailable"}` |

---

## 5. Kontrak data dengan service lain

Analytics mengharapkan format berikut dari service lain (disepakati bersama kelompok).

**Format umum:** `{ "message": "success", "data": ... }`. Endpoint daftar mengembalikan `data` berupa array, endpoint detail mengembalikan `data` berupa object.

**Sales Service** (`GET /sales?year=1997&page=1&limit=1000`):

```json
{
  "message": "success",
  "page": 1,
  "limit": 1000,
  "total": 86837,
  "data": [
    {
      "product_id": 952,
      "customer_id": 4021,
      "store_id": 3,
      "date": "1997-01-15",
      "store_sales": 2.85,
      "store_cost": 1.14,
      "unit_sales": 3
    }
  ]
}
```

Jika Sales Service belum mendukung pagination (response tanpa field `page`), Analytics hanya membaca satu halaman agar data tidak terhitung berulang.

**Product Service** (`GET /products/{id}`): minimal `product_id` dan `product_name`.

**Customer Service** (`GET /customers/{id}`): minimal `customer_id` dan `fullname` (atau `fname` dan `lname`).

**Store, Inventory, Promotion:** endpoint daftar cukup mengembalikan status 200 dengan JSON, karena hanya dipakai untuk `health`.

---

## 6. Konfigurasi

Salin `env` menjadi `.env`, lalu isi:

```
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

integration.customerUrl  = http://<alamat-customer-service>
integration.productUrl   = http://<alamat-product-service>
integration.storeUrl     = http://<alamat-store-service>
integration.salesUrl     = http://<alamat-sales-service>
integration.inventoryUrl = http://<alamat-inventory-service>
integration.promotionUrl = http://<alamat-promotion-service>
integration.pageSize     = 1000
integration.maxPages     = 100
```

| Pengaturan | Fungsi |
|---|---|
| `integration.*Url` | Alamat dasar tiap service, tanpa garis miring di akhir |
| `integration.pageSize` | Jumlah baris sales per halaman yang diminta |
| `integration.maxPages` | Batas halaman yang ditarik |

Analytics hanya menarik maksimal `pageSize x maxPages` baris sales. Atur nilainya agar mencukupi jumlah baris pada Sales Service, misalnya `maxPages` = (total baris / `pageSize`) dibulatkan ke atas, ditambah 1.

Analytics tidak memerlukan database, sehingga bagian `database.default.*` pada `.env` boleh dibiarkan atau dihapus.

---

## 7. Cara menjalankan

### Prasyarat

PHP versi **8.2 atau lebih baru** (persyaratan CodeIgniter 4), dengan ekstensi berikut aktif:

- `intl`
- `mbstring`
- `json` (aktif secara bawaan, jangan dimatikan)
- `libcurl` (`curl`), wajib karena Analytics memanggil service lain lewat `CURLRequest`

Ekstensi `mysqlnd` tidak diperlukan karena Analytics tidak memakai database.

Catatan versi PHP: PHP 8.1 sudah melewati masa dukungan (31 Desember 2025), dan PHP 8.2 akan berakhir masa dukungannya pada 31 Desember 2026. Gunakan versi yang masih didukung.

### Menjalankan secara lokal

1. Pastikan file `.env` sudah terisi (bagian 6).
2. Dari folder proyek (yang berisi file `spark`), jalankan:

   ```
   php spark serve
   ```

3. Buka `http://localhost:8080/analytics/health` untuk memastikan service berjalan.

Agar dapat diakses dari perangkat lain dalam satu jaringan, jalankan dengan:

```
php spark serve --host 0.0.0.0 --port 8080
```

lalu izinkan port 8080 di firewall, dan gunakan alamat IP perangkat sebagai `app.baseURL`.

### Catatan deploy: `index.php` ada di folder `public`

Berbeda dengan versi lama CodeIgniter, `index.php` tidak berada di root proyek, melainkan di dalam folder `public` demi keamanan dan pemisahan komponen. Karena itu, web server atau hosting harus diarahkan ke folder `public`, bukan ke root proyek. Mengarahkan ke root proyek akan membuat kode aplikasi dan framework ikut terbuka ke publik. Cara terbaik adalah memakai virtual host yang menunjuk ke `public`; pada hosting yang tidak mengizinkan hal itu, ubah *document root* ke `public` lewat panel hosting.

---

## 8. Struktur proyek (bagian yang ditambahkan)

```
app/
├── Config/
│   ├── Integration.php           konfigurasi alamat service dan paginasi
│   └── Routes.php                route /analytics/*
├── Controllers/
│   └── AnalyticsController.php   endpoint analitik dan health
└── Libraries/
    ├── ServiceClient.php         klien HTTP umum
    ├── SalesGateway.php          akses dan normalisasi data Sales
    ├── ProductGateway.php        akses data Product
    ├── CustomerGateway.php       akses data Customer
    └── AnalyticsCalculator.php   logika agregasi
```

---

## 9. Perilaku dan batasan

- **Cache:** data sales hasil tarikan disimpan di cache selama 5 menit per tahun. Untuk menghapusnya, jalankan `php spark cache:clear`. Setelah mengubah data di Sales Service, hapus cache agar hasil terbaru terbaca.
- **Ketahanan:** bila Sales Service gagal dihubungi pada halaman pertama, endpoint analitik membalas `502`. Bila gagal di tengah penarikan, Analytics memakai data yang sudah terkumpul. Kegagalan Product atau Customer Service hanya membuat nama bernilai `null`.
- **Cakupan data:** hasil analitik hanya mencerminkan baris sales yang berhasil ditarik (maksimal `pageSize x maxPages`).
- **Timeout:** setiap pemanggilan ke service lain dibatasi 15 detik.
- **Autentikasi:** belum ada (menunggu konfirmasi kebutuhan dari dosen atau asisten).

---

## 10. Pengujian

Pengujian dilakukan dengan Postman (collection `Foodmart`):

1. `health`: keenam service berstatus `up`.
2. `top-products`, `top-customers`, `sales-trend` untuk `year=1997`: status 200 dan struktur sesuai bagian 4.
3. Tahun tanpa data (`year=1998` pada data uji): status 200 dengan `data` kosong.
4. Sales Service tanpa pagination: hasil sama dengan Sales yang mendukung pagination.
5. Sales Service dimatikan (setelah `php spark cache:clear`): `health` menampilkan `down` dan endpoint analitik membalas `502`.

**Verifikasi angka.** Hasil API dibandingkan dengan query langsung pada database Foodmart (`sales_fact_1997`):

```sql
-- Produk terlaris
SELECT product_id, SUM(unit_sales) AS units, ROUND(SUM(store_sales), 2) AS revenue
FROM sales_fact_1997
GROUP BY product_id
ORDER BY units DESC, product_id ASC
LIMIT 10;

-- Pelanggan terbaik
SELECT customer_id, ROUND(SUM(store_sales), 2) AS spent, COUNT(*) AS orders
FROM sales_fact_1997
GROUP BY customer_id
ORDER BY spent DESC, customer_id ASC
LIMIT 10;

-- Tren penjualan bulanan
SELECT t.the_year, t.month_of_year,
       ROUND(SUM(f.store_sales), 2) AS revenue,
       ROUND(SUM(f.store_cost), 2)  AS cost,
       SUM(f.unit_sales)            AS units
FROM sales_fact_1997 f
JOIN time_by_day t ON f.time_id = t.time_id
GROUP BY t.the_year, t.month_of_year
ORDER BY t.the_year, t.month_of_year;
```

Pemetaan kolom SQL ke field API:

| SQL | API |
|---|---|
| `units` | `total_units` |
| `revenue` (produk) | `total_revenue` |
| `spent` | `total_spent` |
| `orders` | `total_orders` |
| `the_year` + `month_of_year` | `period` (`YYYY-MM`) |

---

## 11. Status integrasi

| Service | Status |
|---|---|
| Sales | [ ] terhubung dan terverifikasi |
| Product | [ ] terhubung dan terverifikasi |
| Customer | [ ] terhubung dan terverifikasi |
| Store | [ ] terhubung (health) |
| Inventory | [ ] terhubung (health) |
| Promotion | [ ] terhubung (health) |
| Executive Dashboard | [ ] mengonsumsi endpoint Analytics |

---

## 12. Tentang CodeIgniter 4

Proyek ini dibangun di atas **CodeIgniter 4**, framework PHP full-stack yang ringan, cepat, fleksibel, dan aman. CodeIgniter dirilis dengan lisensi MIT. Informasi lebih lanjut tersedia di [situs resmi](https://codeigniter.com).

Dokumentasi:

- [User Guide CodeIgniter 4](https://codeigniter.com/user_guide/) sebagai dokumentasi utama.
- [User Guide versi pengembangan](https://codeigniter4.github.io/CodeIgniter4/), masih dalam proses penyempurnaan.
- [Dokumentasi API](https://codeigniter4.github.io/api/) untuk komponen framework.

Pelaporan bug atau kontribusi untuk framework itu sendiri dilakukan melalui [repositori resmi CodeIgniter 4](https://github.com/codeigniter4/CodeIgniter4), bukan melalui repositori proyek ini.