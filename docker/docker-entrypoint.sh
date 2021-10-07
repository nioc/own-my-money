#!/bin/sh
set -e

echo -n "update gui javascript with environment variables... "

cd /var/www/own-my-money/front/js/ &&
find . -name "*" -type f -exec sed -i "s|http://localhost|$OMM_BASE_URI|g" {} \;

echo "done"

echo -n "update server configuration.ini with environment variables... "

CONFIGURATION=/var/www/own-my-money/server/configuration/configuration.ini

if [ ! -w $CONFIGURATION ]; then
  echo "configuration.ini file is not writable"
  exit 1
fi

if [ "$OMM_DB_HOST" != "" ]; then
  sed -i "s|dbHost = localhost|dbHost = $OMM_DB_HOST|g" $CONFIGURATION
fi

if [ "$OMM_DB_USER" != "" ]; then
  sed -i "s|dbUser = money|dbUser = $OMM_DB_USER|g" $CONFIGURATION
fi

if [ "$OMM_DB_PWD" != "" ]; then
  sed -i "s|dbPwd = money|dbPwd = $OMM_DB_PWD|g" $CONFIGURATION
fi

if [ "$OMM_DB_NAME" != "" ]; then
  sed -i "s|dbName = money|dbName = $OMM_DB_NAME|g" $CONFIGURATION
fi

if [ "$OMM_MAILER" != "" ]; then
  sed -i "s|mailer = 0|mailer = $OMM_MAILER|g" $CONFIGURATION
fi

if [ "$OMM_MAIL_SENDER" != "" ]; then
  sed -i "s|mailSender = admin@money.domain.com|mailSender = $OMM_MAIL_SENDER|g" $CONFIGURATION
fi

if [ "$OMM_HASH_KEY" != "" ]; then
  sed -i "s|hashKey = ab§CDyz12*12|hashKey = $OMM_HASH_KEY|g" $CONFIGURATION
fi

if [ "$OMM_SETUP" != "" ]; then
  sed -i "s|setup = 0|setup = 1|g" $CONFIGURATION
fi

echo "done"

if [ "${1#-}" != "$1" ]; then
        set -- apache2-foreground "$@"
fi
exec "$@"

exit 0
