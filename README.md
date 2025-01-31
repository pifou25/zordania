# Zordania

A PHP/MySQL Browser Based Medieval Fantasy Game.

http://zordania.fr
discord : https://discord.gg/fjGXrkY

Installation
------------

1. Clone the repository.
2. Copy /v2/conf/secret_parameters.php.default to /v2/conf/secret_parameters.php and alter the settings as appropriate.

Docker Installation
------------

1. Clone the repository.
2. Copy v2/scripts/.env.default to v2/scripts/.env and alter the settings as appropriate.
3. Run the following command to build the docker image and start the containers:
```
cd v2/scripts
docker compose up -d
```
4. go to http://localhost:8088

### services
3 services are running;
- apache & php 
- mariadb with initialized database
- scheduler that trigger the cron every 5 minutes

[![CodeFactor](https://www.codefactor.io/repository/github/pifou25/zordania/badge)](https://www.codefactor.io/repository/github/pifou25/zordania)