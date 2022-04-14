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

 Date: 14/04/2022 21:17:38
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_roles
-- ----------------------------
DROP TABLE IF EXISTS `sys_roles`;
CREATE TABLE `sys_roles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `roleName` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色名称',
  `roleIntroduce` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '角色介绍',
  `isAble` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否启用，1是0否',
  `createdAt` int UNSIGNED NULL DEFAULT 0,
  `updatedAt` int UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sys_roles
-- ----------------------------
INSERT INTO `sys_roles` VALUES (1, 0, '超级管理员', '系统超管，拥有超级权限。', 1, 1649910541, 1649910541);
INSERT INTO `sys_roles` VALUES (2, 1, '开发管理员', '负责系统开发和维护', 1, 1649910541, 1649910541);

SET FOREIGN_KEY_CHECKS = 1;
