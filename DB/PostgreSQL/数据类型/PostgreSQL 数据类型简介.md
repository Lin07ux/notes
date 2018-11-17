   名字                              |     别名         |    描述
----------------------------------- | ---------------- | ---------
bigint                              | int8             | 有符号 8 字节整数
bigserial                           | serial8          | 自增 8 字节整数
bit [(n)]                           |                  | 定长位串
bit varying [(n)]                   | varbit           | 变长位串
boolean                             | bool             | 逻辑布尔值(真/假)
box                                 |                  | 平面中的矩形
bytea                               |                  | 二进制数据("字节数组")
character varying [(n)]             | varchar [(n)]    | 变长字符串
character [(n)]                     | char [(n)]       | 定长字符串
cidr                                |                  | IPv4 或 IPv6 网络地址
circle                              |                  | 平面中的圆
date                                |                  | 日历日期(年, 月, 日)
double precision                    | float8           | 双精度浮点数字
inet                                |                  | IPv4 或 IPv6 网络地址
integer                             | int, int4        | 有符号 4 字节整数
interval [(p)]                      |                  | 时间间隔
line                                |                  | 平面中的无限长直线
lseg                                |                  | 平面中的线段
macaddr                             |                  | MAC 地址
money                               |                  | 货币金额
numeric [(p, s)]                    | decimal [(p, s)] | 可选精度的准确数字
path                                |                  | 平面中的几何路径
point                               |                  | 平面中的点
polygon                             |                  | 平面中的封闭几何路径
real                                | float4           | 单精度浮点数
smallint                            | int2             | 有符号 2 字节整数
serial                              | serial4          | 自增 4 字节整数
text                                |                  | 变长字符串
time [(p)] [without time zone]      |                  | 一天中的时间
time [(p)] with time zone           | timetz           | 一天里的时间，包括时区
timestamp [(p)] [without time zone] |                  |日期和时间
timestamp [(p)] with time zone      | timestamptz      | 日期和时间，包括时区

