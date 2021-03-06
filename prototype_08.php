<html>
<head>
<title>Timeline</title>
<link rel="stylesheet" type="text/css" href="ext-4.0.2a/resources/css/ext-all.css">
<script type="text/javascript" src="ext-4.0.2a/ext-debug.js"></script>
<script type="text/javascript">
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

        $id            = $row['id'];
        $name          = $row['name'];
        $type          = $row['type'];
        $time          = $row['time'];
        $duration      = $row['duration'];
        $from_utc      = $row['from_utc'];
        $from_location = $row['from_location'];
        $from_iata     = $row['from_iata'];
        $to_utc        = $row['to_utc'];
        $to_location   = $row['to_location'];
        $to_iata       = $row['to_iata'];
        $description   = $row['description'];

        if (!$first_event) {
            echo ",\n";
        }
        $first_event = false;
        echo "    {\n";
        echo "        id:            '$id',\n";
        echo "        name:          '$name',\n";
        echo "        type:          '$type',\n";
        echo "        time:          $time,\n";
        echo "        duration:      $duration,\n";
        echo "        from_utc:      '$from_utc',\n";
        echo "        from_location: '$from_location',\n";
        echo "        from_iata:     '$from_iata',\n";
        echo "        to_utc:        '$to_utc',\n";
        echo "        to_location:   '$to_location',\n";
        echo "        to_iata:       '$to_iata',\n";
        echo "        description:   '$description'\n";
        echo "    }";
    }
    echo "\n];\n";
}


echo "/*\n";
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
echo "*/\n";

