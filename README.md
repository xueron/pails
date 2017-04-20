# Pails

Pails is a PHP framework based on [Phalcon](https://github.com/phalcon/cphalcon).

[![Style CI](https://styleci.io/repos/56280806/shield?branch=master)](https://styleci.io/repos/56280806) 

[![Package version](https://img.shields.io/packagist/v/xueron/pails.svg)](https://packagist.org/packages/xueron/pails) 

## Install & Run

### Composer

The fastest way to install Pails is to add it to your project using Composer (http://getcomposer.org/).

1. Install Composer:

    ```
    curl -sS https://getcomposer.org/installer | php
    ```

1. Create a pails project using Composer:

    ```
    php composer.phar create-project xueron/pails-seed myapp
    ```

## 基本目录结构

~/app
  - Console: 命令行工具
    - Commands: 自定义的命令行工具
  - Http: web应用
    - Controllers: WEB应用控制器
  - Models: 数据库模型 -- 扩展 \Phalcon\Mvc\Model
  - Services: 业务服务抽象，复杂一点的业务逻辑在这里实现
  - Providers: 自定义的服务提供者

~/resources
  - views: 视图文件（非PHP代码）

~/config
  - database.yml 数据库配置
  - app.php

~/db
  - migrations: 数据库迁移文件
  - seeds: seeds文件,初始化数据

~/log
  - 应用程序内日志存放

~/public
  - WEB应用的主目录

~/resources
  - assets: 资源文件，主要是css和js
  - views: 视图文件（非PHP代码）

~/tests
  - 单元测试

~/tmp
  - cache: 缓存类文件
    - volt: volt引擎编译缓存
  - pids: 后台程序的pid文件位置（如有）
  - sockets: 后台程序的socket文件位置（如有）

~/composer.json PHP的包管理配置

~/package.json npm的包管理配置

~/webpack.mix.js webpack打包工具的配置

~/pails 命令行工具入口

下面两个是动态生成的：
----

~/node_modules
  - npm包的位置，初始运行npm install的时候会生产

~/vendor
  - composer的安装位置，执行composer install的时候会生成
