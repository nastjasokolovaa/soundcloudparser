# SoundCloud Parser

SoundCloud parser receives data from Soundcloud site using a artist link.

Source code:
[SoundCloudParser.php](src/Service/SoundCloudParser.php)

# Requirements
- PHP >= 8
- Docker
- Composer

# Installation

1. Clone 
```shell
https://github.com/nastjasokolovaa/soundcloudparser
cd soundcloudparser
```
2. Composer install
```shell
composer install
```
3. Up environment
```shell
doker compose up -d
```
4. Specify connection params in the .env file or use default.
5. Make migrations
```shell
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```
6. Start server
```shell
source .env && symfony server:start --no-tls
```

7. Search user
http://127.0.0.1:8000/?link=https://soundcloud.com/birocratic
   ![](https://i.imgur.com/8xyXrda.png)

# Run Tests
```shell
php ./vendor/bin/phpunit 
``` 
 