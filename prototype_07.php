<html>
<head>
<title>Hello Ext</title>
<link rel="stylesheet" type="text/css" href="ext-4.0.2a/resources/css/ext-all.css">
<script type="text/javascript" src="ext-4.0.2a/ext-debug.js"></script>
<script type="text/javascript">
Ext.require('Ext.chart.*');
Ext.require('Ext.layout.container.Fit');

<?php

# From http://snippets.dzone.com/posts/show/7487
if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}



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
    echo "var trip_json = " . json_encode($row) . ";\n";
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


$event_column_names = array('id', 'name', 'type', 'time', 'duration', 'from_utc', 'from_location', 'from_iata', 'to_utc', 'to_location', 'to_iata', 'description');

$event_query = "select * from event where id in ('1001', '1002', '1003')";
$event_results = mysql_query($event_query);
$events = array();

if ($event_results) {
    while ($row = mysql_fetch_array($event_results)) {
        array_push($events, $row);
    }
}


function cmp_events($a, $b) {
    $a = $a['time'];
    $b = $b['time'];
    if ($a == $b) {
        return 0;
    } else {
        return ($a < $b) ? -1 : 1;
    }
}

usort($events, "cmp_events");

if ($events) {
    echo "var events = [\n";
    $first_event = true;
    foreach ($events as $row) {
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
        echo "        time: $time,\n";
        echo "        duration: $duration,\n";
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


echo "var trip_time_margin = 3 * 60 * 60; // 6 hours\n";
$num_events = sizeof($events);
if ($num_events) {
    $first_event = $events[0];
    $last_event = $events[$num_events - 1];
    echo 'var trip_start = ' . $first_event['time'] . ";\n";
    echo 'var trip_end = ' . $last_event['time'] . ' + ' . $last_event['duration'] . ";\n";
    $trip_duration = ($last_event['time'] + $last_event['duration']) - $first_event['time'];
    echo "var trip_duration = trip_end - trip_start; // {$trip_duration} seconds\n";
    echo "var timeline_start = trip_start - trip_time_margin;\n";
    echo "var timeline_end = trip_end + trip_time_margin;\n";
} else {
    echo "var trip_start = 0;\n";
    echo "var trip_end = 0;\n";
    echo "var trip_duration = 0;\n";
    echo "var timeline_start = 0;\n";
}
echo "var initial_timeline_width = 800;\n";
echo "var pixels_per_second = initial_timeline_width / (trip_time_margin + trip_duration + trip_time_margin);\n";


?>


var columns = ['id', 'name', 'type', 'time', 'duration', 'from_utc', 'from_location', 'from_iata', 'to_utc', 'to_location', 'to_iata', 'description'];



Ext.define(
    'JD.TripEvent',
    {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'id', type: 'string' },
            { name: 'name', type: 'string' },
            { name: 'type', type: 'string' },
            { name: 'time', type: 'int' },
            { name: 'duration', type: 'int' },
            { name: 'from_utc', type: 'string' },
            { name: 'from_location', type: 'string' },
            { name: 'from_iata', type: 'string' },
            { name: 'to_utc', type: 'string' },
            { name: 'to_location', type: 'string' },
            { name: 'to_iata', type: 'string' },
            { name: 'description', type: 'string' }
        ]
    }
);



function init_table() {
    var timeline_table = Ext.getDom('event_table');
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
};


