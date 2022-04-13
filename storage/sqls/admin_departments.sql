/*
 Navicat MySQL Data Transfer

 Source Server         : bcc
 Source Server Type    : MySQL
 Source Server Version : 80021
 Source Host           : 106.13.146.231:3306
 Source Schema         : wei_admin

 Target Server Type    : MySQL
 Target Server Version : 80021
 File Encoding         : 65001

 Date: 10/11/2021 10:19:53
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin_departments
-- ----------------------------
DROP TABLE IF EXISTS `admin_departments`;
CREATE TABLE `admin_departments`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '部门名称',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序',
  `is_able` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin COMMENT = '部门表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_departments
-- ----------------------------
INSERT INTO `admin_departments` VALUES (1, 0, '根部门', 1, 1, '2021-08-01 00:00:00', '2021-08-01 00:00:00');

SET FOREIGN_KEY_CHECKS = 1;
