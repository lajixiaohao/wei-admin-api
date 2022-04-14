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

 Date: 14/04/2022 21:16:48
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_abnormal_administrators
-- ----------------------------
DROP TABLE IF EXISTS `sys_abnormal_administrators`;
CREATE TABLE `sys_abnormal_administrators`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `adminId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `describe` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '异常描述',
  `ip` varbinary(16) NULL DEFAULT NULL COMMENT 'ip',
  `device` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '设备',
  `createdAt` int UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '异常用户表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
