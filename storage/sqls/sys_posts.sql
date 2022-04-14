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

 Date: 14/04/2022 21:17:28
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_posts
-- ----------------------------
DROP TABLE IF EXISTS `sys_posts`;
CREATE TABLE `sys_posts`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `postName` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '岗位名称',
  `postIntroduce` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '岗位介绍',
  `sort` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序',
  `isAble` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否启用。1是0否',
  `createdAt` int UNSIGNED NOT NULL DEFAULT 0,
  `updatedAt` int UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`, `postName`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '岗位表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sys_posts
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
