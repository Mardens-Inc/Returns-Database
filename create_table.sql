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
    first_name    VARCHAR(100),
    last_name     VARCHAR(100),
    street        VARCHAR(255),
    city          VARCHAR(100),
    state         VARCHAR(2),
    zip           VARCHAR(5),
    date_of_birth DATE,
    phone         VARCHAR(20),
    email         VARCHAR(100),
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
    employee INT      DEFAULT NULL,
    store    INT     NOT NULL,
    customer INT     NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (card) REFERENCES stores.gift_cards (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (employee) REFERENCES stores.employees (id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (store) REFERENCES stores.stores (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (customer) REFERENCES stores.customers (id) ON DELETE CASCADE ON UPDATE CASCADE
);