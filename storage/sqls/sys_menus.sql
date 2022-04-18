/*
 Navicat MySQL Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80028
 Source Host           : localhost:3306
 Source Schema         : wei-admin

 Target Server Type    : MySQL
 Target Server Version : 80028
 File Encoding         : 65001

 Date: 18/04/2022 19:46:40
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
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_menus
-- ----------------------------
INSERT INTO `sys_menus` VALUES (1, 0, '系统管理', NULL, NULL, NULL, 0, 'system', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (2, 0, '运维管理', NULL, NULL, NULL, 0, 'maintenance', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (3, 0, '日志管理', NULL, NULL, NULL, 0, 'log', 3, 1, 1);
INSERT INTO `sys_menus` VALUES (4, 0, '应用示例', NULL, NULL, NULL, 0, 'app', 4, 1, 1);
INSERT INTO `sys_menus` VALUES (5, 1, '菜单管理', '/sys/menu', 'Menu', 'sys/menu/index', 1, 'menu', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (6, 1, '角色管理', '/sys/role', 'Role', 'sys/role/index', 1, 'role', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (7, 1, '管理员管理', '/sys/admin', 'Admin', 'sys/admin/index', 1, 'admin', 3, 1, 1);
INSERT INTO `sys_menus` VALUES (8, 1, '部门管理', '/sys/dept', 'Dept', 'sys/dept/index', 1, 'dept', 4, 1, 1);
INSERT INTO `sys_menus` VALUES (9, 1, '岗位管理', '/sys/post', 'Post', 'sys/post/index', 1, 'post', 5, 1, 1);
INSERT INTO `sys_menus` VALUES (10, 2, '安全设置', '/maintenance/safe-setting', 'SafeSetting', 'maintenance/safe_setting/index', 1, 'safe_setting', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (11, 2, '异常用户', '/maintenance/abnormal-admin', 'AbnormalAdmin', 'maintenance/abnormal_admin/index', 1, 'abnormal', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (12, 2, 'IP黑名单', '/maintenance/blacklist', 'Blacklist', 'maintenance/blacklist/index', 1, 'blacklist', 3, 1, 1);
INSERT INTO `sys_menus` VALUES (13, 3, '登录日志', '/log/login', 'LoginLog', 'log/login/index', 1, 'login_log', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (14, 3, '操作日志', '/log/operation', 'OperationLog', 'log/operation/index', 1, 'operation_log', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (15, 4, 'wangEditor', '/demo/wangeditor', 'WangEditorDemo', 'demo/wangeditor', 1, 'fulltext', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (16, 4, '密钥对工具', '/demo/rsa', 'RsaDemo', 'demo/rsa', 1, 'rsa', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (17, 4, '二级菜单', NULL, NULL, NULL, 0, 'menu_1', 3, 1, 1);
INSERT INTO `sys_menus` VALUES (18, 17, '三级菜单', '/demo/thrid-menu', 'ThirdMenu', 'demo/third_menu', 1, 'menu_2', 1, 1, 1);

SET FOREIGN_KEY_CHECKS = 1;
