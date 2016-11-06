
* 使某服务自动启动
    `chkconfig --level 3 httpd  on` --> `systemctl enable httpd.service`

* 使某服务不自动启动
    `chkconfig --level 3 httpd off` --> `systemctl disable httpd.service`

* 检查服务状态
    `service httpd status` --> `systemctl status httpd.service`(服务详细信息) 或 `systemctl is-active httpd.service`(仅显示是否 Active)

* 显示所有已启动的服务
    `chkconfig --list` --> `systemctl list-units --type=service`

* 启动某服务
    `service httpd start` --> `systemctl start httpd.service`

* 停止某服务
    `service httpd stop` --> `systemctl stop httpd.service`

* 重启某服务
    `service httpd restart` --> `systemctl restart httpd.service`




