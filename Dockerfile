FROM centos:centos8

# Enable additional repositories
RUN dnf install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm \
    && dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm

# Install PHP8
RUN dnf module enable php:remi-8.0 -y \
    && dnf install -y php php-cli php-common

# Add CLI
ADD ./CloudObjects-CLI.phar /usr/local/bin/cloudobjects

# Make CLI executable
RUN chmod +x /usr/local/bin/cloudobjects

# Set entrypoint
ENTRYPOINT [ "/usr/local/bin/cloudobjects" ]