<?php

class Container {
    /**
     * @var array 设置的依赖绑定
     */
    public $binding = [];

    /**
     * 注入依赖
     *
     * @param string              $abstract 目标类
     * @param null|string|Closure $concrete 依赖
     * @param bool                $shared   是否共享
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        if (!$concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->binding[$abstract] = compact('concrete', 'shared');
    }

    /**
     * 生成依赖的闭包函数
     *
     * @param string $abstract 目标类
     * @param string $concrete 依赖类
     *
     * @return Closure
     */
    protected function getClosure($abstract, $concrete)
    {
        return function ($c) use ($abstract, $concrete) {
            $method = $abstract == $concrete ? 'build' : 'make';
            return $c->$method($concrete);
        };
    }

    /**
     * 生成目标类的示例
     *
     * @param string $abstract 目标类的名称
     *
     * @return object
     *
     * @throws Exception
     */
    public function make($abstract)
    {
        $concrete = $this->getConcrete($abstract);

        if ($this->isBuildable($concrete, $abstract)) {
            return $this->build($concrete);
        }

        return $this->make($concrete);
    }

    /**
     * 获取目标类的依赖闭包
     *
     * @param string $abstract 目标类
     *
     * @return mixed
     */
    protected function getConcrete($abstract)
    {
        if (!isset($this->binding[$abstract])) {
            return $abstract;
        }

        return $this->binding[$abstract]['concrete'];
    }

    /**
     * 判断目标类是否可以直接创建
     *
     * @param string         $concrete 目标类
     * @param string|Closure $abstract 依赖
     *
     * @return bool
     */
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete == $abstract || $concrete instanceof Closure;
    }

    /**
     * 构造依赖类实例,可能会存在依赖
     *
     * @param string|Closure $concrete 依赖类名或闭包
     *
     * @return object
     *
     * @throws Exception
     */
    public function build($concrete)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        // 反射
        $reflector = new ReflectionClass($concrete);
        if (!$reflector->isInstantiable()) {
            throw new Exception("Target [$concrete] is not instantiable.");
        }

        // 获取要实例化对象的构造函数
        $constructor = $reflector->getConstructor();

        // 没有定义构造函数，只有默认的构造函数，说明构造函数参数个数为空
        if (is_null($constructor)) {
            return new $concrete;
        }

        // 获取构造函数所需要的所有参数
        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        // 从给出的数组参数在中实例化对象
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * 获取类的构造函数的依赖参数
     *
     * @param ReflectionParameter[] $parameters 类的构造函数的参数列表
     *
     * @return array
     */
    protected function getDependencies($parameters)
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $c = $parameter->getClass();
            $dependencies[] = is_null($c) ? null : $this->make($c->name);
        }

        return $dependencies;
    }
}
