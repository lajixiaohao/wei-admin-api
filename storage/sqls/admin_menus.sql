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

 Date: 10/11/2021 10:19:44
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin_menus
-- ----------------------------
DROP TABLE IF EXISTS `admin_menus`;
CREATE TABLE `admin_menus`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '菜单名',
  `path` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '路径uri',
  `component_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '组件名称',
  `component_path` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '组件地址',
  `is_cache` tinyint(0) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否缓存组件',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '图标',
  `menu_type` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '菜单类型。1左侧展示型菜单，2页面/按钮型菜单，3操作权限型菜单',
  `is_show` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否显示',
  `sort` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `path`(`path`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 39 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_menus
-- ----------------------------
INSERT INTO `admin_menus` VALUES (1, 0, '系统设置', '', NULL, NULL, 0, 'system', 1, 1, 1);
INSERT INTO `admin_menus` VALUES (2, 1, '菜单管理', '/system/menu', 'Menu', 'system/menu/index', 1, 'menu', 1, 1, 1);
INSERT INTO `admin_menus` VALUES (3, 1, '角色管理', '/system/role', 'Role', 'system/role/index', 1, 'role', 1, 1, 2);
INSERT INTO `admin_menus` VALUES (4, 1, '管理员管理', '/system/admin', 'Admin', 'system/admin/index', 1, 'admin', 1, 1, 5);
INSERT INTO `admin_menus` VALUES (5, 1, '部门管理', '/system/department', 'Department', 'system/department/index', 1, 'department', 1, 1, 3);
INSERT INTO `admin_menus` VALUES (6, 1, '岗位管理', '/system/post', 'Post', 'system/post/index', 1, 'post', 1, 1, 4);
INSERT INTO `admin_menus` VALUES (7, 2, '菜单管理使用说明', '/system/menu/instruction', 'MenuInstruction', 'system/menu/instruction', 1, NULL, 2, 1, 1);
INSERT INTO `admin_menus` VALUES (8, 3, '列表', 'api:role:list', NULL, NULL, 0, NULL, 3, 0, 1);
INSERT INTO `admin_menus` VALUES (9, 3, '编辑', 'api:role:edit', NULL, NULL, 0, NULL, 3, 0, 3);
INSERT INTO `admin_menus` VALUES (10, 3, '删除', 'api:role:remove', NULL, NULL, 0, NULL, 3, 0, 4);
INSERT INTO `admin_menus` VALUES (11, 3, '角色关系树', 'api:role:tree', NULL, NULL, 0, NULL, 3, 0, 5);
INSERT INTO `admin_menus` VALUES (12, 3, '权限分配', 'api:role:permission-assign', NULL, NULL, 0, NULL, 3, 0, 6);
INSERT INTO `admin_menus` VALUES (13, 3, '添加', 'api:role:add', NULL, NULL, 0, NULL, 3, 0, 2);
INSERT INTO `admin_menus` VALUES (14, 5, '部门树结构', 'api:department:tree', NULL, NULL, 0, NULL, 3, 0, 1);
INSERT INTO `admin_menus` VALUES (15, 5, '添加', 'api:department:add', NULL, NULL, 0, NULL, 3, 0, 2);
INSERT INTO `admin_menus` VALUES (16, 5, '编辑', 'api:department:edit', NULL, NULL, 0, NULL, 3, 0, 3);
INSERT INTO `admin_menus` VALUES (17, 5, '删除', 'api:department:remove', NULL, NULL, 0, NULL, 3, 0, 4);
INSERT INTO `admin_menus` VALUES (18, 6, '列表', 'api:post:list', NULL, NULL, 0, NULL, 3, 0, 1);
INSERT INTO `admin_menus` VALUES (19, 6, '添加', 'api:post:add', NULL, NULL, 0, NULL, 3, 0, 2);
INSERT INTO `admin_menus` VALUES (20, 6, '编辑', 'api:post:edit', NULL, NULL, 0, NULL, 3, 0, 3);
INSERT INTO `admin_menus` VALUES (21, 6, '删除', 'api:post:remove', NULL, NULL, 0, NULL, 3, 0, 4);
INSERT INTO `admin_menus` VALUES (22, 4, '列表', 'api:admin:list', NULL, NULL, 0, NULL, 3, 0, 1);
INSERT INTO `admin_menus` VALUES (23, 4, '添加', 'api:admin:add', NULL, NULL, 0, NULL, 3, 0, 2);
INSERT INTO `admin_menus` VALUES (24, 4, '编辑', 'api:admin:edit', NULL, NULL, 0, NULL, 3, 0, 3);
INSERT INTO `admin_menus` VALUES (25, 4, '删除', 'api:admin:remove', NULL, NULL, 0, NULL, 3, 0, 4);
INSERT INTO `admin_menus` VALUES (26, 4, '管理员关系树', 'api:admin:tree', NULL, NULL, 0, NULL, 3, 0, 5);
INSERT INTO `admin_menus` VALUES (27, 4, '重置密码', 'api:admin:modify-password', NULL, NULL, 0, NULL, 3, 0, 6);
INSERT INTO `admin_menus` VALUES (28, 4, '变更下级接管账号', 'api:admin:change-takeover', NULL, NULL, 0, NULL, 3, 0, 7);
INSERT INTO `admin_menus` VALUES (29, 0, '富文本编辑器', '', '', '', 0, 'fulltext_1', 1, 1, 3);
INSERT INTO `admin_menus` VALUES (30, 29, 'wangeditor', '/wangeditor', 'WangEditor', 'rich_text/wangeditor', 1, 'fulltext_2', 1, 1, 1);
INSERT INTO `admin_menus` VALUES (31, 0, '日志管理', '', '', '', 0, 'log', 1, 1, 4);
INSERT INTO `admin_menus` VALUES (32, 31, '登录日志', '/log/login', 'LoginLog', 'log/login/index', 1, 'login_log', 1, 1, 1);
INSERT INTO `admin_menus` VALUES (33, 31, '操作日志', '/log/operation', 'OperationLog', 'log/operation/index', 1, 'operation_log', 1, 1, 2);
INSERT INTO `admin_menus` VALUES (34, 32, '列表', 'api:log:login', NULL, NULL, 0, NULL, 3, 0, 1);
INSERT INTO `admin_menus` VALUES (35, 33, '列表', 'api:log:operation', NULL, NULL, 0, NULL, 3, 0, 1);
INSERT INTO `admin_menus` VALUES (36, 0, '加密应用', '', '', '', 0, 'encryption_1', 1, 1, 2);
INSERT INTO `admin_menus` VALUES (37, 36, '非对称加密', '/encryption/rsa', 'RsaEncryption', 'encryption/rsa', 1, 'encryption_2', 1, 1, 1);
INSERT INTO `admin_menus` VALUES (38, 37, '生成密钥', 'api:encryption:rsa', NULL, NULL, 0, NULL, 3, 0, 1);

SET FOREIGN_KEY_CHECKS = 1;
