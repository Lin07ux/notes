## json_encode()、json_decode()
json_encode 后保持中文编码函数：`json_encode("试试", JSON_UNESCAPED_UNICODE);`

json_decode 默认情况下，会把 json 解码成一个对象，如果要转成关联数组，则需要设置第二个参数为 true：`json_decode($arr, true);`

