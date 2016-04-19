`form`元素的`enctype`属性，规定了对表单提交给服务器时表单数据编码的内容类型(Content Type)。

> [HTML 4.01规范的Form章节](http://www.w3.org/TR/1999/REC-html401-19991224/interact/forms.html#adef-enctype)
> enctype = content-type [CI]
> This attribute specifies the content type used to submit the form to the server.

### 可取值
HTML5 中`enctype`具有以下三种可取的值，其中最后一项`text/plain`是相比4.01新增的。

- `application/x-www-form-urlencoded` **默认的编码类型**。使用该类型时，会将表单数据中 非字母数字的字符转换成转义字符 ，如"%HH"，然后组合成这种形式 `key1=value1&key2=value2`；所以后端在取数据后，要进行解码。
- `multipart/form-data` 该类型用于**高效传输文件、非ASCII数据和二进制数据**，将表单数据逐项地分成不同的部分，用指定的分割符分割每一部分。每一部分都拥有 `Content-Disposition`头部，指定了该表单项的键名和一些其他信息；并且每一部分都有可选的`Content-Type`，不特殊指定就为`text/plain`。

    下面是一个采用 multipart/form-data 编码类型时传输的数据的例子：
    
```
Content-Type: multipart/form-data; boundary=AaB03x   
--AaB03x   
Content-Disposition: form-data; name="submit-name"   
Larry   
--AaB03x   
Content-Disposition: form-data; name="files"; filename="file1.txt"   
Content-Type: text/plain   
... contents of file1.txt ...       
--AaB03x--
```

- `text/plain` 按照键值对排列表单数据`key1=value1\r\nkey2=value2`，不进行转义。

**注意1**：当`enctype`取默认值或`text/plain`时，若表单中有文件，则只留文件名；

**注意2**：一般来说，`method`和`enctype`是两个不同的互不影响的属性，但在传文件时，`method`必须要指定为`POST`，否则文件只剩下`filename`了。

**注意3**：当没有传文件时，即便指定的`enctype`值为`multipart/form-data`，也会自动被改回默认的`application/x-www-form-urlencoded`。

当`enctype`的取值不是上面三个值中的任何一个的时候，均会默认转成默认取值`application/x-www-form-urlencoded`。





