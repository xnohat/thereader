#!/bin/bash
echo "Enter your github username (not email):"
read githubusername
php artisan down
git reset --hard #carefully this command will destroy all changes of tracked files on server side code
git pull https://$githubusername@github.com/xnohat/threader.git master:master

