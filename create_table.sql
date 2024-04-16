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
create table if not exists returns_addr
(
    id     INT AUTO_INCREMENT,
    street VARCHAR(255),
    city   VARCHAR(100),
    state  VARCHAR(2),
    PRIMARY KEY (id)
);
create table if not exists locations
(
    id      INT AUTO_INCREMENT,
    city    VARCHAR(100),
    address VARCHAR(255),
    PRIMARY KEY (id)
);
create table if not exists returns
(
    id            INT AUTO_INCREMENT,
    date          DATETIME DEFAULT CURRENT_TIMESTAMP,
    first_name    VARCHAR(100),
    last_name     VARCHAR(100),
    type          TINYINT NOT NULL,
    card          INT      DEFAULT NULL,
    employee      INT     NOT NULL,
    store         INT     NOT NULL,
    customer_addr INT     NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (card) REFERENCES stores.gift_cards (id),
    FOREIGN KEY (employee) REFERENCES stores.employees (id),
    FOREIGN KEY (store) REFERENCES stores.locations (id),
    FOREIGN KEY (customer_addr) REFERENCES stores.returns_addr (id)
);