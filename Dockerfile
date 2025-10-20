FROM sail-8.4/app

RUN apt-get update \
 && apt-get install -y --no-install-recommends msmtp msmtp-mta ca-certificates \
 && rm -rf /var/lib/apt/lists/*

# sendmail 互換
RUN ln -sf /usr/bin/msmtp /usr/sbin/sendmail

# msmtp 設定（Mailpit に中継）
RUN printf "%s\n" \
"defaults" \
"account default" \
"host mailpit" \
"port 1025" \
"tls off" \
"auth off" \
"from noreply@example.local" \
> /etc/msmtprc && chmod 600 /etc/msmtprc
