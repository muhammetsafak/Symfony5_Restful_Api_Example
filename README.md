# Symfony 5 Restful API Example

Not : Bu repo hızlıca oluşturulmuş basit bir **ÖRNEK** projedir. Alıştırma niteliğindedir.

## Kurulum

`.env` dosyasından veritabanı bağlantı bilgilerinizi ayarlayın. Yapısı şuna benzer;

```
DATABASE_URL="mysql://[KullanıcıAdı]:[Şifre]@[HostnameOrIP]:[Port]/[VeritabanıAdı]?serverVersion=5.1&charset=utf8mb4"
```

Sonrasında terminal üzerinde aşağıdaki komutları sırayla yürütün;

```
composer update
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

Aşağıdaki komut deneme yapabileceğiniz basit bir sunucuyu `http://127.0.0.1:8000` adresinde ayağa kaldırır.

```
symfony server:start
```

**Not :** Bu komut için [Symfony CLI](https://symfony.com/download) cihazınızda kurulu olmalıdır.



### 7 Temel İşlevin Kullanımı

**Yeni Kullanıcı Kaydı**
```
POST /register
Content-Type: application/json

{
    "name": "Müşteri Adı",
    "mail": "mail@example.com",
    "password": "aBcy543"
}
```

Başarı durumda şöyle bir cevap döner;

```
HTTP/1.1 200 OK
Content-Type: application/json

{
    "status": 200,
    "success": "Kullanıcı başarılı bir şekilde oluşturuldu."
}
```

**Kullanıcı Giriş İşlemi**

```
GET /login_check
Content-Type: application/json

{
    "mail": "ahmet@example.com",
    "password": "ahmet"
}
```

Giriş Başarılı İse Şuna Benzer Bir Cevap Döner:

```
HTTP/1.1 200 OK
Content-Type: application/json

{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2NTIzOTM2NTMsImV4cCI6MTY1MjM5NzI1Mywicm9sZXMiOlsiUk9MRV9VU0VSIl0sIm1haWwiOiJhaG1ldEBleGFtcGxlLmNvbSJ9.glPo6KjX7oiIHmMvRtpoxytSPP2lNDpddIkS6PE5jGzykJNNAgr5iEYXqCjwaKnicXFFtXYZAzwZ2oeQZWYDSGafuVI52FxtSwDO5lP8H5gtd4V_PvbF5koeodZ7ftm9IrwXuo7ze4G6mhFXZk6jWwoan_Jb3EzduoYzExQrVAXARpEcwGHDNAEJ_xi18jq2VV1C2NEGNumufDqnwLyxWq4JvBwrnLBwqT53UFhi1tngy6rDPUFbQDOTWkOL3w2PPE1fBwT6FNzKvWdYRE55Nt5A5LPMmJIVFZXIkFoHc47z3NbcBdM5MqIEF00mbC0VXiz9Aydt10cLdeyojbxxMRI7holzifccux71T6kLuP7-GGkkEnn3jkBsLL2xlFTIXukB6d9DgkcTdvCBKkpDYETXRaI60t_WKZ0BvjvbDcDLQBgZDw4hAXEHRFPtNuCbwaIpGdXOY4Ys-Szp0DgSiOAckIy4stEftACE4c30T6miNwEqHcV2RXsVn4HlIflhSR85lYXjHupiLAidqGedx5joZ6qzKkgTvXo8lRMsOkDPgPOIBkiU3-EpxhOYTlcsrMNPzQWpslKRGSf0LywLCBHcP3ieIMAqMsFddZw-pVyW1Tq1GQilc_ptgb6vnxqFHLZVZi0i1kqQPNTo1ta_mYgZVnHREAm1KdQgreil5DI"
}
```

**Tüm Siparişlerini Listeleme**

_Sadece kendi siparişlerini görebilir_

```
GET /list
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ...

```

Başarılı ise şuna benzer bir cevap döndürmelidir;

```
HTTP/1.1 200 OK
Content-Type: application/json

{
    "6": {
        "orderCode": 6,
        "address": "295 Jones Stravenue Suite 787\nColleentown, NJ 98640",
        "products": {
            "26": {
                "productId": 26,
                "quantity": 2,
                "name": "porro"
            },
            "38": {
                "productId": 38,
                "quantity": 1,
                "name": "nihil"
            },
            "5": {
                "productId": 5,
                "quantity": 2,
                "name": "optio"
            },
            "40": {
                "productId": 40,
                "quantity": 1,
                "name": "ut"
            },
            "31": {
                "productId": 31,
                "quantity": 1,
                "name": "quaerat"
            }
        },
        "status": {
            "code": 1,
            "msg": "Gönderildi",
            "shippingDate": {
                "date": "2022-05-14 01:52:34.000000",
                "timezone_type": 3,
                "timezone": "Europe/Berlin"
            }
        }
    },
    "7": {
        "orderCode": 7,
        "address": "8615 Jakubowski Fords\nSouth Deltaport, AK 65127-8869",
        "products": {
            "13": {
                "productId": 13,
                "quantity": 2,
                "name": "voluptatem"
            },
            "14": {
                "productId": 14,
                "quantity": 2,
                "name": "et"
            },
            "9": {
                "productId": 9,
                "quantity": 1,
                "name": "iste"
            },
            "26": {
                "productId": 26,
                "quantity": 2
            }
        },
        "status": {
            "code": 0,
            "msg": "Hazırlanıyor"
        }
    },
    "21": {
        "orderCode": 21,
        "address": "Amasya",
        "products": {
            "5": {
                "productId": 5,
                "quantity": 1
            },
            "10": {
                "productId": 10,
                "quantity": 1,
                "name": "exercitationem"
            },
            "32": {
                "productId": 32,
                "quantity": 2,
                "name": "consectetur"
            }
        },
        "status": {
            "code": 0,
            "msg": "Hazırlanıyor"
        }
    }
}
```

**Tek Bir Sipariş Detayı**

```
GET /show/5
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ...

```

Eğer başarılı ve ilgili sipariş size aitse şuna benzer bir cevap döner.

```
HTTP/1.1 200 OK
Content-Type: application/json

{
    "19": {
        "orderCode": 19,
        "address": "924 Daphne Parkways Suite 412\nEast Vicentetown, CA 54366-1767",
        "products": {
            "46": {
                "productId": 46,
                "quantity": 1,
                "name": "sint"
            },
            "47": {
                "productId": 47,
                "quantity": 2,
                "name": "nisi"
            },
            "30": {
                "productId": 30,
                "quantity": 1,
                "name": "repudiandae"
            }
        },
        "status": {
            "code": 0,
            "msg": "Hazırlanıyor"
        }
    }
}
```

**Yeni Sipariş Oluşturma**

```
POST /new
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ...
Content-Type: application/json

{
    "address": "İstanbul",
    "products": [
        {
            "id": 5,
            "quantity": 3
        },
        {
            "id": 10,
            "quantity": 5
        }
    ]
}
```

Eğer bazı ürünler stok olmaması gibi herhangi bir sebepten dolayı eklenemezse `errors` dizine bir hata mesajı eklenir. 

Hiçbir ürün eklenemezse sipariş oluşturulması başarısız olacaktır.

```
HTTP/1.1 201 Created
Content-Type: application/json

{
    "status": {
        "status": 1,
        "msg": "Ok!"
    },
    "errors": [],
    "orderCode": 21,
    "detailUrl": "/show/21"
}
```

`status`, Siparişin oluşturulup oluşturulamaması ile ilgilidir. Hatalar `errors` ile alınmalıdır.

**Sipariş Güncelleme**

```
PUT /update/21
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ...
Content-Type: application/json

{
  "address": "İzmir",
  "products": [
    {
      "id": 5,
      "quantity": 3
    },
    {
      "id": 10,
      "quantity": 5
    },
    {
      "id": 2,
      "quantity": 3
    }
  ]
}
```

Sadece `address` belirtilerek yanlızca adres bilgisi güncellenebilir ancak eğer `products` belirtilirse sipariş içeriği yeninden tanımlanır. Miktarları azaltılabilir, stok durumuna göre arttırılabilir. Yeni ürün eklenebilir ya da bir ürün listeden çıkarılabilir.

```
HTTP/1.1 201 Created
Content-Type: application/json

{
    "orderCode": 19,
    "status": {
        "status": 1,
        "msg": "Ok!"
    },
    "errors": [],
    "process": {
        "addressUpdate": 1,
        "productsUpdate": 1
    },
    "detailUrl": "/show/19"
}
```

**Sipariş Silme**

```
DELETE /delete/21
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ...

```

İşlem başarılıysa;

```
HTTP/1.1 200 Ok
Content-Type: application/json

{
    "status": {
        "code": 1,
        "msg": "Siparişiniz (#19) silindi"
    },
    "errors": []
}
```

Hepsi bu kadar.