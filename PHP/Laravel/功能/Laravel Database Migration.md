Laravel 中管理数据库的表格结构使用的是 Migration 命令行工具。这样能记录每一次对数据库表格结构的更改，从而方便数据库迁移和团队开发中保持数据库一致。

使用 Migration 工具生成的数据表结构文件位于：`database/migrations/`文件夹中。

## 一、基础命令

### 1.1 创建数据表文件

创建数据表结构，可以使用如下的命令：

```php
php artisan make:migration <file_name> [--create=<table_name>]
```

这样就会创建出一个以当前时间开头的、文件名中包含`<file_name>`内容的一个 php 文件，其中包含基础的内容。一般建议将`<file_name>`填写成一个能表名当前操作的内容，如`create_articles_table`表示创建一个`articles`数据表。

如果命令中没有`--create`选项，则只会生成一个文件，文件中包含一个类的定义，类中的`up`和`down`方法均没有给出初始定义；如果提供了`--create`选项，生成的文件中的类的`up`和`down`方法就会有一个初始定义语句，而且`up`方法的定义中，明确了生成的数据表的名称就是`--create`选项的值。所以一般**推荐添加`--create`选项**。

比如，可以使用如下的命令来创建一个 articles 数据库表：

```shell
php artisan make:migration create_articles_table --create=articles
```

上述命令执行后，就会生成文件名类似`2016_05_27_051245_create_articles_table.php`的一个文件。之后执行生成数据表的命令的时候，就会在数据库中生成一个`articles`数据表。

默认情况下，Laravel 中已经自带了两个数据表结构文件：`2014_10_12_000000_create_users_table.php`，`2014_10_12_100000_create_password_resets_table.php`。分别用于生成用户数据表和重置用户密码表。

### 1.2 编写数据表结构

上面的命令生成了基础的文件，然后就可以在其中实现`up`和`down`方法，来完成数据表的生成和回滚操作。

在`up`和`down`两个方法中执行的数据库操作都需要通过调用`Schema`类的静态方法来完成。

**在`up`方法中，一般格式如下**：

```php
Schema::create('table_name', function (Blueprint $table) {
    $table->increments('id');   // 生成自增 ID
    $table->timestamps();       // 生成 create_at 和 update_at 两个字段
});
```

当然，数据表中肯定不只这几个字段，还可以使用 Migration 提供的其他一些生成字段的方法来添加。具体可以查看下文的介绍。


**在`down`方法中，一般格式如下**：

```php
Schema::drop('table_name');
```

在回滚数据表的时候，可能还需要做更多的操作，可以通过传入一个匿名函数给`Schema::drop()`方法来实现。

### 1.3 生成数据表

创建完成表结构之后，数据库中并没有立即生成对应的数据表，而是要在命令行中执行如下的命令，才会将新的变动在数据库中实现：

```shell
php artisan migrate
```

> 如果是使用的虚拟机开发，比如 Homestead，则需要在虚拟机中的代码文件夹目录下执行这个命令。
> 如果要在宿主机上运行，可以更改`.env`文件中的`DB_PORT=3306`为`DB_PORT=33060`。
> 当前，也还有其他方法，不过基本思路都是：在宿主机上运行的时候，需要将连接数据库的端口号更改成 homestead 中 mysql 端口映射的端口号，比如，修改`config/database.php`文件中数据库的`'host' => env('DB_HOST', 'localhost')`为`host' => env('DB_HOST', 'localhost') . ('homestead' == gethostname() ? null : ':33060')`。

### 1.4 撤销数据库操作
如果需要将前一步对数据库所做操作进行回滚，那么可以使用 Migration 中的 rollback 命令实现：

```shell
php artisan migrate:rollback
```

### 1.5 修改表结构

如果发现需要对数据库的某些字段做调整，比如增加，修改字段，可以通过回滚数据表，修改数据表结构之后重新生成表。如果数据表中已经有数据了，这样就会造成数据的丢失。此时可以再建立一个 Migration 文件来对原先的数据表结构做修改。

```shell
php artisan make:migration <file_name> [--table=<table_name>]
```

和生成一个一般的数据表结构文件执行的命令基本相同，只是这里的附加选项是`--table`，用于指定修改哪个数据表。如果不指定这个选项，那么生成的文件中就只有一般的类定义，而`up`和`down`方法均没有代码实现；如果指定了这个选项，这两个方法就会生成一些基础的实现代码。

修改数据表结构文件中的`up`和`down`方法中，均是使用了`Schema::table()`方法来对数据库做操作。

- 如果是增加列，则和定义一个列一样的操作，如：`$table->string('intro');`；
- 如果是删除列，则可以使用`dropColumn()`方法来操作，如：`$table->dropColumn('intro');`；
- 其他更多的操作，可以看下文的介绍。

## 二、表字段方法

生成表字段的这些方法都需要通过一个 Blueprint 类型的`$table`对象来调用(这个`$table`是通过参数传入进去的，所以可以取名为任意可接受的值)。

### 2.1 自增字段

自增字段可以使用`increments(field_name)`方法来生成。其中，参数`field_name`表示自增字段的名称。

一般情况下，生成数据表文件的时候，会自带生成一个字段名为`id`的自增字段。

### 2.2 时间戳字段

有两个方法可以生成时间戳字段，用法和效果各不相同：

- `timestamps()`  同时生成`create_at`和`update_at`两个字段，均为时间戳格式；
- `timestamp(field_name)`  生成一个名称为 field_name 的字段，格式为时间戳。接受一个参数，表示字段的名称。

一般情况下，通过`migrate --create`命令生成的文件中，会自动包含有一个`timestamps()`方法的调用。



