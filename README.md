bildersee
=========

Installation
------------

1. Create an empty MySQL database and a user with read/write rights
2. Copy default config file `config.default.php` to `config.php`
3. Edit config file in order to configure your database settings
4. Create the database structure by importing database.sql
5. Setup crontab to call `http://example.com/view/update.php` every 10 minutes (or as often as you want to run updates)
6. Default admin user/password: admin / bildersee