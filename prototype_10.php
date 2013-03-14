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
            { name: 'id',            type: 'string' },
            { name: 'name',          type: 'string' },
            { name: 'type',          type: 'string' },
            { name: 'time',          type: 'int'    },
            { name: 'duration',      type: 'int'    },
            { name: 'from_utc',      type: 'string' },
            { name: 'from_location', type: 'string' },
            { name: 'from_iata',     type: 'string' },
            { name: 'to_utc',        type: 'string' },
            { name: 'to_location',   type: 'string' },
            { name: 'to_iata',       type: 'string' },
            { name: 'description',   type: 'string' }
        ]
    }
);



Ext.define('JD.Timeline', {
    extend: 'Ext.draw.Component',
    tripEvents: [],
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
    tripTimeMargin: 3 * 60 * 60, // 6 hours


    constructor: function(config) {
        this.items = [];
        this.width = config.width;
        this.height = config.height;
        this.eventStore = Ext.create('Ext.data.Store', {
            model: 'JD.TripEvent',
            data: config.tripEvents || [],
            sorters: [
                {
                    property: 'time',
                    direction: 'ASC'
                }
            ]
        });
        delete config.tripEvents;
        this.calculateDimensions();
        this.gradients = this.makeGradients();
        config.gradients = this.gradients;
        this.items = this.buildTimeline();
        Ext.apply(config, { items: this.items });
        this.callParent([config]);
    },

    dumpStore: function() {
        this.eventStore.each(function(record) {
            console.log(record);
        });
    },


    buildTimeline: function() {
        var sprites = [].concat(
            this.makeAxis(),
            this.makeGrid(),
            this.makeAxisSteps(),
            this.makeEvents()
        );
        return sprites;
    },


    rebuildTimeline: function() {
        var sprites = this.buildTimeline();
        Ext.Array.forEach(
            sprites,
            function(sprite) {
                this.surface.add(sprite);
                sprite.show(true);
            },
            this
        );
    },


    calculateDimensions: function() {
        if (this.eventStore.count() > 0) {
            var last = this.eventStore.last().data;
            this.tripStart = this.eventStore.first().data.time;
            this.tripEnd = last.time + last.duration;
            this.tripDuration = this.tripEnd - this.tripStart;
            this.timelineStart = this.tripStart - this.tripTimeMargin;
            this.timelineEnd = this.tripEnd + this.tripTimeMargin;
            this.timelineDuration = this.tripTimeMargin + this.tripDuration + this.tripTimeMargin;
            this.timelinePixelWidth = this.width;
            this.pixelsPerSecond = this.timelinePixelWidth / this.timelineDuration;
        }
    },


    makeAxis: function() {
        return Ext.create('Ext.draw.Sprite', {
            type: 'path',
            path: ['M', 0, 200, 'l', this.width, 0],
            stroke: '#000000'
        });
    },


    makeGrid: function() {
        var me = this;
        var gridItems = [];
        var currentTimelinePosition = 0;
        var currentTimezone = this.eventStore.getAt(0).data.from_utc;
        // TODO: Don't parse the same timezone multiple times.

        this.eventStore.each(function(record) {
            var event = record.data;
            var newTimelinePosition = me.calculateTimelinePosition(event.time);
            var sprite = Ext.create('Ext.draw.Sprite', {
                type: 'rect',
                fill: me.makeColor(me.parseTimezone(currentTimezone)),
                x: currentTimelinePosition,
                y: 0,
                width: newTimelinePosition - currentTimelinePosition + 1,
                height: 200
            });
            currentTimelinePosition = newTimelinePosition;
            gridItems.push(sprite);
            var newTimelinePosition = me.calculateTimelinePosition(event.time + event.duration);

            if (event.to_utc && currentTimezone !== event.to_utc) {
                var newTimelinePosition = me.calculateTimelinePosition(event.time + event.duration);
                var tz1 = me.parseTimezone(event.from_utc);
                var tz2 = me.parseTimezone(event.to_utc);

                var sprite2 = Ext.create('Ext.draw.Sprite', {
                    type: 'rect',
                    fill: 'url(#timezone_' + tz1.hours + '_' + tz2.hours + ')',
                    x: currentTimelinePosition,
                    y: 0,
                    width: newTimelinePosition - currentTimelinePosition + 1,
                    height: 200
                });
                currentTimelinePosition = newTimelinePosition;
                gridItems.push(sprite2);
                currentTimezone = event.to_utc;
            }

            return true;
        });

        var sprite = Ext.create('Ext.draw.Sprite', {
            type: 'rect',
            fill: this.makeColor(this.parseTimezone(currentTimezone)),
            //fill: currentTimezone === '+3' ? 'rgb(212, 40, 40)' : 'rgb(180, 216, 42)',
            x: currentTimelinePosition,
            y: 0,
            width: me.calculateTimelinePosition(this.timelineEnd) - currentTimelinePosition,
            height: 200
        });
        gridItems.push(sprite);

        return gridItems;
    },


    makeEvents: function() {
        var me = this;
        var ui_events = [];

        this.eventStore.each(function(record) {
            var event = record.data;
            var position = me.calculateTimelinePosition(event.time);
            var duration = Math.round(event.duration * me.pixelsPerSecond);

            var newEvent;
            if (event.type === 'flight') {
                newEvent = Ext.create('Ext.draw.Sprite', {
                    type: 'rect',
                    fill: '#ffc',
                    width: duration,
                    height: 120,
                    x: position,
                    y: 10
                });
            } else if (event.type === 'accomodation') {
                newEvent = Ext.create('Ext.draw.Sprite', {
                    type: 'rect',
                    fill: '#cfc',
                    width: duration,
                    height: 120,
                    x: position,
                    y: 10
                });
            } else if (event.type === 'transportation') {
                newEvent = Ext.create('Ext.draw.Sprite', {
                    type: 'rect',
                    fill: '#cff',
                    width: duration,
                    height: 120,
                    x: position,
                    y: 10
                });
            }

            ui_events.push(newEvent);

            var label = Ext.create('Ext.draw.Sprite', {
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
            });
            ui_events.push(label);

            return true;
        });

        return ui_events;
    },


    calculateTimelinePosition: function(time) {
        return Math.round((time - this.timelineStart) * this.pixelsPerSecond);
    },


    makeAxisSteps: function() {
        var me = this;
        var axisItems = [];

        this.eventStore.each(function(record) {
            var event = record.data;
            var pos1 = me.calculateTimelinePosition(event.time);
            var pos2 = me.calculateTimelinePosition(event.time + event.duration);
            axisItems.push(Ext.create('Ext.draw.Sprite', {
                type: 'path',
                path: ['M', pos1, 200, 'l', 0, 10],
                stroke: '#000000'
            }));
            axisItems.push(Ext.create('Ext.draw.Sprite', {
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
            }));
            axisItems.push(Ext.create('Ext.draw.Sprite', {
                type: 'path',
                path: ['M', pos2, 200, 'l', 0, 10],
                stroke: '#000000'
            }));
            return true;
        });

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
    },


    tzRe: new RegExp('^([+-]?\\d{1,2})(?:\\.(\\d{2}))?$'),


    parseTimezone: function(timezoneString) {
        var m = this.tzRe.exec(timezoneString);
        if (Ext.isArray(m)) {
            return {
                hours: Number(m[1]),
                minutes: Ext.isString(m[2]) ? Number(m[2]) : 0
            };
        }
    },


    makeColor: function(timezone) {
        var gmt = 12;
        var utc = gmt + timezone.hours;
        var h = (utc * 15) / 60;
        var s = 0.4;
        var v = 0.4;
        var i = Math.floor(h);
        var f = h - i;
        var p = v * (1 - s);
        var q = v * (1 - (s * f));
        var t = v * (1 - (s * (1 - f)));
        var r = 0;
        var g = 0;
        var b = 0;
        switch (i) {
            case 0: r = v; g = t; b = p; break;
            case 1: r = q; g = v; b = p; break;
            case 2: r = p; g = v; b = t; break;
            case 3: r = p; g = q; b = v; break;
            case 4: r = t; g = p; b = v; break;
            case 5: r = v; g = p; b = q; break;
        }
        return [
            'rgb(',
                Math.floor(256 * r), ', ',
                Math.floor(256 * g), ', ',
                Math.floor(256 * b),
            ')'
        ].join('');
    },


    makeGradient: function(timezone1, timezone2) {
        return {
            id: 'timezone_' + timezone1.hours + '_' + timezone2.hours,
            angle: 0,
            stops: {
                0: {
                    color: this.makeColor(timezone1)
                },
                100: {
                    color: this.makeColor(timezone2)
                }
            }
        };
    },


    makeGradients: function() {
        var gradients = [];
        for (var i = -11; i <= 12; i++) {
            for (var j = -11; j <= 12; j++) {
                if (i !== j) {
                    gradients.push(this.makeGradient({ hours: i }, { hours: j }));
                    gradients.push(this.makeGradient({ hours: j }, { hours: i }));
                }
            }
        }
        return gradients;
    }

});


