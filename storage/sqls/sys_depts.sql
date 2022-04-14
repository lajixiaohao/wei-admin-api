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

 Date: 14/04/2022 21:17:07
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_depts
-- ----------------------------
DROP TABLE IF EXISTS `sys_depts`;
CREATE TABLE `sys_depts`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `deptName` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '部门名称',
  `deptIntroduce` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '部门介绍',
  `sort` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序',
  `createdAt` int UNSIGNED NOT NULL DEFAULT 0,
  `updatedAt` int UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`, `deptName`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '部门表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sys_depts
-- ----------------------------
INSERT INTO `sys_depts` VALUES (1, 0, '根部门', '根部门，非实际部门。', 1, 1649910541, 1649910541);

SET FOREIGN_KEY_CHECKS = 1;
