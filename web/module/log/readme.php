<?php
/*
    @标题：  日志操作相关说明

    @描述：  用于记录后台的数据库操作，一般用于以后管理员查询信息用，记录的操作有 对数据库进行
            删除、更新、添加操作都会被记录。

    @方法：  记录操作日志
             @param string $res_name    操作的表名
             @param int    $res_id      资源id
             @param string $action      执行的动作 （新增；修改；删除; 审核）
            _widget('log')->record($res_name, $res_id, $action);

    @例子：   添加了商品ID为3的商品。
                _widget('log')->record('goods',3,'新增');
             批量操作：添加了商品ID为4商品，添加快照ID为4的快照，修改了商品ID为5的商品
                $res_name = array('goods','goods_snapshots','goods');
                $res_id   = array(4,4,5);
                $action   = array('新增','新增','修改');
                _widget('log')->record($res_name, $res_id,$action);
             审核通过一条评论ID为5的评论
                _widget('log')->record('comment', 5, '审核');
             删除了一条订单ID为8的订单同时又添加一张银行卡(卡ID为9):
                _widget('log')->record(array('order','bank_card'),array(8,9),array('删除','新增'));
             对同一张表进行多个同一操作,例如要对order表进行多次删除操作,(被删主键ID为8，9，10)
                _widget('log')->record('order', array(8,9,10), '删除');
             对同一张表进行多个不同操作，例如要对order表的ID为8的数据进行删除，修改ID为9的数据，新增ID为10的数据。注意
             每个操作和主键ID必须一一对应。而且必须都为索引数组
                _widget('log')->record('order', array(8,9,10), array('删除', '修改', '新增'));


*/