Ext.define('JD.Timeline', {
    extend: 'Ext.draw.Component',
    viewBox: true, // true doesn't work very well
    resizable: {
        dynamic: true,
        pinned: true,
        handles: 's e se',
        minWidth: 600,
        minHeight: 200,
    },
    style: {
        backgroundColor: '#ccf'
    },
    gradients: [
        {
            id: 'timezone-plus-3-to-plus-4',
            angle: 0,
            stops: {
                0: {
                    color: 'rgb(212, 40, 40)'
                },
                100: {
                    color: 'rgb(180, 216, 42)'
                }
            }
        },
        {
            id: 'timezone-plus-4-to-plus-3',
            angle: 0,
            stops: {
                0: {
                    color: 'rgb(180, 216, 42)'
                },
                100: {
                    color: 'rgb(212, 40, 40)'
                }
            }
        }
    ],
    tripTimeMargin: 3 * 60 * 60, // 6 hours
    constructor: function(config) {
        this.items = [];
        this.tripEvents = config.tripEvents;
        this.numberOfTripEvents = this.tripEvents.length;
        if (this.numberOfTripEvents) {
            this.firstEvent = this.tripEvents[0];
            this.lastEvent = this.tripEvents[this.numberOfTripEvents - 1];
            this.tripStart = this.firstEvent.time;
            this.tripEnd = this.lastEvent.time + this.lastEvent.duration;
            this.tripDuration = this.tripEnd - this.tripStart;
            this.timelineStart = this.tripStart - this.tripTimeMargin;
            this.timelineEnd = this.tripEnd + this.tripTimeMargin;
            this.timelineDuration = this.tripTimeMargin + this.tripDuration + this.tripTimeMargin;
            this.timelinePixelWidth = config.width;
            this.pixelsPerSecond = this.timelinePixelWidth / this.timelineDuration;

            this.items.push(this.makeEvents(this.tripEvents));
        }
        this.items.push(this.makeAxis(config));
        Ext.apply(config, { items: this.items });
        this.callParent([config]);
    },
    makeEvents: function(events) {
        var me = this;
        var ui_events = [];
        var i = 0;

        Ext.each(
            events,
            function(event) {
                var pos = me.calculateTimelinePosition(event.time);
                var duration = Math.round(event.duration * me.pixelsPerSecond);
                var new_event = {
                    type: 'rect',
                    fill: event.type === 'flight' ? '#ffc' : '#cfc',
                    width: duration,
                    height: 180,
                    x: pos,
                    y: 10
                };
                ui_events.push(new_event);
                var label = {
                    type: 'text',
                    stroke: '#000',
                    text: event.name,
                    x: pos + 10,
                    y: 20,
                    rotate: {
                        degrees: 90,
                        x: pos + 10,
                        y: 20
                    }
                };
                ui_events.push(label);
                i++;
                return true;
            }
        );

        return ui_events;
    },
    calculateTimelinePosition: function(time) {
        return Math.round((time - this.timelineStart) * this.pixelsPerSecond);
    },
    makeAxis: function(config) {
        var me = this;
        var axisItems = [];

        axisItems.push({
            type: 'path',
            path: ['M', 0, 200, 'l', config.width, 0],
            stroke: '#000000'
        });

        Ext.each(
            this.tripEvents,
            function(event) {
                var pos1 = me.calculateTimelinePosition(event.time);
                var pos2 = me.calculateTimelinePosition(event.time + event.duration);
                axisItems.push({
                    type: 'path',
                    path: ['M', pos1, 200, 'l', 0, 10],
                    stroke: '#000000'
                });
                var date1 = new Date((event.time + Number(event.from_utc) * 60 * 60) * 1000);
                var date1_str = [
                    date1.getUTCFullYear(),
                    '-',
                    date1.getUTCMonth() < 9 ? '0' + (date1.getUTCMonth() + 1) : (date1.getUTCMonth() + 1),
                    '-',
                    date1.getUTCDate() < 9 ? '0' + date1.getUTCDate() : date1.getUTCDate(),
                    'T',
                    date1.getUTCHours() < 9 ? '0' + (date1.getUTCHours() + 1) : (date1.getUTCHours() + 1),
                    ':',
                    date1.getUTCMinutes() < 9 ? '0' + (date1.getUTCMinutes() + 1) : (date1.getUTCMinutes() + 1)
                ].join('');
                axisItems.push({
                    type: 'text',
                    text: date1_str,
                    x: pos1,
                    y: 200 + 15,
                    rotate: {
                        degrees: 30,
                        x: pos1 + 15,
                        y: 200 + 15
                    }
                });
                axisItems.push({
                    type: 'path',
                    path: ['M', pos2, 200, 'l', 0, 10],
                    stroke: '#000000'
                });
                return true;
            }
        );

        return axisItems;
    }
});



function init_timeline() {
    var drawComponent = Ext.create('JD.Timeline', {
        width: initial_timeline_width,
        height: 300,
        renderTo: 'timeline',
        tripEvents: events
    });
}


/*
function init_timeline_chart() {

    var store = Ext.create('Ext.data.Store', {
        model: 'JD.TripEvent',
        data: events
    });


    var chart = Ext.create('Ext.chart.Chart', {
        renderTo: 'timeline',
        width: initial_timeline_width,
        height: 300,
        //theme: 'Base',
        store: store,
        axes: [
            {
                title: 'Duration',
                type: 'Numeric',
                position: 'left',
                fields: ['duration'],
                decimals: 0,
                minimum: 0
            },
            {
                title: 'Time',
                type: 'Numeric',
                position: 'bottom',
                fields: ['time'],
                decimals: 0,
                minimum: timeline_start,
                maximum: timeline_end,
                label: {
                    rotate: {
                        degrees: 90
                    }
                }
            }
        ],
        series: [
            {
                type: 'line',
                xField: 'time',
                yField: 'duration'
            }
        ]

    });

}
*/

Ext.onReady(function() {
    init_table();
    init_timeline();
});;


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
<div id="timeline"></div>
<br>

<h2>Note that the table is sorted in this prototype.</h2>
<br>
<table id="event_table" cellspacing="2" border="1" cellpadding="2"></table>

</body>
</html>

