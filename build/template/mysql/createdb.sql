
CREATE DATABASE IF NOT EXISTS `${environment.database.name}` CHARACTER SET 'utf8';
GRANT ALL ON `${environment.database.name}`.* TO '${environment.database.user}'@'%';
USE `${environment.database.name}`;
