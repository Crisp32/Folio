FROM mysql:5.7

ENV MYSQL_ROOT_PASSWORD=rootpassword
ENV MYSQL_DATABASE=folio_db
ENV MYSQL_USER=user
ENV MYSQL_PASSWORD=userpassword

# Set the default authentication plugin to mysql_native_password
RUN echo "[mysqld]\ndefault-authentication-plugin=mysql_native_password" > /etc/mysql/conf.d/default-auth.cnf