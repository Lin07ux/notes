1. 先用 php 生成一对公钥和私钥

```php
<?php
$res = openssl_pkey_new();
openssl_pkey_export($res, $pri);
$d = openssl_pkey_get_details($res);
$pub = $d['key'];
var_dump($pri, $pub);
```

将会一次输出私钥和公钥的 pem 字串，如：

```
# 私钥
string(916) "-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAKs124okAnX5jh1Q
wsETi80b4ZyYlYUSsAtvS7ZG+GSLAox24TKNwWIy5cUdKfK/5QEJjZ0S8LjRSYCG
to9LkwQTQrKY8BBXVCQQS3VXRqz4on9bmcGSyLMK6vQxt9NnJpk6aBmJE7z35+8z
TTLwdUbYaMAcYqrsYDfgBSNBgwbDAgMBAAECgYBvolJfpFMmcW3El6AlHIWPW5qj
7KmxdxnqmssXMvdLN4iV5f24ZM2vJdjDydxMN+st3fjEblEfcPcoIq5uiKx2bnO3
f9g0yt+qUoXdz4LK6jV1NfuAZDcnBm7CXfE7BDREgyiRg4ZXIvp4L0Y2zBMdFTmg
LKkXb2M1P3pK53UbwQJBANupqPXFQjdHvAa3voT+UI8OhmLbw/Vyh6Ii1glwMfyq
VS2lAakj9d1hqLYZvw+eNeZBnzZNPuMJg5aj/WKUqasCQQDHiFLS7Yb1rbhfjmRt
ZL4zXuvX1hVjTNo2TeZwPniGpYa+QHcauDDep5C9q//n+D+ZtkbkECxpOVhrUHSI
KO9JAkEApl3lHd98uymVevEHVurWUMMLfSw9SlSn7WC9AwD6mwbW5G8oxtX5jOY+
RU1Sq52D7RSeZR40FvhJtXoWmudFaQJAc8E+a2epF/YENEtjL6N2RE8Y+0oTdlwr
a72dphhXy6VTmiPD9RhOIZ2MdrzF5Uk7fG0mi0Bmw1SlWvx93lABIQJBALXPeoNa
b4dQkRkfqCHtyrWOeRcC1Y6FyTfNj+cRBzdIcmFc21hxj6HKy2M6/XGI3rAE5L+B
mmlSmN1enhoCUqc=
-----END PRIVATE KEY-----
"

# 公钥
string(272) "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCrNduKJAJ1+Y4dUMLBE4vNG+Gc
mJWFErALb0u2RvhkiwKMduEyjcFiMuXFHSnyv+UBCY2dEvC40UmAhraPS5MEE0Ky
mPAQV1QkEEt1V0as+KJ/W5nBksizCur0MbfTZyaZOmgZiRO89+fvM00y8HVG2GjA
HGKq7GA34AUjQYMGwwIDAQAB
-----END PUBLIC KEY-----
"
```


2. 保存好自己的私钥，把公钥可以公开给别人。如果需要对某数据进行签名，证明那数据是从你这里发出的，就需要用私钥：

```php
<?php
$res = openssl_pkey_get_private($pri);
if (openssl_sign('hello', $out, $res)) {
    var_dump(base64_encode($out));
}
```

上例中`$pri`为自己的私钥，`'hello'`为待签名的数据，如果签名成功，最后输出为 base64 编码后的签名，如：

```
j19H+C/NQEcyowezOQ+gmGi2UoPJNXyJ+KwpkEzJ5u4qaRD3cY4qhfFfIosypypwJTJ4LjRYOIPNQMQm6ICj2nMdGfn/p/pp7il+xGz2aUWdOXkJFgIc/PGC95C9sLH04Tc6QSuV5IMd9rjBjyv+ieokMLFm9cmtN2hGag9vq1s=
```


3. 别人收到你的数据 'hello' 和签名字串，想验证这是从你发来的数据的话，用你公开的公钥验证：

```php
$sig = base64_decode($sig);
$res = openssl_pkey_get_public($pubkey);
if (openssl_verify('hello', $sig, $res) === 1) {
    // 通过验证
}
```

上例中刚开始的`$sig`为之前你 base64 编码过的签名，`$pubkey`为你的公钥。

php 中这种签名使用的是 RSA 算法；数字签名可以在单点登录等系统中派上用场。



