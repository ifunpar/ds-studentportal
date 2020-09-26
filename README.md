# Student Portal Wrapper for PHP

UNPAR's Student Portal Wrapper as PHP Library. This lib will help your app to talk to UNPAR's Student portal programatically. Currently we're only able to get student info (Marks, Schedule, etc). Check the issue tab for more information regarding certain issues.

All the documentation will be written in mixed languages (either in Bahasa or English).

---

Wrapper Student Portal sebagai Library PHP. Lib ini akan membantu aplikasi Anda untuk berkomunikasi dengan Student Portal UNPAR. Saat ini kami hanya mendukung untuk pengambilan informasi mahasiswa terkait (nilai, jadwal, dan lain lain). Cek bagian issue untuk membantu (atau melihat diskusi) menambahkan fitur.

Dokumentasi akan ditulis dengan bahasa yang tercampur (entah dalam Bahasa Indonesia ataupun Bahasa Inggris).

# Penggunaan

## Pemasangan

Untuk menggunakan library ini, Anda diharuskan untuk memasang plugin DeSSO terlebih dahulu. Anda dapat melakukannya dengan mengeksekusi perintah berikut:

```shell
$ composer install chez14/desso chez14/ds-studentportal
```

DeSSO bertindak sebagai petugas yang meloginkan SSO, sedangkan Library ini bertindah sebagai layanan yang akan dibantu diloginkan via SSO. Untuk informasi lebih lanjut, Anda dapat melihat dokumentasi dari DeSSO itu sendiri.

 ## Menggunakan API

Untuk menggunakan library berikut, Anda diharuskan membuat instansi baru dari DeSSO, memasukkan informasi login mahasiswa tersebut, lalu melakukan login untuk SSO.

```php

use Chez14\Desso\Client;

$client = new Client();
$client->setCredential("7316xxx@student.unpar.ac.id", "dont-forget-3oct11");
if (!$client->login()) {
	throw new Exception("Whoops! Login tidak berhasil.");
}
```

Selanjutnya Anda hanya cukup melakukan login di level *service* dengan memanggil `serviceLogin`.

```php
use Chez14\Desso\Services\StudentPortal;

$stupor = new StudentPortal();
if(!$client->serviceLogin($stupor)){
 	throw new Exception("Whoops, login ke student portal tidak berhasil.");
}
```

Selesai! Anda dapat menggunakan library ini.

```php
$profile = $stupor->getProfile()->getDatas();
var_dump($profile);
// Array [....]
```

# Methods, Functions & Classes

**Dokumentasi daftar method, fungsi dan kelas dapat dilihat pada alamat berikut:** [http://ifunpar.github.io/ds-studentportal](http://ifunpar.github.io/ds-studentportal).

Tautan kilat:
- Kelas Student Portal: \
  https://ifunpar.github.io/ds-studentportal/classes/Chez14.Desso.Services.StudentPortal.html
  
- Daftar API yang disupport oleh library ini: \
  https://ifunpar.github.io/ds-studentportal/namespaces/Chez14.Desso.Services.Data.html
  
- https://ifunpar.github.io/ds-studentportal/namespaces/Chez14.Desso.Services.html


# Lisensi

[MIT](LICENSE).

---

Untuk informasi dan pertanyaan lebih lanjut, Anda dapat menghubungi saya (Chris) via:

- Issue
- Twitter (cek profil saya di @chez14)
- Email (cek bagian [lisensi](LICENSE), atau profil Github saya di @chez14)