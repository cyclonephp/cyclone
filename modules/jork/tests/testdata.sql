USE jork_test;

drop table if exists t_posts;
drop table if exists t_topics;
drop table if exists t_categories;
drop table if exists t_users;
drop table if exists user_contact_info;
/**/
create table t_posts(
    id int primary key auto_increment,
    name text,
    topic_fk int not null,
    user_fk int not null,
    created_at datetime,
    creator_fk int not null,
    modified_at datetime,
    modifier_fk int
);

create table t_topics (
    id int primary key auto_increment,
    name text,
    created_at datetime,
    creator_fk int not null,
    modified_at datetime,
    modifier_fk int
);

create table t_categories (
    id int primary key auto_increment,
    name text,
    moderator_fk int,
    created_at datetime,
    creator_fk int not null,
    modified_at datetime,
    modifier_fk int
);

create table t_users (
    id int primary key auto_increment,
    name varchar(64),
    password varchar(32),
    created_at datetime not null
);

create table user_contact_info (
    user_fk int not null,
    email varchar(128),
    phone_num text
);
/**/
