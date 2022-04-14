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

 Date: 14/04/2022 21:17:00
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_administrators
-- ----------------------------
DROP TABLE IF EXISTS `sys_administrators`;
CREATE TABLE `sys_administrators`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `account` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '登录账号',
  `pwd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '登录密码',
  `trueName` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '姓名',
  `roleId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色ID',
  `deptId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '部门ID',
  `postId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '岗位ID',
  `isLocked` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否被锁定。1是0否',
  `isAble` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否启用。1是0否',
  `isDeleted` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已删除。1是0否',
  `createdAt` int UNSIGNED NOT NULL DEFAULT 0,
  `updatedAt` int UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`, `account`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sys_administrators
-- ----------------------------
INSERT INTO `sys_administrators` VALUES (1, 0, 'admin', '$2y$10$7TkaK1lM0RqZ0x5mIogmceQu5KTiSoqQdk0yGq.rmqI4jhbl2ulOa', 'Administrator', 1, 1, 0, 0, 1, 0, 1649910541, 1649910541);
INSERT INTO `sys_administrators` VALUES (2, 0, 'development', '$2y$10$7TkaK1lM0RqZ0x5mIogmceQu5KTiSoqQdk0yGq.rmqI4jhbl2ulOa', NULL, 2, 0, 0, 0, 1, 0, 1649910541, 1649910541);

SET FOREIGN_KEY_CHECKS = 1;
