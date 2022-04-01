# wei-admin-api

 wei-admin项目接口服务，基于Lumen8.3.4框架开发。 

项目使用说明：

1. 安装

   ```shell
   composer install
   ```

2. 项目基础配置

   1）打开.env文件配置数据库（mysql、redis）链接、appkey、token、资源服务器、时区等

   2）打开configs/filesystem.php文件配置使用SFTP上传至资源服务器相关信息

   3）生成非对称密钥对，存储在keys目录下

3. 必要的SQL文件位于目录：storage/sqls下

4. 项目特别依赖的扩展和服务

   1）GD库；用以生成图形验证码

   2）Redis服务；用以存储图形验证码相关信息

联系邮箱：2壹7陆9柒4柒3零#qq.com