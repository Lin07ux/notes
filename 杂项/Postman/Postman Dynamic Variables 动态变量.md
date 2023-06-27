> 转摘：[Dynamic variables](https://learning.postman.com/docs/writing-scripts/script-references/variables-list/)

Postman 的脚本中，可以使用一些预定义的变量来生成随机数据，底层使用的是 [faker library](https://www.npmjs.com/package/faker) 库。可以通过动态变量在每次请求时生成随机的姓名、地址、Email 地址等等数据。

这些动态变量和普通变量的使用方式一样，可以在 Script 脚本中使用，也可以在请求的 url、参数中使用。但是动态变量是以`$`符号开头，比如`$guid`、`$timestamp`等。这些动态变量会在执行的时候被动态的赋值。

> 在 Pre-request 脚本中使用动态变量需要使用`pm.variables.replaceIn()`方法，比如：`pm.variables.replaceIn('{{$randomFirstName}}')`。

![](http://cnd.qiniu.lin07ux.cn/markdown/ISBuCp-20210122115641.png)

![](http://cnd.qiniu.lin07ux.cn/markdown/yhjYGc-20210122115722.png)

动态变量列表如下：

### 1. 通用动态变量

* `$guid` 生成 uuid-v4 样式 GUID，如：`"611c2e81-2ccb-42d8-9ddc-2d0bfa65c1b4"`。
* `$randomUUID` 生成随机的 36 位 UUID，如：`"727131a2-2717-44ad-ab02-006587e947dc"`。
* `$timestamp` 生成当前的时间戳，单位是秒，如：`1611262054`。
* `$isoTimestamp` 当前的 ISO 时间字符串，如：`2020-06-09T21:10:36.177Z`。

### 2. 文本、数字、颜色

* `$randomAlphaNumeric` 生成随机的单个英文字符和数字，如：`6`、`G`、`z`。
* `$randomBoolean` 生成随机的 Boolean 值，如：`true`、`false`。
* `$randomInt` 生成随机的整数，范围是 0 ~ 1000，如：`1`、`804`、`200`。
* `$randomColor` 生成随机颜色（英文名称），如：`red`、`yellow`、`fuchsia`。
* `$randomHexColor` 生成随机的十六进制颜色，如：`"#47594a"`、`"#431e48"`。
* `$randomAbbreviation` 生成随机的缩写，如：`SQL`、`PCI`、`JSON`。

### 3. 网络和 IP 地址

* `$randomIP` 生成随机的 IPv4 地址，如：`241.102.234.100`。
* `$randomIPV6` 生成随机的 IPv6 地址，如：`dbe2:7ae6:119b:c161:1560:6dda:3a9b:90a9`。
* `$randomMACAddress` 生成随机的 MAC 地址，如：`33:d4:68:5f:b4:c7`。
* `$randomPassword` 生成随机密码，如：`"t9iXe7COoDKv8k3"`。
* `$randomLocale` 生成随机的两位字母的语言类型，如：`"ny"`、`"sr"`等。
* `$randomUserAgent` 生成随机的 User Agent，如：`Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.9.8; rv:15.6) Gecko/20100101 Firefox/15.6.6`。
* `$randomProtocol` 生成随机的协议，如：`"http"`、`"https"`。
* `$randomSemver` 生成随机的 Semantic 版本号，如：`7.0.5`、`2.5.8`。

### 4. 名称

* `$randomFirstName` 生成随机的 First 名称，如：`Ethan`、`Chandler`。
* `$randomLastName` 生成随机的 Last 名称，如：`Schaden`、`Schneider`。
* `$randomFullName` 生成随机的全名称，如：`Connie Runolfsdottir`、`Sylvan Fay`。
* `$randomNamePrefix` 生成随机的名称前缀，如：`Dr.`、`Ms.`、`Mr.`。
* `$randomNameSuffix` 生成随机的名称尾缀，如：`I`、`MD`、`DDS`。

### 5. 职业

* `$randomJobArea` 生成随机的职位区域，如：`Mobility`、`Intranet`、`Configuration`。
* `$randomJobDescriptor` 生成随机的职位描述，如：`Forward`、`Corporate`、`Senior`。
* `$randomJobTitle` 生成随机的职位头衔，如：`International Creative Liaison`、`Product Factors Officer`。
* `$randomJobType` 生成随机的职位类型，如：`Supervisor`、`Manager`、`Coordinator`。

### 6. 手机号、地址和地址

* `$randomPhoneNumber` 生成随机的 10 位手机号，如：`700-008-5275`、`494-261-3424`。
* `$randomPhoneNumberExt` 生成随机的 12 位手机号，如：`27-199-983-3864`、`99-841-448-2775`。
* `$randomCity` 生成随机的城市名称，如：`Spinkahaven`、`Korbinburgh`。
* `$randomStreetName` 生成随机的街道名称，如：`Kuhic Island`、`General Street`。
* `$randomStreetAddress` 生成随机的街道地址，如：`5742 Harvey Streets`。
* `$randomCountry` 生成随机的国家地址，如：`Lao People's Democratic Republic`、`Kazakhstan`。
* `$randomCountryCode` 生成随机的国家编码，如：`CV`、`MD`、`TD`。
* `$randomLatitude` 生成随机的纬度信息，如：`55.2099`、`27.3644`、`-84.7514`。
* `$randomLongitude` 生成随机的经度信息，如：`40.6609`、`171.7139`、`-159.9757`。

### 7. 日期时间

* `$randomDateFuture` 生成随机的未来时间，如：`Tue Mar 17 2020 13:11:50 GMT+0530 (India Standard Time)`。
* `$randomDatePast` 生成随机的过去时间，如：`Sat Mar 02 2019 09:09:26 GMT+0530 (India Standard Time)`。
* `$randomDateRecent` 生成随机的近期时间，如：`Tue Jul 09 2019 23:12:37 GMT+0530 (India Standard Time)`。
* `$randomWeekday` 生成随机的周几，如：`Thursday`、`Friday`。
* `$randomMonth` 生成随机的月份，如：`February`、`January`。

### 8. 域名、邮箱和账户名

* `$randomDomainName` 生成随机的域名，如：`gracie.biz`。
* `$randomDomainSuffix` 生成随机的顶级域名，如：`org`、`net`。
* `$randomDomainWord` 生成随机的不合法的域名，如：`gwen`、`jaden`。
* `$randomEmail` 生成随机的邮箱，如：`Pablo62@gmail.com`、`Ruthe42@hotmail.com`。
* `$randomExampleEmail` 生成随机的、在`example.com`域名中的邮箱，如：`Talon28@example.com`、`Quinten_Kerluke45@example.net`。
* `$randomUserName` 生成随机的账户名，如：`Lottie.Smitham24`、`Alia99`。
* `$randomUrl` 生成随机的 url，如：`https://anais.net`、`https://tristin.net`。

### 9. 图片

* `$randomAvatarImage` 生成随机的头像图片，如：`https://s3.amazonaws.com/uifaces/faces/twitter/johnsmithagency/128.jpg`。
* `$randomImageUrl` 生成随机的图片地址，如：`http://lorempixel.com/640/480`。
* `$randomAbstractImage` 生成随机的抽象图片，如：`http://lorempixel.com/640/480/abstract`。
* `$randomAnimalsImage` 生成随机的动物图片，如：`http://lorempixel.com/640/480/animals`。
* `$randomBusinessImage` 生成随机的商业图片，如：`http://lorempixel.com/640/480/business`。
* `$randomCatsImage` 生成随机的猫咪图片，如：`http://lorempixel.com/640/480/cats`。
* `$randomCityImage` 生成随机的城市图片，如：`http://lorempixel.com/640/480/city`。
* `$randomFoodImage` 生成随机的事物图片，如：`http://lorempixel.com/640/480/food`。
* `$randomNightlifeImage` 生成随机的夜景图片，如：`http://lorempixel.com/640/480/nightlife`。
* `$randomFashionImage` 生成随机的时尚图片，如：`http://lorempixel.com/640/480/fashion`。
* `$randomPeopleImage` 生成随机的人物图片，如：`http://lorempixel.com/640/480/people`。
* `$randomNatureImage` 生成随机的自然风景图片，如：`http://lorempixel.com/640/480/nature`
* `$randomSportsImage` 生成随机的运动图片，如：`http://lorempixel.com/640/480/sports`。
* `$randomTransportImage` 生成随机的运输图片，如：`http://lorempixel.com/640/480/transport`。
* `$randomImageDataUri` 生成随机的图片 data URI 数据，如：`data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20version%3D%221.1%22%20baseProfile%3D%22full%22%20width%3D%22undefined%22%20height%3D%22undefined%22%3E%20%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22grey%22%2F%3E%20%20%3Ctext%20x%3D%220%22%20y%3D%2220%22%20font-size%3D%2220%22%20text-anchor%3D%22start%22%20fill%3D%22white%22%3Eundefinedxundefined%3C%2Ftext%3E%20%3C%2Fsvg%3E`。

### 10. 文件和路径

* `$randomFileName` 生成随机的文件名，如：`neural_sri_lanka_rupee_gloves.gdoc`。
* `$randomFileType` 生成随机的文件类别，如：`application`、`video`、`model`。
* `$randomFileExt` 生成随机的文件扩展名，如：`war`、`book`、`fsc`。
* `$randomCommonFileName` 生成随机的常见的文件名，如：`well_modulated.mpg4`。
* `$randomCommonFileType` 生成随机的常见的文件类型，如：`application`、`audio`。
* `$randomCommonFileExt` 生成随机的常见的文件扩展名，如：`m2v`、`png`、`wav`。
* `$randomFilePath` 生成随机的文件路径，如：`/home/programming_chicken.cpio`。
* `$randomDirectoryPath` 生成随机的文件夹路径，如：`/usr/bin`、`/root`、`/usr/local/bin`。
* `$randomMimeType` 生成随机的 MIME 类型，如：`application/vnd.groove-identity-message`、`audio/vnd.vmx.cvsd`。

### 11. 金融

* `$randomBankAccount` 生成随机的 8 位银行账户，如：`09454073`、`65653440`、`75728757`。
* `$randomBankAccountName` 生成随机的银行账户名称，如：`Home Loan Account`、`Checking Account`。
* `$randomCreditCardMask` 生成随机的信用卡掩码，如：`3622`、`5815`。
* `$randomBankAccountBic` 生成随机的银行身份号 BIC(Bank Identifier Code)，如：`EZIAUGJ1`、`KXCUTVJ1`。
* `$randomBankAccountIban` 生成随机的 15-31 位的 IBAN(International Bank Account Number)，如：`MU20ZPUN3039684000618086155TKZ`。
* `$randomTransactionType` 生成随机的交易类型，如：`invoice`、`payment`、`deposit`。
* `$randomCurrencyCode` 生成随机的 3 位币种类型，如：`CDF`、`ZMK`、`GNF`。
* `$randomCurrencyName` 生成随机的币种名称，如：`CFP Franc`、`Cordoba Oro`。
* `$randomCurrencySymbol` 生成随机的币种符号，如：`$`、`£`。
* `$randomBitcoin` 生成随机的比特币地址，如：`3VB8JGT7Y4Z63U68KGGKDXMLLH5`。

### 12. 商业

* `$randomCompanyName` 生成随机的公司名称，如：`Johns - Kassulke`。
* `$randomCompanySuffix` 生成随机的公司后缀，如：`Inc`、`LLC`。
* `$randomBs` 生成随机的 business speak adjective，如：`killer leverage schemas`。
* `$randomBsAdjective` 生成随机的语音支持描述，如：`viral`、`24/7`。
* `$randomBsBuzz` 生成随机的 business speak buzzword，如：`repurpose`。
* `$randomBsNoun` 生成随机的 business speak noun，如：`e-services`。

### 13. 产品

* `$randomPrice` 生成随机的价格，范围为：100.00 ~ 999.00，如：`531.55`、`488.76`。
* `$randomProduct` 生成随机的产品，如：`Pizza`、`Pants`、`Towels`。
* `$randomProductAdjective` 生成随机的产品形容词，如：`Unbranded`、`Incredible`、`Tasty`。
* `$randomProductMaterial` 生成随机的产品原料，如：`Frozen`、`Plastic`、`Steel`。
* `$randomProductName` 生成随机的产品名称，如：`Handmade Concrete Tuna`。
* `$randomDepartment` 生成随机的产品分类，如：`Tools`、`Movies`、`Electronics`。

### 14. 语法

* `$randomNoun` 生成随机的名词，如：`matrix`、`bandwidth`、`bus`。
* `$randomVerb` 生成随机的动词，如：`navigate`、`quantify`、`parse`。
* `$randomIngverb` 生成随机的进行时动词，如：`navigating`、`synthesizing`、`backing up`。
* `$randomAdjective` 生成随机的形容词，如：`auxiliary`、`multi-byte`、`back-end`。
* `$randomWord` 生成随机的单词，如：`withdrawal`、`infrastructures`、`IB`。
* `$randomWords` 生成随机的多个单词，如：`Corporate Springs`、`Christmas Island Ghana Quality`。
* `$randomPhrase` 生成随机的语句，如：`You can't program the monitor without navigating the mobile XML program!`。

### 15. Lorem Ipsum

* `$randomLoremWord` 生成随机的单词，如：`est`。
* `$randomLoremWords` 生成随机的多个单词，如：`vel repellat nobis`。
* `$randomLoremSentence` 生成随机的语句，如：`Molestias consequuntur nisi non quod.`。
* `$randomLoremSentences` 生成随机的由 2~6 个语句组成的段落，如：`Et sint voluptas similique iure amet perspiciatis vero sequi atque. Ut porro sit et hic. Neque aspernatur vitae fugiat ut dolore et veritatis. Ab iusto ex delectus animi. Voluptates nisi iusto. Impedit quod quae voluptate qui.`。
* `$randomLoremParagraph` 生成随机的大段文字段落。
* `$randomLoremParagraphs` 生成随机的由 3 个段落组成的段落组。
* `$randomLoremText` 生成随机数量的单词组成的文本。
* `$randomLoremSlug` 生成随机的 URL 片段，如：`eos-aperiam-accusamus`、`beatae-id-molestiae`、`qui-est-repellat`。
* `$randomLoremLines` 生成随机的 1 ~ 5 行文字，如：`Ducimus in ut mollitia.\nA itaque non.\nHarum temporibus nihil voluptas.\nIste in sed et nesciunt in quaerat sed.`。


