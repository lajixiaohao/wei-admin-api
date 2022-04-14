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

 Date: 14/04/2022 21:16:53
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_admin_settings
-- ----------------------------
DROP TABLE IF EXISTS `sys_admin_settings`;
CREATE TABLE `sys_admin_settings`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `adminId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `isDarkMode` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否开启深色模式。1是0否',
  PRIMARY KEY (`id`, `adminId`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '争对某个管理员的基本设置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sys_admin_settings
-- ----------------------------
INSERT INTO `sys_admin_settings` VALUES (1, 1, 0);

SET FOREIGN_KEY_CHECKS = 1;
