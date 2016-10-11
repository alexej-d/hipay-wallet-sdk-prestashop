#!/bin/sh

#!/bin/sh -e

/tmp/docker_run.sh

#install module HiPay

echo "\n* Starting install module HiPay ...";

php /var/www/html/hipay_install.php

echo "\n* End HiPay treatment ...";


echo "\n* Almost ! Starting Apache now\n";
exec apache2 -DFOREGROUND

