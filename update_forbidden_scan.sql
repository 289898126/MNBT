-- MNBT 违禁词扫描功能数据库更新脚本
-- 执行前请备份数据库！

-- 添加违禁词扫描配置字段到 MN_config 表
ALTER TABLE `MN_config` ADD `wjsckg` VARCHAR(20) NOT NULL DEFAULT 'false' COMMENT '违禁词扫描开关';
ALTER TABLE `MN_config` ADD `wjsccnr` TEXT NULL DEFAULT NULL COMMENT '违禁词内容(每行一个)';
ALTER TABLE `MN_config` ADD `wjsckgqbfx` VARCHAR(10) NOT NULL DEFAULT 'true' COMMENT '是否只扫描变更文件';
ALTER TABLE `MN_config` ADD `wjscml` VARCHAR(500) NOT NULL DEFAULT '/www/wwwroot' COMMENT '扫描目录';
ALTER TABLE `MN_config` ADD `wjstqml` TEXT NULL DEFAULT NULL COMMENT '跳过目录(逗号分隔)';
ALTER TABLE `MN_config` ADD `wjstqhz` TEXT NULL DEFAULT NULL COMMENT '跳过后缀(逗号分隔)';
ALTER TABLE `MN_config` ADD `wjscdzmax` INT(11) NOT NULL DEFAULT 5242880 COMMENT '单文件最大大小(字节),默认5MB';
ALTER TABLE `MN_config` ADD `wjscdhmax` INT(11) NOT NULL DEFAULT 1000 COMMENT '单次扫描最大命中数';
ALTER TABLE `MN_config` ADD `wjscqzcs` VARCHAR(20) NOT NULL DEFAULT '0 3 * * *' COMMENT '定时全量复扫 cron 表达式(默认每天凌晨3点)';
ALTER TABLE `MN_config` ADD `wjscqzcskg` VARCHAR(20) NOT NULL DEFAULT 'true' COMMENT '定时全量复扫开关';