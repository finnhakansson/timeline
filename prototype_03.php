<html>
<head>
<title>Hello Ext</title>
<link rel="stylesheet" type="text/css" href="ext-4.0.2a/resources/css/ext-all.css">
<script type="text/javascript" src="ext-4.0.2a/ext-debug.js"></script>
<script type="text/javascript">
<?php

error_reporting(E_ALL);

# Connect to database.
$con = mysql_connect("localhost", "finn", "123456");
if (!$con) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('test');


$trip_query = "select id, name from trip where id = '101'";

$trip = mysql_query($trip_query);
if ($trip) {
    $row = mysql_fetch_array($trip);
    $id = $row["id"];
    $name = $row["name"];
    echo "var trip = { id: '$id', name: '$name' };\n";
}


$trip_events_query = "select event_id from trip_events where trip_id = '101'";
$trip_events_result = mysql_query($trip_events_query);
if ($trip_events_result) {
    echo "var trip_events = [";
    $first = true;
    while ($row = mysql_fetch_array($trip_events_result)) {
        if (!$first) {
            echo ", ";
        }
        $event_id = $row["event_id"];
        echo "'$event_id'";
        $first = false;
    }
    echo "];\n";
}



$event_query = "select * from event where id in ('1001', '1002', '1003')";
$event_results = mysql_query($event_query);
if ($event_results) {
    echo "var events = [\n";
    $first_event = true;
    while ($row = mysql_fetch_array($event_results)) {
        if (!$first_event) {
            echo ",\n";
        }
        echo "    {\n";
        $id = $row['id'];
        $name = $row['name'];
        $type = $row['type'];
        $time = $row['time'];
        $duration = $row['duration'];
        $from_utc = $row['from_utc'];
        $from_location = $row['from_location'];
        $from_iata = $row['from_iata'];
        $to_utc = $row['to_utc'];
        $to_location = $row['to_location'];
        $to_iata = $row['to_iata'];
        $description = $row['description'];
        echo "        id: '$id',\n";
        echo "        name: '$name',\n";
        echo "        type: '$type',\n";
        echo "        time: '$time',\n";
        echo "        duration: '$duration',\n";
        echo "        from_utc: '$from_utc',\n";
        echo "        from_location: '$from_location',\n";
        echo "        from_iata: '$from_iata',\n";
        echo "        to_utc: '$to_utc',\n";
        echo "        to_location: '$to_location',\n";
        echo "        to_iata: '$to_iata',\n";
        echo "        description: '$description'\n";
        echo "    }";
        $first_event = false;
    }
    echo "\n];\n";
}



?>

events = events.sort(
    function(a, b) {
        return a.time > b.time;
    }
);

var columns = ['id', 'name', 'type', 'time', 'duration', 'from_utc', 'from_location', 'from_iata', 'to_utc', 'to_location', 'to_iata'];


Ext.onReady(function() {
    var timeline_table = Ext.getDom('timeline');
    var temp_list = '';

    temp_list += '<tr>';
    Ext.each(
        columns,
        function(column) {
            temp_list += '<td>';
            temp_list += column;
            temp_list += '</td>';
            return true;
        }
    );
    temp_list += '</tr>\n';

    Ext.each(
        events,
        function(event) {
            temp_list += '<tr>';
            Ext.each(
                columns,
                function(column) {
                    temp_list += '<td>';
                    temp_list += event[column];
                    temp_list += '</td>';
                    return true;
                }
            );
            temp_list += '</tr>\n';
            return true;
        }
    );
    timeline_table.innerHTML = temp_list;

    var ui_events = [];
    var i = 0;
    Ext.each(
        events,
        function(event) {
            var new_event = {
                type: 'rect',
                fill: event.type === 'flight' ? '#ffc' : '#cfc',
                width: 100,
                height: 250,
                x: 50 + i * 200,
                y: 10
            };
            ui_events.push(new_event);
            var label = {
                type: 'text',
                stroke: '#000',
                text: event.name,
                x: 50 + i * 200 + 10,
                y: 30
            };
            ui_events.push(label);
            i++;
            return true;
        }
    );

    var drawComponent = Ext.create('Ext.draw.Component', {
        viewBox: false,
        width: 800,
        height: 300,
        renderTo: 'new_timeline',
        style: {
            backgroundColor: '#ccf'
        },
        items: ui_events
    });


    //drawComponent.surface.add(ui_events);

});

</script>
</head>
<body>

<h2>Ugly Timeline</h2>
<br>
<p>
    This ugly timeline is implemented with ExtJS's Ext.draw package
    which uses SVG and VML (for IE) under the hood. Click
    <a href="http://docs.sencha.com/ext-js/4-0/#/guide/drawing_and_charting">here</a>
    for a technical introduction.
</p>
<br>
<div id="new_timeline"></div>
<br>

<h2>Note that the table is sorted in this prototype.</h2>
<br>
<table id="timeline" cellspacing="2" border="1" cellpadding="2"></table>

</body>
</html>

