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

 Date: 14/04/2022 21:17:18
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_menus
-- ----------------------------
DROP TABLE IF EXISTS `sys_menus`;
CREATE TABLE `sys_menus`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '菜单名',
  `path` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '路径uri',
  `componentName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '组件名称',
  `componentPath` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '组件地址',
  `isCache` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否缓存组件',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '图标',
  `sort` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序',
  `isShow` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否显示',
  `type` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '菜单类型。1左侧展示型菜单，2页面/按钮型菜单，3操作权限型菜单',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `path`(`path`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_menus
-- ----------------------------
INSERT INTO `sys_menus` VALUES (1, 0, '系统管理', NULL, NULL, NULL, 0, 'system', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (2, 0, '运维管理', NULL, NULL, NULL, 0, 'maintenance', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (3, 0, '日志管理', NULL, NULL, NULL, 0, 'log', 3, 1, 1);
INSERT INTO `sys_menus` VALUES (4, 0, '意见反馈', '/feedback', 'Feedback', 'feedback/index', 0, 'feedback', 3, 1, 1);
INSERT INTO `sys_menus` VALUES (5, 1, '基本设置', '/sys/base-setting', 'BaseSetting', 'sys/base_setting/index', 1, 'base_setting', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (6, 1, '权限管理', NULL, NULL, NULL, 0, 'auth', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (7, 1, '部门管理', '/sys/dept', 'Dept', 'sys/dept/index', 1, 'dept', 3, 1, 1);
INSERT INTO `sys_menus` VALUES (8, 1, '岗位管理', '/sys/post', 'Post', 'sys/post/index', 1, 'post', 4, 1, 1);
INSERT INTO `sys_menus` VALUES (9, 6, '管理员管理', '/sys/auth/admin', 'Admin', 'sys/auth/admin/index', 1, 'admin', 3, 1, 1);
INSERT INTO `sys_menus` VALUES (10, 6, '角色管理', '/sys/auth/role', 'Role', 'sys/auth/role/index', 1, 'role', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (11, 6, '菜单管理', '/sys/auth/menu', 'Menu', 'sys/auth/menu/index', 1, 'menu', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (12, 9, '添加或编辑管理员', '/sys/auth/admin/add-edit', 'AddEditAdmin', 'sys/auth/admin/add_edit', 0, NULL, 1, 1, 2);
INSERT INTO `sys_menus` VALUES (13, 2, '安全设置', '/maintenance/safe-setting', 'SafeSetting', 'maintenance/safe_setting', 1, 'safe_setting', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (14, 3, '登录日志', '/log/login', 'LoginLog', 'log/login', 1, 'login_log', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (15, 3, '操作日志', '/log/operation', 'OperationLog', 'log/operation', 1, 'operation_log', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (16, 2, '异常用户', '/maintenance/abnormal', 'Abnormal', 'maintenance/abnormal', 1, 'abnormal', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (17, 2, 'IP黑名单', '/maintenance/blacklist', 'Blacklist', 'maintenance/blacklist', 1, 'blacklist', 3, 1, 1);
INSERT INTO `sys_menus` VALUES (18, 4, '意见反馈动态测试', '/feedback/test', 'FeedbackTest', 'feedback/test', 0, NULL, 1, 1, 2);
INSERT INTO `sys_menus` VALUES (19, 5, '基本设置动态测试', '/sys/base-setting/test', 'BaseSettingTest', 'sys/base_setting/test', 0, NULL, 1, 1, 2);

SET FOREIGN_KEY_CHECKS = 1;
