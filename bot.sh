#!/bin/sh
if [ -f "bot.pid" ] ; then 
  kill `cat bot.pid`
  rm -f bot.pid 
fi;
php bot.php &
