
CREATE TABLE event (
    id varchar(20),
    name varchar(200),
    type varchar(50),
    time varchar(20),
    duration varchar(20),

    from_utc varchar(20),
    from_location varchar(200),
    from_iata varchar(20),

    to_utc varchar(20),
    to_location varchar(200),
    to_iata varchar(20),

    description varchar(4000)
);

create table trip_events (
    trip_id varchar(20),
    event_id varchar(20)
);

create table trip (
    id varchar(20),
    name varchar(200),
    privacy_settings varchar(200)
);

CREATE TABLE user (
    id varchar(20),
    name varchar(50),
    password varchar(20),
    fname varchar(50),
    lname varchar(50),
    email varchar(256)
);

