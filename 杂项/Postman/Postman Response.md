`pm.response`变量用于引用当前的响应数据。

## 一、格式化响应数据

* Text

    ```JavaScript
    const responseText = pm.response.text();
    ```

* JSON

    ```JavaScript
    const responseJson = pm.response.json();
    ```
    
    > 如果响应数据不是有效的 JSON 字符串，则会抛出异常

* XML

    ```JavaScript
    const responseJson = xml2Json(pm.response.text());
    ```

* CSV

    ```JavaScript
    const parse = require('csv-parse/lib/sync');
    const responseJson = parse(pm.response.text());
    ```

* HTML

    ```JavaScript
    const $ = cheerio.load(pm.response.text());
    // output the html for testing
    console.log($.html());
    ```


