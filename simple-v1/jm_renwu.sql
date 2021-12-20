CREATE TABLE `jm_renwu` (
   `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
   `fs` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '方式',
   `key` VARCHAR(200) NOT NULL DEFAULT ''  COMMENT '关联搜词，如：域名',
   `data_id` int(10) NOT NULL DEFAULT 0  COMMENT '内部关联id',
   `tjsj` int(10) NOT NULL DEFAULT '0' COMMENT '提交时间',
   `gxsj` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
   `wb_data` text NOT NULL  COMMENT '外部提交数据',
   `wb_id` VARCHAR (200) NOT NULL DEFAULT ''  COMMENT '外部id',
   `zt` tinyint(3) NOT NULL COMMENT '状态：0待执行，1成功，2失败，9执行中,10外部执行中，99异常',
   `log` text COMMENT '任务日志',
   PRIMARY KEY (`id`),
   KEY `zt` (`zt`),
   KEY `gxsj` (`gxsj`),
   KEY `fs` (`fs`),
   KEY `key` (`key`),
   KEY `tjsj` (`tjsj`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='任务表'