# EasyAdmin(极易后台开发框架)

### EasyAdmin是一款基于ThinkPHP6+Bootstrap3的全插件式框架，使用插件模式开发，拥有强大的扩展能力和完善的插件开发支持，可用于cms、商城、采集、微信、小程序、app、api等各种类型的网站开发，是一款全能型的开源框架。
## EasyAdmin的魅力在于所有的需求均可以通过开发插件解决，比如：
* 用EasyAdmin写一个cms插件，
* 写一个插件给cms添加一个新的模板或者功能模块。
* 写一个插件给cms的后台会员中心添加一个新的模板或功能。
* 写一个插件给cms增加采集功能。
* 写一个插件给cms采集功能增加一个定时采集功能。
* 写一个插件给cms所有前台页面底部添加一个在线聊天功能。
* 写一个插件给cms增加wap版。
* 写一个插件给cms增加一套api接口用于小程序开发。
* 写一个插件给cms增加一个新的前台绑定新的域名，实现一个后台两套前台。
* 写一个新的插件将以上所有功能集成到一个新的cms插件里。
* ......
* ......
* 一个插件不需要了直接删掉即可，所有插件完全独立，插件文件全部位于插件目录内，无侵入式框架。

## 环境

> 运行环境要求PHP7.3+。

## 主要新特性

* 全插件式框架
* 采用thinkPHP 6.0+AdminLTE 开发
* 参考了fastadmin，初期可看作fa的tp6.0升级版(已完成)
* 实现了插件化开发，除核心框架外所有功能均已插件化(已完成)
* 插件目录化，插件所有文件均存放在插件目录下，不污染系统框架(已完成)
* 插件开发遵循thinkphp开发规则，每个插件都相当于一个独立thinkphp，不需要另学习开发规则(已完成)
* 强大的插件市场，插件无需重复开发，开箱即用(已完成)

## 其他功能

* 拥有完整的插件生成、开发、打包等辅助插件开发插件，适合外包、二次开发。
* 每个插件都相当于一个独立thinkphp，所有其他基于thinkphp开发的系统只需修改很少代码即可打包成插件平滑移植到EasyAdmin框架


## 安装

~~~
composer create-project xiaoyaor/easyadmin ea 1.0.*
~~~

如果需要更新框架使用
~~~
composer update topthink/framework
~~~
## 截图
后台首页截图： 
![截图](https://raw.githubusercontent.com/xiaoyaor/EasyAdmin/master/screenshort.png)

## 官网

请访问 [极易官网](https://www.easyadmin.vip)。

## 开源地址
Gitee：<a href="https://gitee.com/gitshenyin/EasyAdmin" target="_blank">访问Gitee</a>

Github：<a href="https://github.com/xiaoyaor/EasyAdmin" target="_blank">访问Github</a>

## QQ群

EasyAdmin交流群:[863713643](//shang.qq.com/wpa/qunwpa?idkey=ce12bc3cbc9a2ccbca97d287609f61dffc0347a62a204780271be3ef12f70129)

## 文档

[极易开发手册](https://doc.easyadmin.vip)。

## 版权信息

EasyAdmin遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2019-2020 by EasyAdmin (http://www.easyadmin.vip)

All rights reserved。

EasyAdmin® 商标和著作权所有者为逍遥游(临沂)信息科技有限公司。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
