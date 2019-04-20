### 传参

Policy 类中，不仅仅可以设置 User 和相应数据 Model 参数，还可以增加其他的参数。

在进行授权验证的时候，可以将附加参数合成一个数组。比如，对于如下的一个 Policy：

```php
class AccountPolicy
{
    public function list (User $user, $cooperator_id)
    {
        return $user->isAdmin() && $user->cooperator_id === (int)$cooperator_id;
    }
}
```

在 Controller 中进行授权验证时，可以使用如下方式传递参数：

```php
class AccountController extends Controller
{
    public function show ($id)
    {
        $this->authorize('list', [Account::class, $id]);
    }
}
```

这样就可以正常使用`AccountPolicy`授权策略中的`list()`方法，并将`$id`参数传入进去了。

