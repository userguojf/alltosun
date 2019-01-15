--
-- 表的结构 `group_user`
--

CREATE TABLE IF NOT EXISTS `group_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

--
-- 转存表中的数据 `group_user`
--
-- 2 为 user_id 是初始化的超级管理员
INSERT INTO `group_user` (`id`, `admin_user_id`, `group_id`, `user_id`, `add_time`) VALUES
(26, 0, 8, 2, '0000-00-00 00:00:00');



--
-- 表的结构 `group_action`
--

CREATE TABLE IF NOT EXISTS `group_action` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL COMMENT '用户组ID',
  `action_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '控制器ID',
  `is_root` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否主模块',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户组和模块之前的关系表' AUTO_INCREMENT=2091 ;


--
-- 表的结构 `group`
--

CREATE TABLE IF NOT EXISTS `group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(50) NOT NULL DEFAULT '' COMMENT '用户组名称',
  `desc` varchar(200) NOT NULL DEFAULT '',
  `is_root` tinyint(4) NOT NULL DEFAULT '0' COMMENT '超级管理员组',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='权限模块-用户组表' AUTO_INCREMENT=22 ;

--
-- 转存表中的数据 `group`
--

INSERT INTO `group` (`id`, `name`, `desc`, `is_root`) VALUES
(7, '管理员', '抽奖管理员', 0),
(8, '超级管理员', '超级管理员不能删除', 1),
(21, '精品和优惠管理员', '精品和优惠管理员', 0);



CREATE TABLE IF NOT EXISTS `action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(50) NOT NULL DEFAULT '' COMMENT '控制器名字',
  `action_name` char(50) NOT NULL DEFAULT '' COMMENT '控制器方法名',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父类ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'anurl 处理的url',
  `is_ajax` tinyint(3) NOT NULL DEFAULT '0' COMMENT '默认不是0 如果为1是 is_ajax',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `action_name` (`action_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='权限模块-模块控制器表' AUTO_INCREMENT=132 ;

--
-- 转存表中的数据 `action`
--

INSERT INTO `action` (`id`, `name`, `action_name`, `pid`, `url`, `is_ajax`) VALUES
(37, '权限管理', 'action', 0, '', 0),
(50, '权限列表', 'action/admin/action', 37, 'action/admin/action', 0),
(106, '角色添加、编辑', 'action/admin/group/add', 37, 'action/admin/group/add', 1),
(49, '权限保存', 'action/admin/action/save', 37, 'action/admin/action/save', 1),
(51, '角色管理', 'action/admin/group', 37, 'action/admin/group', 0),
(53, '删除权限', 'action/admin/action/delete', 37, 'action/admin/action/delete', 1),
(98, '删除角色', 'action/admin/group/delete', 37, 'action/admin/group/delete', 1),
(99, '角色成员列表', 'action/admin/group_user', 37, 'action/admin/group_user', 1),
(100, '添加角色成员', 'action/admin/group_user/add', 37, 'action/admin/group_user/add', 1),
(101, '删除角色成员', 'action/admin/group_user/delete', 37, 'action/admin/group_user/delete', 1),
(102, '添加权限', 'action/admin/action/add', 37, 'action/admin/action/add', 1),
(104, '保存角色', 'action/admin/group/save', 37, 'action/admin/group/save', 1),
(105, '保存角色成员', 'action/admin/group_user/save', 37, 'action/admin/group_user/save', 1),
(107, '用户模块', 'user', 0, '', 0),
(116, '用户列表', 'user/admin', 107, 'user/admin', 0);

