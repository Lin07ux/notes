### 身份证验证
```javascript
// 加权因子
var Wi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1 ];
// 身份证验证位值 10代表X
var ValideCode = [ 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ];   
function IdCardValidate (idCard) { 
     // 去掉字符串头尾空格
    idCard = trim(idCard.replace(/ /g, ""));
    if (idCard.length == 15) {
        // 进行15位身份证的验证
        return isValidityBrithBy15IdCard(idCard);    
    } else if (idCard.length == 18) {   
        // 得到身份证数组
        var a_idCard = idCard.split("");
        
        // 进行18位身份证的基本验证和第18位的验证
        return isValidityBrithBy18IdCard(idCard) && isTrueValidateCodeBy18IdCard(a_idCard)  
    }

    return false;  
}

/**  
 * 判断身份证号码为18位时最后的验证位是否正确  
 * @param a_idCard 身份证号码数组  
 * @return  
 */  
function isTrueValidateCodeBy18IdCard (a_idCard) {   
    var sum = 0;            // 声明加权求和变量   
    if (a_idCard[17].toLowerCase() == 'x') {   
        a_idCard[17] = 10;  // 将最后位为x的验证码替换为10方便后续操作   
    }
    
    for ( var i = 0; i < 17; i++) {   
        sum += Wi[i] * a_idCard[i]; // 加权求和   
    }
    valCodePosition = sum % 11;     // 得到验证码所位置 

    return a_idCard[17] == ValideCode[valCodePosition]
}   
/**  
  * 验证18位数身份证号码中的生日是否是有效生日  
  * @param idCard 18位书身份证字符串  
  * @return  
  */  
function isValidityBrithBy18IdCard (idCard18) {   
    var year  = idCard18.substring(6,10);   
    var month = idCard18.substring(10,12);   
    var day   = idCard18.substring(12,14);   
    var temp_date = new Date(year,parseFloat(month)-1,parseFloat(day));

    // 这里用getFullYear()获取年份，避免千年虫问题   
    if(temp_date.getFullYear()!=parseFloat(year)   
          || temp_date.getMonth()!=parseFloat(month)-1   
          || temp_date.getDate()!=parseFloat(day)) {   
            return false;   
    }

    return true;  
}   
/**  
* 验证15位数身份证号码中的生日是否是有效生日  
* @param idCard15 15位书身份证字符串  
* @return  
*/  
function isValidityBrithBy15IdCard (idCard15) {   
 var year  = idCard15.substring(6,8);   
 var month = idCard15.substring(8,10);   
 var day   = idCard15.substring(10,12);   
 var temp_date = new Date(year,parseFloat(month)-1,parseFloat(day));
 
 // 对于老身份证中的你年龄则不需考虑千年虫问题而使用getYear()方法   
 if (temp_date.getYear()!=parseFloat(year)   
         ||temp_date.getMonth()!=parseFloat(month)-1   
         ||temp_date.getDate()!=parseFloat(day)){   
           return false;   
    }

    return true;   
}   
//去掉字符串头尾空格   
function trim (str) {   
    return str.replace(/(^\s*)|(\s*$)/g, "");   
}
```

### 判断是否为空

```javascript
/**判断是否为空**/
function isBlank(_value){
    if(_value==null || _value=="" || _value==undefined){
        return true;
    }
    
    return false;
}
```

### 邮箱正则表达式

```javascript
var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/;
```

### 检查文件类型

```javascript
/**
 * 检查文件类型
 * @param ths file对象
 * @return 是否符合规格
 */
function checkImgType (ths) {
    try {
        var obj_file = $(ths).get(0).files;
        
        for (var i = 0; i < obj_file.length; i++) {
            if (!/\.(JPEG|BMP|GIF|JPG|PNG)$/.test(obj_file[i].name.toUpperCase()))
                return false;
        }
   } catch (e) {
   }

    return true;
}
```

### 检查文件大小 

```javascript
/**
 * 检查文件大小
 * @param ths file对象
 * @param limitSize 限制大小(k)
 * @return 是否符合规格 
 */
function checkImgSize (ths, limitSize) {
    try {
        var maxsize = limitSize * 1024;
        var msgSize = limitSize + "K";
        
        if (limitSize >= 1024) {
            msgSize = limitSize / 1024 + "M";
        }
        
        var errMsg   = "上传的图片不能超过" + msgSize;
        var obj_file = $(ths).get(0).files;
    
        for (var i = 0; i < obj_file.length; i++) {
            if (obj_file[i].size > maxsize) {
                alert(errMsg);
                $(ths).val("");
                return false;
            }
        }
    } catch (e) {

    }
   
    return true;
}
```