echo "var initialTimelineWidth = 800;\n";
echo "var initialTimelineHeight = 300;\n";


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
        backgroundColor: '#66a'
    },
    gradients: [
        {
            id: 'timezonePlus3ToPlus4',
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
            id: 'timezonePlus4ToPlus3',
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
        this.width = config.width;
        this.tripEvents = config.tripEvents || [];
        this.numberOfTripEvents = this.tripEvents.length;
        this.calculateDimensions();
        config.gradients = this.gradients;

        this.items.push(this.makeGrid(config));
        this.items.push(this.makeAxis(config));
 
        if (this.numberOfTripEvents) {
            this.items.push(this.makeEvents(this.tripEvents));
        }

        Ext.apply(config, { items: this.items });
        this.callParent([config]);
    },


    calculateDimensions: function() {
        if (this.numberOfTripEvents) {
            this.firstEvent = this.tripEvents[0];
            this.lastEvent = this.tripEvents[this.numberOfTripEvents - 1];
            this.tripStart = this.firstEvent.time;
            this.tripEnd = this.lastEvent.time + this.lastEvent.duration;
            this.tripDuration = this.tripEnd - this.tripStart;
            this.timelineStart = this.tripStart - this.tripTimeMargin;
            this.timelineEnd = this.tripEnd + this.tripTimeMargin;
            this.timelineDuration = this.tripTimeMargin + this.tripDuration + this.tripTimeMargin;
            this.timelinePixelWidth = this.width;
            this.pixelsPerSecond = this.timelinePixelWidth / this.timelineDuration;
        }
    },


    makeGrid: function(config) {
        var me = this;
        var gridItems = [];
        var currentTimelinePosition = 0;
        var currentTimezone = this.tripEvents[0].from_utc;

        Ext.each(
            me.tripEvents,
            function(event) {
                var newTimelinePosition = me.calculateTimelinePosition(event.time);
                var sprite = {
                    type: 'rect',
                    fill: currentTimezone === '+3' ? 'rgb(212, 40, 40)' : 'rgb(180, 216, 42)',
                    x: currentTimelinePosition,
                    y: 0,
                    width: newTimelinePosition - currentTimelinePosition + 1,
                    height: 200
                };
                currentTimelinePosition = newTimelinePosition;
                gridItems.push(sprite);
                var newTimelinePosition = me.calculateTimelinePosition(event.time + event.duration);

                if (event.to_utc && currentTimezone !== event.to_utc) {
                    var newTimelinePosition = me.calculateTimelinePosition(event.time + event.duration);

                    var sprite2 = {
                        type: 'rect',
                        fill: Number(event.from_utc) == 3 ? 'url(#timezonePlus3ToPlus4)' : 'url(#timezonePlus4ToPlus3)',
                        x: currentTimelinePosition,
                        y: 0,
                        width: newTimelinePosition - currentTimelinePosition + 1,
                        height: 200
                    };
                    currentTimelinePosition = newTimelinePosition;
                    gridItems.push(sprite2);
                    currentTimezone = event.to_utc;
                }

                return true;
            }
        );

        var sprite = {
            type: 'rect',
            fill: currentTimezone === '+3' ? 'rgb(212, 40, 40)' : 'rgb(180, 216, 42)',
            x: currentTimelinePosition,
            y: 0,
            width: me.calculateTimelinePosition(this.timelineEnd) - currentTimelinePosition,
            height: 200
        };
        gridItems.push(sprite);

        return gridItems;
    },


    makeEvents: function(events) {
        var me = this;
        var ui_events = [];
        var i = 0;

        Ext.each(
            events,
            function(event) {
                var position = me.calculateTimelinePosition(event.time);
                var duration = Math.round(event.duration * me.pixelsPerSecond);
                var newEvent;
                if (event.type === 'flight') {
                    newEvent = {
                        type: 'rect',
                        fill: '#ffc',
                        width: duration,
                        height: 120,
                        x: position,
                        y: 10
                    };
                } else if (event.type === 'accomodation') {
                    newEvent = {
                        type: 'rect',
                        fill: '#cfc',
                        width: duration,
                        height: 120,
                        x: position,
                        y: 10
                    };
                }
                ui_events.push(newEvent);
                var label = {
                    type: 'text',
                    stroke: '#000',
                    text: event.name,
                    x: position + 10,
                    y: 20,
                    rotate: {
                        degrees: 90,
                        x: position + 10,
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
                axisItems.push({
                    type: 'text',
                    text: me.makeLocalDateString(event.time, event.from_utc),
                    x: pos1,
                    y: 200 + 15,
                    rotate: {
                        degrees: 30,
                        x: pos1 + 15,
                        y: 200 + 15
                    },
                    translation: {
                        x: 0,
                        y: 10
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
    },

    //
    // Useful links about time:
    //
    // http://www.w3schools.com/jsref/jsref_obj_date.asp
    // http://en.wikipedia.org/wiki/ISO_8601
    // http://en.wikipedia.org/wiki/UTC%2B4
    //
    makeLocalDateString: function(epoch, timezone) {
        timezone = Number(timezone);
        var date = new Date((epoch + timezone * 60 * 60) * 1000);
        var year = date.getUTCFullYear();
        var month = date.getUTCMonth() + 1;
        var day = date.getUTCDate();
        var hours = date.getUTCHours();
        var minutes = date.getUTCMinutes();
        var localDateString = [
            year,
            '-',
            month < 10 ? '0' + month : month,
            '-',
            day < 10 ? '0' + day : day,
            'T',
            hours < 10 ? '0' + hours : hours,
            ':',
            minutes < 10 ? '0' + minutes : minutes
        ].join('');
        return localDateString;
    }
});



function init_timeline() {
    var drawComponent = Ext.create('JD.Timeline', {
        width: initialTimelineWidth,
        height: initialTimelineHeight,
        renderTo: 'timeline',
        tripEvents: events
    });
}


Ext.onReady(function() {
    init_timeline();
});;


</script>
</head>
<body style="background-color: #ccf;">

<h2>Timeline</h2>
<br>
<p>
    This simple (<span style="color: red;">and resizable</span>) timeline is implemented with ExtJS's Ext.draw package
    which uses SVG (or VML for IE) under the hood. Click
    <a href="http://docs.sencha.com/ext-js/4-0/#/guide/drawing_and_charting">here</a>
    for a technical introduction.
</p>
<br>
<div id="timeline"></div>

<br>

<h3>TODO</h3>
<ul>
    <li>Drag-and-drop (or at least add) new events into the timeline.</li>
    <li>On click on the events, pop up a small window with more info about the event.</li>
    <li>Indicate timezones better. Now it is done with changing backgrounds.</li>
</ul>
<br>
<br>
<h3>IDEAS</h3>
<ul>
    <li>User-defined events.</li>
    <li>How about sub events? For instance, a user attending an event, i.e. a conference,
        may want to set up a sub-event within the conference event for, let's say a one-on-one meeting.</li>
</ul>
    
</body>
</html>

