# 84php-更好用的非MVC的PHP框架

## ++紧急通知++

* 由于官网进行备案变更，预计3月25日前官网都无法正常访问，深表歉意。

* 之前发布的3.0.0有严重的安全问题（一些包含机密信息的报错内容会被展示出来），现已撤回。

* 作为小小的补偿，近期将发布的3.0.1版本（已推送至develop分支）里新增了日志模块LOG和其它功能，支持报错信息、SQL、和自定义信息的记录。


## 官网

~~[https://www.84php.com](https://www.84php.com)~~ `网站备案变更中`

## 文档

[https://doc.bux.cn/84php](https://doc.bux.cn/84php)

## QQ群

* 823907259

## 性能对比（QPS）

> **测试方式：** Apache Bench / 并发50共5000次请求 / 测试5次取平均值（四舍五入）
> **测试项目：** 文本输出 / 从一个包含2000条数据的数据库中，用分页的方式，取出第920-1000行数据
> **服务器环境：** 腾讯云-上海四区标准型S2 / Win2008 R2-SP1 企业版 / Apache 2.4.41-win64 / PHP 7.4.3-x64-TS
> **数据库环境：** 腾讯云-上海四区高可用版-1核1000MB-MySQL5.7
> **版本号：** 84PHP v3.0.1 / ThinkPHP（以下简称 TP） v6.0.2 / Yii 2

### 文本输出
```
84PHP   ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■  478 QPS
 TP     ■■■■■  64 QPS
yii2    ■■■■  48 QPS
```

### 数据分页
```
84PHP   ■■■■■■■■■■■■■■■■■■■■■■■  270 QPS
 TP     ■■■  39 QPS
yii2    ■■■  36 QPS
```

## 谜之起源

**明明有TP、Yii、Zend、Laravel和数不清的非著名PHP框架，为什么又要造轮子？**

这个问题也一度让我很疑惑——这个框架凭什么脱颖而出？

答案是：**简单、安全、高效**。

几乎每一个框架都会把这三点用来宣传，但实际上能做到的，寥寥无几。

国内用的最多的就是TP框架了，TP真的很棒，但是随着越来越丰富的功能和与日俱增的版本号，TP的易用性和性能也在下滑。

**这明显不可能：既要简单、又要功能丰富、还要高性能。**

所以这个结论成为了84PHP的一个很重要的设计思想：**性能 > 易用性 > 功能丰富度** 。

也就是说，84PHP注定不会是一个大而全的框架，绝对不会为了特殊的开发者需求而构建，因为大部分特殊的需求都有与其对应的解决方案。

基于此，便衍生出了框架的另一个设计思想：**加减并行** 。

随着技术的迭代，注定会有很多功能被丢在历史长河的垃圾桶中：API模式的开发使得PHP自带的SESSION失去了意义、将来会普及的NewSQL，也会将传统关系型SQL和NoSQL合二为一，以及还有很多我们看不到的技术趋势。因此，一些过时的功能，将不会在框架中保留，尽可能地使得框架不要太臃肿。

所以在84PHP中采用了模块插拔式设计，不至于牵一发而动全身，用不到模块就只是一个文件而已。如果你有代码洁癖，删了就好了。

上面讲的是高效，那么易用性如何保证呢？

当下，可以说除了84PHP之外的框架，都采用了MVC模式。不是说MVC模式不好，而是对于没有系统学过软件工程的小白程序员来说，MVC模式实在难以理解，更不用说面向对象、命名空间、继承与多态。如果我当初学会了ThinkPHP，估计也就不会有84PHP了。

所以84PHP的模式，更贴近于原生，而不是奇奇怪怪的各种主流或非主流模式。如果想用MVC模式开发，ThinkPHP真的适合你。

说了这么多，建议屏幕前的你百看不如一试。

友情提示：官网那2万多字的文档，真没必要一字一句读，有了个大概认识，直接动手尝试，遇到问题再看文档或者去群里求助就好啦。
 