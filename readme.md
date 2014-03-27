## Waa application

### Development
1. mysql.server start
2. supervisord
3. brew services start redis
4. mongod --fork

### Requirement
1. PHP5.5
2. Composer
3. Bower
4. Grunt
5. Redis
6. MySQL
7. MongoDB
8. Supervisor

### Install
1. `composer install`
2. `bower install`
3. `grunt`
4. set up supervisor

#### supervisor setting

    [program:waaQueue]
    command                 = php artisan queue:work
    directory               = /Users/yansu/Sites/waa
    process_name            = %(program_name)s_%(process_num)s
    numprocs                = 3
    autostart               = true
    autorestart             = true
    stdout_logfile          = /Users/yansu/Sites/waa/app/storage/logs/supervisor_waaQueue.log
    stdout_logfile_maxbytes = 10MB
    stderr_logfile          = /Users/yansu/Sites/waa/app/storage/logs/supervisor_waaQueue.log
    stderr_logfile_maxbytes = 10MB

