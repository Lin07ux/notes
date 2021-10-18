* `strings.HasPrefix(s, prefix string) bool` 判断字符串`s`是否以`prefix`开头；
* `strings.HasSuffix(s, suffix string) bool` 判断字符串`s`是否以`prefix`结尾。

* `strings.Contains(s, substr string) bool` 判断字符串`s`是否包含`sbustr`。

* `strings.Index(s, str string) int` 返回字符串`str`在字符串`s`中首次出现位置的索引（`str`的第一个字符的索引）。-1 表示字符串`s`不包含字符串`str`；
* `strings.LastIndex(s, str string) int` 返回字符串`str`在字符串`s`中最后出现位置的索引（`str`的第一个字符的索引）。-1 表示字符串`s`不包含字符串`str`；
* `strings.IndexRune(s string, r rune) int` 查询非 ASCII 编码的字符`r`在字符串`s`中的位置。

* `strings.Replace(s, old, new string, n int) string` 将字符串`s`中的前`n`个子字符串`old`替换成`new`子字符串，并返回替换后的新字符串。如果`n = -1`则替换所有的`old`子字符串。

* `strings.Count(s, str string) int` 统计子字符串`str`在字符串`s`中出现的非重复次数，区分大小写。

* `strings.Repeat(s string. n count)` 将字符串`s`重复`count`，返回拼接后的新字符串。

* `strings.ToLower(s string) string` 将字符串中的 Unicode 字符全部转换为相应的小写字符；
* `strings.ToUpper(s string) string` 将字符串中的 Unicode 字符全部转换为相应的大写字符。

* `strings.Trim(s, cutset string) string` 剔除字符串`s`开头和结尾的`cutset`字符串去除掉；
* `strings.TrimLeft(s, cutset string) stirng` 剔除字符串`s`开头的`cutset`字符串去除掉；
* `strings.TrimRight(s, cutset string) stirng` 剔除字符串`s`结尾的`cutset`字符串去除掉；
* `strings.TrimSpace(s string) string` 剔除字符串开头和结尾的空白符号。

* `strings.Fields(s string) []string` 使用一个或多个空白符号作为动态长度的分隔符将字符串分隔成若干块，并返回一个 Slice。如果字符串只包含空白符号，则返回一个长度为 0 的 Slice；
* `strings.Split(s, sep string) []string` 使用子字符串`sep`将字符串`s`进行分隔，并返回一个 Slice。
* `strings.Join(sl []string, sep string) string` 将元素类型为 string 的 Slice `sl`使用分隔符`seq`拼接成一个新的字符串并返回。

* `strings.NewReader(s string) *Reader` 生成一个从字符串`s`中读取内容的 Reader，并返回指向该 Reader 的指针；
* `strings.Read(b []byte) (n int, err error)` 生成一个从`[]byte`中读取内容的 Reader，并返回指向该 Reader 的指针；




