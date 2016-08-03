
### TIMESTAMP
TIMESTAMP 时间戳在创建的时候可以有多重不同的特性，如：

1. 在创建新记录和修改现有记录的时候都对这个数据列刷新：
	`TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`
	
2. 在创建新记录的时候把这个字段设置为当前时间，但以后修改时，不再刷新它：
	`TIMESTAMP DEFAULT CURRENT_TIMESTAMP`
	
3. 在创建新记录的时候把这个字段设置为 0，以后修改时刷新它：
	`TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`
	
4. 在创建新记录的时候把这个字段设置为给定值，以后修改时刷新它：
	`TIMESTAMP DEFAULT ‘yyyy-mm-dd hh:mm:ss' ON UPDATE CURRENT_TIMESTAMP`



