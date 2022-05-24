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

 Date: 24/05/2022 10:39:58
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin_depts
-- ----------------------------
DROP TABLE IF EXISTS `admin_depts`;
CREATE TABLE `admin_depts`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `deptName` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '部门名称',
  `deptIntroduce` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '部门介绍',
  `sort` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序',
  `createdAt` datetime NULL DEFAULT NULL,
  `updatedAt` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `deptName`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '部门表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_depts
-- ----------------------------
INSERT INTO `admin_depts` VALUES (1, 0, '根部门', '根部门，非实际部门。', 1, '2022-05-12 00:00:00', '2022-05-12 00:00:00');

-- ----------------------------
-- Table structure for admin_login_logs
-- ----------------------------
DROP TABLE IF EXISTS `admin_login_logs`;
CREATE TABLE `admin_login_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `adminId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `ip` varbinary(16) NULL DEFAULT NULL COMMENT '登录IP',
  `device` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '登录设备',
  `loginAt` datetime NULL DEFAULT NULL COMMENT '登录时间',
  `logoutAt` datetime NULL DEFAULT NULL COMMENT '退出登录时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '登录日志表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_login_logs
-- ----------------------------

-- ----------------------------
-- Table structure for admin_menus
-- ----------------------------
DROP TABLE IF EXISTS `admin_menus`;
CREATE TABLE `admin_menus`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '菜单名',
  `path` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '路由地址',
  `componentName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '组件名称',
  `componentPath` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '组件地址',
  `isCache` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否缓存组件',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT '图标',
  `sort` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序',
  `isShow` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否显示。1是0否',
  `type` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '菜单类型。1左侧展示型菜单，2页面/按钮型菜单，3操作权限型菜单',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `path`(`path`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 45 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_bin COMMENT = '菜单表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_menus
-- ----------------------------
INSERT INTO `admin_menus` VALUES (1, 0, '系统管理', NULL, NULL, NULL, 0, 'system', 1, 1, 1);
INSERT INTO `admin_menus` VALUES (3, 0, '日志管理', NULL, NULL, NULL, 0, 'log', 3, 1, 1);
INSERT INTO `admin_menus` VALUES (4, 0, '应用示例', NULL, NULL, NULL, 0, 'app', 4, 1, 1);
INSERT INTO `admin_menus` VALUES (5, 1, '菜单管理', '/system/menu', 'Menu', 'system/menu/index', 1, 'menu', 1, 1, 1);
INSERT INTO `admin_menus` VALUES (6, 5, '添加或编辑菜单', '/system/menu/form', 'MenuForm', 'system/menu/form', 0, NULL, 1, 1, 2);
INSERT INTO `admin_menus` VALUES (7, 5, '添加或编辑权限', '/system/menu/permission', 'Permission', 'system/menu/permission', 0, NULL, 2, 1, 2);
INSERT INTO `admin_menus` VALUES (8, 1, '角色管理', '/system/role', 'Role', 'system/role/index', 1, 'role', 2, 1, 1);
INSERT INTO `admin_menus` VALUES (9, 8, '列表', 'api:role:list', NULL, NULL, 0, NULL, 1, 0, 3);
INSERT INTO `admin_menus` VALUES (10, 8, '添加', 'api:role:add', NULL, NULL, 0, NULL, 2, 0, 3);
INSERT INTO `admin_menus` VALUES (11, 8, '编辑', 'api:role:edit', NULL, NULL, 0, NULL, 3, 0, 3);
INSERT INTO `admin_menus` VALUES (12, 8, '删除', 'api:role:remove', NULL, NULL, 0, NULL, 4, 0, 3);
INSERT INTO `admin_menus` VALUES (13, 8, '分配权限', '/system/role/permission', 'PermissionAssign', 'system/role/permission', 0, '', 5, 1, 2);
INSERT INTO `admin_menus` VALUES (14, 8, '分配权限', 'api:role:permission-assign', NULL, NULL, 0, NULL, 6, 0, 3);
INSERT INTO `admin_menus` VALUES (15, 8, '角色关系树', 'api:role:tree', NULL, NULL, 0, NULL, 7, 0, 3);
INSERT INTO `admin_menus` VALUES (16, 1, '部门管理', '/system/dept', 'Dept', 'system/dept/index', 1, 'dept', 3, 1, 1);
INSERT INTO `admin_menus` VALUES (17, 16, '部门树', 'api:dept:tree', NULL, NULL, 0, NULL, 1, 0, 3);
INSERT INTO `admin_menus` VALUES (18, 16, '添加', 'api:dept:add', NULL, NULL, 0, NULL, 2, 0, 3);
INSERT INTO `admin_menus` VALUES (19, 16, '编辑', 'api:dept:edit', NULL, NULL, 0, NULL, 3, 0, 3);
INSERT INTO `admin_menus` VALUES (20, 16, '删除', 'api:dept:remove', NULL, NULL, 0, NULL, 4, 0, 3);
INSERT INTO `admin_menus` VALUES (21, 1, '岗位管理', '/system/post', 'Post', 'system/post/index', 1, 'post', 4, 1, 1);
INSERT INTO `admin_menus` VALUES (22, 21, '列表', 'api:post:list', NULL, NULL, 0, NULL, 1, 0, 3);
INSERT INTO `admin_menus` VALUES (23, 21, '添加', 'api:post:add', NULL, NULL, 0, NULL, 2, 0, 3);
INSERT INTO `admin_menus` VALUES (24, 21, '编辑', 'api:post:edit', NULL, NULL, 0, NULL, 3, 0, 3);
INSERT INTO `admin_menus` VALUES (25, 21, '删除', 'api:post:remove', NULL, NULL, 0, NULL, 4, 0, 3);
INSERT INTO `admin_menus` VALUES (26, 1, '管理员管理', '/system/admin', 'Admin', 'system/admin/index', 1, 'admin', 5, 1, 1);
INSERT INTO `admin_menus` VALUES (27, 26, '添加或编辑管理员', '/system/admin/form', 'AdminForm', 'system/admin/form', 0, '', 1, 1, 2);
INSERT INTO `admin_menus` VALUES (28, 26, '列表', 'api:admin:list', NULL, NULL, 0, NULL, 2, 0, 3);
INSERT INTO `admin_menus` VALUES (29, 26, '添加', 'api:admin:add', NULL, NULL, 0, NULL, 3, 0, 3);
INSERT INTO `admin_menus` VALUES (30, 26, '编辑', 'api:admin:edit', NULL, NULL, 0, NULL, 4, 0, 3);
INSERT INTO `admin_menus` VALUES (31, 26, '删除', 'api:admin:remove', NULL, NULL, 0, NULL, 5, 0, 3);
INSERT INTO `admin_menus` VALUES (32, 26, '管理员关系树', 'api:admin:tree', NULL, NULL, 0, NULL, 6, 0, 3);
INSERT INTO `admin_menus` VALUES (33, 26, '重置密码', 'api:admin:modify-password', NULL, NULL, 0, NULL, 7, 0, 3);
INSERT INTO `admin_menus` VALUES (34, 26, '变更下级接管账号', 'api:admin:modify-superior', NULL, NULL, 0, NULL, 8, 0, 3);
INSERT INTO `admin_menus` VALUES (35, 26, '导出表格', 'api:admin:export', NULL, NULL, 0, NULL, 9, 0, 3);
INSERT INTO `admin_menus` VALUES (36, 3, '登录日志', '/log/login', 'LoginLog', 'log/login/index', 1, 'login_log', 1, 1, 1);
INSERT INTO `admin_menus` VALUES (37, 36, '列表', 'api:log:login', NULL, NULL, 0, NULL, 1, 0, 3);
INSERT INTO `admin_menus` VALUES (38, 3, '操作日志', '/log/operation', 'OperationLog', 'log/operation/index', 1, 'operation_log', 2, 1, 1);
INSERT INTO `admin_menus` VALUES (39, 38, '列表', 'api:log:operation', NULL, NULL, 0, NULL, 1, 0, 3);
INSERT INTO `admin_menus` VALUES (40, 4, 'wangEditor', '/demo/wangeditor', 'WangEditorDemo', 'demo/wangeditor', 1, 'fulltext', 1, 1, 1);
INSERT INTO `admin_menus` VALUES (41, 4, '密钥对工具', '/demo/rsa', 'RsaDemo', 'demo/rsa', 1, 'rsa', 2, 1, 1);
INSERT INTO `admin_menus` VALUES (42, 41, '获取密钥对', 'api:encryption:rsa', NULL, NULL, 0, NULL, 1, 0, 3);
INSERT INTO `admin_menus` VALUES (43, 4, '多级菜单', '', '', '', 1, 'menu_1', 3, 1, 1);
INSERT INTO `admin_menus` VALUES (44, 43, '多级菜单1', '/demo/thrid-menu', 'ThirdMenu', 'demo/third_menu', 1, 'menu_2', 1, 1, 1);

-- ----------------------------
-- Table structure for admin_operation_logs
-- ----------------------------
DROP TABLE IF EXISTS `admin_operation_logs`;
CREATE TABLE `admin_operation_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `adminId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `api` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '请求API',
  `describe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '操作描述',
  `ip` varbinary(16) NULL DEFAULT NULL COMMENT '请求IP',
  `device` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '操作设备',
  `createdAt` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '操作日志表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_operation_logs
