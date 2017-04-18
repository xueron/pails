配置文件使用说明
==============

Pails通过config服务管理配置文件。配置文件统一放在 ~/config 目录下面。

每个配置文件为一组，分三段，分别是三种运行环境的配置：production、development、testing

一个完整的配置文件格式如下：

.. code-block:: php

    <?php
    return [
        'development' => [
            'key'            => '',
            'stores_tableid' => '',
        ],
        'testing'     => [
            'key'            => '',
            'stores_tableid' => '',
        ],
        'production'  => [
            'key'            => '',
            'stores_tableid' => '', 
        ],
    ];

额外的几个文件
------------
~/config 目录下面有几个文件是个例外，他们有命令行工具自动生成和维护，不是上述格式。分别是：
* commands.php 这里面维护的是一个应用内创建的命令行工具的列表，自动加载。pails make:command 命令会更新这个列表。
* services.php 这里面维护的是一个服务的列表内，会在应用启动的时候自动注入到DI里面。pails make:service 和 pails make:worker 命令会更新这个列表。
* listeners.php 这里维护的是一个Phalcon事件的监听程序列表，会在应用启动的时候，自动挂载到EventsManager上面。pails make:listener 命令会更新这个列表。

获取配置
-------
在程序内获取配置非常方便。通过DI注入的config服务即可。config服务会自动使用当前的运行环境。比如，在生产环境上production，则会取production那一段里面的配置。
获取方法如下：

.. code-block:: php

    $this->config->get('amap.key'),

其中：
* 第一段 amap 表示从 ~/config/amap.php 这个配置文件获取。
* 剩下的部分，表示所去配置的键值。
* 取哪个环境下的配置，由config服务自动识别。