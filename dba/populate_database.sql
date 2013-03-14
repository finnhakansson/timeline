
insert into user values ('1', 'francesco', 'abc', 'Francesco', 'Guerrieri', 'francescog@gmail.com');
insert into user values ('2', 'finn', '123', 'Finn', 'Hakansson', 'finn_hakansson@yahoo.com');

insert into trip values ('101', 'my short trip', 'public');
-- insert into trip values ('102', 'around the world', 'public');

insert into event values (
    '1001',
    'Dubai to Jeddah',
    'flight',
    '1312378200', -- 2011-08-03T13:30:00Z
    '9600',
    '+4',
    'Dubai',
    'DXB',
    '+3',
    'Jeddah',
    'JED',
    'Flight to Jeddah, blah, blah, blah'
);

insert into event values (
    '1002',
    'Jeddah to Dubai',
    'flight',
    '1312519800', -- 2011-08-05T04:50:00Z
    '9600',
    '+3',
    'Jeddah',
    'JED',
    '+4',
    'Dubai',
    'DXB',
    'Return flight to Dubai, blah, blah, blah'
);

insert into event values (
    '1003',
    'Hotel in Jeddah',
    'accomodation',
    '1312396200', -- 2011-08-03T18:30:00Z
    '111600',
    '+3',
    'Jeddah',
    '',
    '',
    '',
    '',
    'Description about the hotel...'
);


insert into trip_events values ('101', '1001');
insert into trip_events values ('101', '1002');
insert into trip_events values ('101', '1003');