-- ----------------------------

-- ----------------------------
-- Table structure for admin_posts
-- ----------------------------
DROP TABLE IF EXISTS `admin_posts`;
CREATE TABLE `admin_posts`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `postName` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '岗位名称',
  `postIntroduce` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '岗位介绍',
  `sort` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序',
  `isAble` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否启用。1是0否',
  `createdAt` datetime NULL DEFAULT NULL,
  `updatedAt` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `postName`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '岗位表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_posts
-- ----------------------------

-- ----------------------------
-- Table structure for admin_role_permissions
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_permissions`;
CREATE TABLE `admin_role_permissions`  (
  `roleId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色ID',
  `menuId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '菜单ID'
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色权限表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_role_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for admin_roles
-- ----------------------------
DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE `admin_roles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentId` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `roleName` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色名称',
  `roleIntroduce` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '角色介绍',
  `isAble` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否启用，1是0否',
  `createdAt` datetime NULL DEFAULT NULL,
  `updatedAt` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_roles
-- ----------------------------
INSERT INTO `admin_roles` VALUES (1, 0, '超级管理员', '系统超管，拥有超级权限。', 1, '2022-05-12 00:00:00', '2022-05-12 00:00:00');

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
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin_users
-- ----------------------------
INSERT INTO `admin_users` VALUES (1, 0, 'admin', '$2y$10$5VLIlb5BwCyaMfE5ATtmc.A1tn1hMcx4aaaR0sPTEJLaeJMSljT3e', 'Administrator', 1, 1, 0, 1, '2022-05-12 00:00:00', '2022-05-12 15:28:29');

SET FOREIGN_KEY_CHECKS = 1;
