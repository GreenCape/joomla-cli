FROM debian:wheezy
MAINTAINER Niels Braczek <nbraczek@bsds.de>

ADD install.sh install.sh
RUN chmod +x install.sh \
    && sleep 1 \
    && ./install.sh \
    && rm install.sh

# smtp port
EXPOSE 1025

# webserver port
EXPOSE 1080

CMD ["mailcatcher", "-f", "--ip=0.0.0.0"]
