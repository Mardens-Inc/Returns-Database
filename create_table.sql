CREATE DATABASE IF NOT EXISTS stores;
use stores;

create table if not exists gift_cards
(
    id     INT AUTO_INCREMENT,
    date   DATETIME DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(10, 2) NOT NULL,
    card   VARCHAR(20)    NOT NULL,
    PRIMARY KEY (id)
);
create table if not exists customers
(
    id            INT AUTO_INCREMENT,
    first_name    text,
    last_name     text,
    address        text,
    city          text,
    state         text,
    zip           text,
    date_of_birth DATE,
    phone         text,
    email         text,
    PRIMARY KEY (id)
);
create table if not exists stores
(
    id      INT AUTO_INCREMENT,
    city    VARCHAR(100),
    address VARCHAR(255),
    PRIMARY KEY (id)
);
create table if not exists returns
(
    id       INT AUTO_INCREMENT,
    date     DATETIME DEFAULT CURRENT_TIMESTAMP,
    type     TINYINT NOT NULL,
    card     INT      DEFAULT NULL,
    employee INT      DEFAULT -1,
    store    INT     NOT NULL,
    customer INT     NOT NULL,
    PRIMARY KEY (id)
);