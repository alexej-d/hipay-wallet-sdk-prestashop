FROM prestashop/prestashop:1.6.1.7

MAINTAINER Johan PROTIN <jprotin@hipay.com>

RUN apt-get install -y ssmtp && echo "sendmail_path = /usr/sbin/ssmtp -t" > /usr/local/etc/php/conf.d/sendmail.ini \
    && echo "mailhub=smtp:1025\nUseTLS=NO\nFromLineOverride=YES" > /etc/ssmtp/ssmtp.conf

COPY conf /tmp
COPY src /var/www/html/modules
RUN sed -i "/exec apache2 -DFOREGROUND/d" /tmp/docker_run.sh \
    && sed -i "/Almost ! Starting Apache now/d" /tmp/docker_run.sh \
    	&& mv /tmp/hipay_install.php /var/www/html

ENTRYPOINT ["/tmp/entrypoint.sh"]