/*****************************************************************
 *                                                               *
 * Some tests with Hue-Saturation Value to RGB.                  *
 *                                                               *
 *****************************************************************/

// http://en.wikipedia.org/wiki/HSL_and_HSV#Converting_to_RGB
// http://www5.informatik.tu-muenchen.de/lehre/vorlesungen/graphik/info/csc/COL_25.htm
function hsv_rgb(h, s, v) {
    h = h / 60;
    var i = Math.floor(h);
    var f = h - i;
    var p = v * (1 - s);
    var q = v * (1 - (s * f));
    var t = v * (1 - (s * (1 - f)));
    switch (i) {
        case 0: r = v; g = t; b = p; break;
        case 1: r = q; g = v; b = p; break;
        case 2: r = p; g = v; b = t; break;
        case 3: r = p; g = q; b = v; break;
        case 4: r = t; g = p; b = v; break;
        case 5: r = v; g = p; b = q; break;
    }

    return ['rgb(', Math.floor(256 * r), ', ', Math.floor(256 * g), ', ', Math.floor(256 * b), ')'].join('');
}


function make_24_colors(s, v) {
    var colors = [];
    for (var h = 0; h < 24; h++) {
        colors.push(hsv_rgb(h * 15, s, v));
    }
    return colors;
}


function make_24_color_divs(s, v) {
    var colors = make_24_colors(s/10, v/10);
    var content = '';
    Ext.Array.forEach(
        colors,
        function(color) {
            content += [
                '<div ',
                    'style="',
                        'background-color: ', color, '; ',
                        'height: 9px; ',
                        'width: 4px; ',
                        'float: left;',
                    '"',
                '>',
                '</div>'
            ].join('');
        }
    );
    return content;
}


