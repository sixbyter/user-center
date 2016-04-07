# User Center

这文档目前还未完善, 比较粗糙

先确认一个规范


response的json格式是:

默认值:
```json
{
    "data": null,
    "error": {
        "code": 0,
        "message" "响应成功"
    }
}
```


请求需要验证, 验证方式是sign验证方式:

好处:

1. 确保请求来源是授权的来源
2. 请求没有被串改

缺点:
不如HTTPS