### 1. 查看个人代码量

```shell
git log --author="username" --pretty=tformat: --numstat | awk '{ add += $1; subs += $2; loc += $1 - $2 } END { printf "added lines: %s, removed lines: %s, total lines: %s\n", add, subs, loc }' -
```

替换命令中的`username`为指定人员的 Git 名称即可得到该用户增加、删除、净增的行数。如：

```
added lines: 120745, removed lines: 71738, total lines: 49007
```

### 2. 统计每个人增删行数

```shell
git log --format='%aN' | sort -u | while read name; do echo -en "$name\t"; git log --author="$name" --pretty=tformat: --numstat | awk '{ add += $1; subs += $2; loc += $1 - $2 } END { printf "added lines: %s, removed lines: %s, total lines: %s\n", add, subs, loc }' -; done
```

这个可以统计出每个用户的增、删和净增代码行数。如：

```
Max-laptop    added lines: 1192, removed lines: 748, total lines: 444
chengshuai    added lines: 120745, removed lines: 71738, total lines: 49007
cisen    added lines: 3248, removed lines: 1719, total lines: 1529
```

### 3. 查看仓库提交者排名前 5

```shell
git log --pretty='%aN' | sort | uniq -c | sort -k1 -n -r | head -n 5
```

### 4. 贡献值统计

```shell
git log --pretty='%aN' | sort -u | wc -l
```

### 5. 提交数统计

```shell
git log --oneline | wc -l
```

### 6. 添加或修改的代码行数

```shell
git log --stat | perl -ne 'END { print $c } $c += $1 if /(\d+) insertions/'
```


