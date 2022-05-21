# wei-admin-api

[wei-admin](https://github.com/lajixiaohao/wei-admin)项目接口服务，基于Lumen8.3.4框架开发。 

项目使用说明：

1. 安装

   ```shell
   composer install
   ```

2. 项目基础配置

   1）在根目录下新建`.env`文件，参考`.env.exmple`配置

   2）非对称加密密钥对，存储在storage/keys目录下

   3）确保storage/logs目录权限为`0777`

3. 必要的SQL文件位于目录：storage/sqls下

4. 项目特别依赖的扩展和服务

   1）GD库；用以生成图形验证码

   2）Redis服务；用以存储图形验证码相关信息
   
5. 特别是在windows下确保` openssl.cnf `安装有效，以保证openssl相关函数正常运行

联系邮箱：2壹7陆9柒4柒3零#qq.com