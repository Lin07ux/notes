schedule 是一个 Python 中的定时器模块，提供了非常语义化的 API。

* [schedule - GitHub](https://github.com/dbader/schedule)
* [文档](https://schedule.readthedocs.io/)

使用示例如下：

```Python
import schedule
import time

def job():
    print("I'm working...")

schedule.every(10).minutes.do(job) 
schedule.every().hour.do(job)
schedule.every().day.at("10:30").do(job)
schedule.every().monday.do(job)
schedule.every().wednesday.at("13:15").do(job)
schedule.every().minute.at(":17").do(job)

while True:
    schedule.run_pending()
    time.sleep(1)
```

