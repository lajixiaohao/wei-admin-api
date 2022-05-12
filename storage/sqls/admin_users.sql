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

 Date: 12/05/2022 17:57:58
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin_users
-- ----------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `account` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '登录账号',
  `pwd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '登录密码',
  `trueName` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '姓名',
  `roleId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色ID',
  `deptId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '部门ID',
  `postId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '岗位ID',
  `isAble` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否启用。1是0否',
  `createdAt` datetime NULL DEFAULT NULL,
  `updatedAt` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `account`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_users
-- ----------------------------
INSERT INTO `admin_users` VALUES (1, 0, 'admin', '$2y$10$5VLIlb5BwCyaMfE5ATtmc.A1tn1hMcx4aaaR0sPTEJLaeJMSljT3e', 'Administrator', 1, 1, 0, 1, '2022-05-12 00:00:00', '2022-05-12 15:28:29');

SET FOREIGN_KEY_CHECKS = 1;