function draw_gradient_test() {
    var div_gradient_tests = Ext.getDom('gradient-tests');    
    var content = '';
    content += '<div style="float: left; width: 80px;">Saturation:</div>';
    for (var s_head = 10; s_head >= 0; s_head--) {
        content += '<div style="float: left; width: 96px;">' + s_head/10 + '</div>';
    }
    content += '</div><div style="clear: both;"></div>';

    for (var v = 10; v >= 0; v--) {
        content += '<div style="float: left; width: 40px;">Value ' + v/10 + '</div>';
        content += '<div style="float: left;">';
        for (var s = 10; s >= 0; s--) {
            content += make_24_color_divs(s, v);
        }
        content += '</div><div style="clear: both;"></div>';
    }
    div_gradient_tests.innerHTML += content;
}


/**********************************************************************/

function init_timeline() {

    var drawComponent = Ext.create('JD.Timeline', {
        width: initialTimelineWidth,
        height: initialTimelineHeight,
        renderTo: 'timeline',
        tripEvents: events
    });


    var newEvent = {
        id:            '1004',
        name:          'Taxi to Airport',
        type:          'transportation',
        time:          1312519800 - 2 * 60 * 60,
        duration:      3600,
        from_utc:      '+3',
        from_location: 'Jeddah',
        from_iata:     '',
        to_utc:        '',
        to_location:   '',
        to_iata:       '',
        description:   'Hurry to airport...'
    };

    Ext.create('Ext.Button', {
        text: 'Add Event',
        renderTo: 'add_event_button_container',
        handler: function() {
            this.disable();
            drawComponent.eventStore.add(newEvent);
            drawComponent.eventStore.sort('time', 'ASC');
            drawComponent.surface.removeAll(true);
            drawComponent.rebuildTimeline();
        }
    });

    Ext.create('Ext.Button', {
        text: 'Run Gradient Test',
        renderTo: 'run_gradient_test_button_container',
        handler: function() {
            this.disable();
            draw_gradient_test();
            Ext.getDom('gradient-test-outer').style.display = '';
        }
    });

    Ext.create('Ext.Button', {
        text: 'Test',
        renderTo: 'test_button_container',
        handler: function() {
            console.log(drawComponent.parseTimezone('+3'));
            console.log(drawComponent.parseTimezone('+5.45'));
            console.log(drawComponent.parseTimezone('-8'));
            console.log(drawComponent.parseTimezone('11'));
            var plus3 = drawComponent.parseTimezone('+3');
            var minus8 = drawComponent.parseTimezone('-8');
            var p3 = drawComponent.makeColor(plus3);
            var m3 = drawComponent.makeColor(minus8);
            console.log(p3);
            console.log(m3);
        }
    });
}


Ext.onReady(function() {
    init_timeline();
});


</script>
</head>
<body style="background-color: #ccf;">

<h2>Timeline</h2>
<br>
<p>
    This simple (<span style="color: red;">and resizable</span>) timeline
    is implemented with ExtJS's Ext.draw package
    which uses SVG (or VML for IE) under the hood. Click
    <a href="http://docs.sencha.com/ext-js/4-0/#/guide/drawing_and_charting">here</a>
    for a technical introduction.
    It also uses an Ext.data.Store to store trip events.
</p>
<br>
<div id="timeline"></div>

<br>

<div id="add_event_button_container" style="float: left;"></div>
<div id="run_gradient_test_button_container" style="float: left;"></div>
<div id="test_button_container" style="float: left;"></div>
<div style="clear: both;"></div>
<br>

<div id="gradient-test-outer" style="display: none;">
<br>
<h3>Gradient Test</h3>
<br>
<div id="gradient-tests" style="font-size: 6px; border: 0px;">
</div>
<br>
<br>
</div>

<h3>TODO</h3>
<ul>
    <li>Drag-and-drop (or at least add) new events into the timeline.</li>
    <li>On click on the events, pop up a small window with more info about the event.</li>
    <li>Indicate timezones better. Now it is done with changing backgrounds.</li>
    <li>Use an HSI circular gradient for timezone backgrounds.</li>
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

