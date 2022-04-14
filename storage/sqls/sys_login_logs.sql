/*
 Navicat MySQL Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80022
 Source Host           : localhost:3306
 Source Schema         : wei-admin

 Target Server Type    : MySQL
 Target Server Version : 80022
 File Encoding         : 65001

 Date: 14/04/2022 21:17:13
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_login_logs
-- ----------------------------
DROP TABLE IF EXISTS `sys_login_logs`;
CREATE TABLE `sys_login_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `adminId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `ip` varbinary(16) NULL DEFAULT NULL COMMENT '登录IP',
  `device` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '登录设备',
  `loginAt` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录时间',
  `logoutAt` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '退出登录时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '登录日志表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
