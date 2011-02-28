drop table if exists serusers;

drop sequence if exists serusers_id_seq;

drop table if exists users;

drop sequence if exists seq_users;

create sequence seq_users;

create table users(
    id integer primary key default nextval('seq_users'),
    name varchar(256) not null
);

create table serusers(
    id serial primary key,
    name varchar(256)
);

insert into users (name) values ('user1'), ('user2');

insert into serusers (name) values ('user1'), ('user2');