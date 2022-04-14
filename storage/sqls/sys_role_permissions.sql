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

 Date: 14/04/2022 21:17:34
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_role_permissions
-- ----------------------------
DROP TABLE IF EXISTS `sys_role_permissions`;
CREATE TABLE `sys_role_permissions`  (
  `roleId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色ID',
  `menuId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '菜单ID'
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色权限表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sys_role_permissions
-- ----------------------------
INSERT INTO `sys_role_permissions` VALUES (2, 1);
INSERT INTO `sys_role_permissions` VALUES (2, 5);
INSERT INTO `sys_role_permissions` VALUES (2, 6);
INSERT INTO `sys_role_permissions` VALUES (2, 9);
INSERT INTO `sys_role_permissions` VALUES (2, 10);
INSERT INTO `sys_role_permissions` VALUES (2, 11);
INSERT INTO `sys_role_permissions` VALUES (2, 12);

SET FOREIGN_KEY_CHECKS = 1;