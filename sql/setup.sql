CREATE USER IF NOT EXISTS 'MyApp'@'%' IDENTIFIED BY '***';
GRANT ALL PRIVILEGES ON *.* TO 'MyApp'@'%' IDENTIFIED BY '***' REQUIRE NONE WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
CREATE DATABASE IF NOT EXISTS `MyApp`;
GRANT ALL PRIVILEGES ON `MyApp`.* TO 'MyApp'@'%';
GRANT ALL PRIVILEGES ON `MyApp\_%`.* TO 'MyApp'@'%';

use MyApp;
CREATE TABLE IF NOT EXISTS `users` (
    ID int NOT NULL AUTO_INCREMENT,
    email varchar(250),
    password varchar(500),
    first varchar(250),
    last varchar(250),
    authid varchar(1000),
    created_date datetime DEFAULT CURRENT_TIMESTAMP,
    updated_date datetime ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (ID)
);

use MyApp;
CREATE TABLE IF NOT EXISTS `passwordrecovery` (
  `passwordrecoveryid` int NOT NULL AUTO_INCREMENT,
  `userid` varchar(10) NOT NULL,
  `token` varchar(250) NOT NULL,
  `expire_date` varchar(250) NOT NULL,
  `created_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`passwordrecoveryid`)
)

