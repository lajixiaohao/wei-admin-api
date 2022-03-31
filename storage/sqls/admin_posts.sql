/*
 Navicat MySQL Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80022
 Source Host           : localhost:3306
 Source Schema         : wei_admin

 Target Server Type    : MySQL
 Target Server Version : 80022
 File Encoding         : 65001

 Date: 05/09/2021 17:05:34
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin_posts
-- ----------------------------
DROP TABLE IF EXISTS `admin_posts`;
CREATE TABLE `admin_posts`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '岗位名称',
  `sort` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序',
  `is_able` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin COMMENT = '岗位表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
