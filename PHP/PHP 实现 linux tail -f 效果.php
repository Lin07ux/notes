#!/usr/bin/env php
<?php
# 顶部的第一行是告诉可执行文件, php 在系统 PATH 中查找, 提高可移植性

if (2 != count($argv)) {
	fwrite(STDERR, '调用格式错误! 使用 ./tail filename 格式'.PHP_EOL);
	return 1;
}

$file_name = $argv[1];
define("MAX_SHOW", 8192);

$file_size     = 0;
$file_size_new = 0;
$add_size      = 0;
$ignore_size   = 0;

$fp = fopen($file_name, "r");
while(1) {
	clearstatcache();
	$file_size_new = filesize($file_name);
	$add_size      = $file_size_new - $file_size;
	if ($add_size > 0) {
		if ($add_size > MAX_SHOW) {
			$ignore_size = $add_size - MAX_SHOW;
			$add_size    = MAX_SHOW;
			fseek($fp, $file_size + $ignore_size);
		}

		fwrite(STDOUT, fread($fp, $add_size));
		$file_size = $file_size_new;
	}

	usleep(50000);
}

fclose($fp);