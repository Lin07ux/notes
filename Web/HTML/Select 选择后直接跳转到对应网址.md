### 方法一

```html
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
	<title>select加链接</title>
</head>
<body>
	<script language=javascript>
	   // open the related site windows
		function mbar(sobj) {
			var docurl = sobj.options[sobj.selectedIndex].value;
			if (docurl != "") {
				open(docurl, '_blank');
				sobj.selectedIndex=0;
				sobj.blur();
			}
		}
	</script>

	<select onchange=mbar(this) name="select">
		<option selected>=== 合作伙伴 ===</option>
		<option value="http://www.baidu.com">百度</option>
		<option value="http://www.163.com">网易</option>
		<option value="http://www.flash8.net/">闪吧</option>
	</select>
</body>
</html>
```

### 方法二

```html
<select name="pageselect" onchange="self.location.href=options[selectedIndex].value" >
	<option value="http://www.baidu.com">百度</option>
	<option value="http://www.163.com">网易</option>
</select>
```

### 方法三

```html
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>select选择-按钮跳转</title>
	<script type="text/javascript">
		function setsubmit () {
			if(mylink.value == 0)
				window.location='http://www.baidu.com';
			else if(mylink.value == 1)
				window.location='http://www.163.com';
			else if(mylink.value == 2)
				window.location='http://www.sina.com';
		}
	</script>
</head>
<body>
	<select name="mylink" id="mylink">
		<option value="0">百度</option>
		<option value="1">网易</option>
		<option value="2">新浪</option>
	</select>
	<input type="button" id="btn" value="提交" onclick="setsubmit(this)" />
</body>
</html>
```

