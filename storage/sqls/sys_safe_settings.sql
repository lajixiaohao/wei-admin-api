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

 Date: 14/04/2022 21:17:43
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_safe_settings
-- ----------------------------
DROP TABLE IF EXISTS `sys_safe_settings`;
CREATE TABLE `sys_safe_settings`  (
  `lockedLimit` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '1分钟内密码输错多少次后账户被锁定，0表示无限制',
  `apiRequestLimit` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '1分钟内所有接口请求次数超过多少次后账户被锁定。0表示无限制'
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统安全设置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sys_safe_settings
-- ----------------------------
INSERT INTO `sys_safe_settings` VALUES (0, 0);

SET FOREIGN_KEY_CHECKS = 1;
