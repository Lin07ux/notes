## 一、响应结果判断

可以针对响应的响应体、状态、响应头、Cookie、响应时间等信息进行验证。

### 1.1 响应体验证

```JavaScript
pm.test("Person is Jane", () => {
    const responseJson = pm.response.json();

    pm.expect(responseJson.name).to.eql("Jane");
    pm.expect(responseJson.age).to.eql(23);
});
```

### 1.2 状态码验证

```JavaScript
// 判断状态码为具体的值
pm.test("Status code is 201", () => {
    pm.response.to.have.status(201);
});

// 判断状态码为一系列值中的一个
pm.test("Successful Post request", () => {
    pm.except(pm.response.code).to.be.oneOf([201, 202]);
});

// 判断状态为状态描述
pm.test("Status code name has string", () => {
    pm.response.to.have.status("Created");
});
```

### 1.3 响应头验证

```JavaScript
pm.test("Content-Type header is present", () => {
    pm.response.to.have.header("Content-Type");
    pm.expect(pm.response.headers.get('Content-Type')).to.eql('application/json');
});
```

### 1.4 Cookie 验证

```JavaScript
pm.test("Cookie JSESSIONID is present", () => {
    pm.expect(pm.cookies.has('JSESSIONID')).to.be.true;
    pm.expect(pm.cookies.get('isLoggedIn')).to.eql('1');
});
```

### 1.5 环境变量验证

```JavaScript
pm.test("Check the active environment", () => {
    pm.expect(pm.environment.name).to.eql("Production");
});
```

### 1.5 响应时间验证

```JavaScript
pm.test("Response time is less than 200ms", () => {
    pm.expect(pm.response.responseTime).to.be.below(200);
});
```

## 二、通用验证

除了上面针对响应各个层面的验证之外，还能针对更多的数据做更多的方式的验证。

### 2.1 判断响应数据的值与变量的关系

对于响应的数据，可以判断其与 Postman 环境变量、集合变量等之间的关系：

```JavaScript
pm.test("Response property matches environment variable", function () {
    pm.expect(pm.response.json().name).to.eql(pm.environment.get('name'));
});
```

### 2.2 判断数据类型

```JavaScript
/* response has this structure:
{
  "name": "Jane",
  "age": 29,
  "hobbies": [
    "skating",
    "painting"
  ],
  "email": null
}
*/

const jsonData = pm.response.json();

pm.test("Test data type of the response", () => {
    pm.expect(jsonData).to.be.an("object");
    pm.expect(jsonData.name)to.be.a("string");
    pm.expect(jsonData.age).to.be.a("number");
    pm.expect(jsonData.hobbies).to.be.an("array");
    pm.expect(jsonData.website).to.be.undefined;
    pm.expect(jsonData.email).to.be.null;
});
```

### 2.3 验证数组的属性

可以验证数组是否是空数组、是否包含某些元素。

```JavaScript
/*
response has this structure:
{
  "errors": [],
  "areas": [ "goods", "services" ],
  "settings": [
    {
      "type": "notification",
      "detail": [ "email", "sms" ]
    },
    {
      "type": "visual",
      "detail": [ "light", "large" ]
    }
  ]
}
*/

const jsonData = pm.response.json();

pm.test("Test array properties", () => {
    // errors array is empty
    pm.expect(jsonData.errors).to.be.empty;
    
    // areas includes "goods"
    pm.expect(jsonData.areas).to.include("goods");
    
    // get the notification settings object
    const notificationSettings = jsonData.settings.find(m => m.type === "notification");
    
    // notificationSettings is an object
    pm.expect(notificationSettings).to.be.an("object", "Could not find the setting");
    
    // detail array of notificationSettings should include "sms"
    pm.expect(notificationSettings.detail).to.include("sms");
    
    // detail array of notificationSettings should all listed
    pm.expect(notificationSettings.detail).to.have.members(["email", "sms"]);
});
```

> 列表中元素的顺序不影响`.members()`的测试结果。


### 2.4 验证对象的属性

```JavaScript
pm.test("Test object properties", () => {
    const obj = { a: 1, b: 2 };
    
    pm.expect(obj).to.have.all.keys('a', 'b');
    pm.expect(obj).to.have.any.keys('a', 'c');
    pm.expect(obj).to.not.have.any.keys('c', 'd');
    pm.expect(obj).to.have.property('a');
    pm.expect(obj).to.be.an('object').that.has.all.keys('a', 'b');
});
```

> * 这里的“对象”可以为`object`、`set`、`array`或者`map`。
> * 如果`.keys()`在执行前未指定`.all`或者`.any`，则默认为`.all`。
> * 由于`.keys()`的判断是基于目标的类别的，所以建议在使用`.keys()`之前使用`.a`或`.an`对目标进行类别判断。

### 2.5 判断值在指定的集合中

```JavaScript
pm.test("Value is in valid list", () => {
    pm.expect(pm.response.json().type).to.be.oneOf(["Subscriber", "Customer", "User"]);
});
```

### 2.6 判断对象的包含

```JavaScript
/*
response has the following structure:
{
  "id": "d8893057-3e91-4cdd-a36f-a0af460b6373",
  "created": true,
  "errors": []
}
*/

pm.test("Object is contained", () => {
    const expectedObject = { "created": true, "errors": [] };

    pm.expect(pm.response.json()).to.deep.include(expectedObject);
});
```

> 使用`.deep`可以使后面`.equal`、`.include`、`.members`、`.keys`、`.property`在判断时使用深入比较（松散比较）替代严格比较(`===`)。
> 
> 虽然`.eql()`也使用松散比较，但是`.deep.equal`的松散比较会使得断言链后面的判断也都变成松散比较。

