<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * GoogleMap iframe wrapper
 * 
 * @package block_dashboard
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version Moodle 1.9
 */
require('../../config.php');

$courseid = required_param('id', PARAM_INT);

<<<<<<< HEAD
if ($courseid != SITEID){
	if (!$course = $DB->get_record('course', array('id' => "$courseid"))){
		print_error('invalidcourseid');
	}
	
	require_login($course);
=======
// Security.
if ($courseid != SITEID) {
    if (!$course = $DB->get_record('course', array('id' => "$courseid"))) {
        print_error('invalidcourseid');
    }
    require_login($course);
>>>>>>> MOODLE_33_STABLE
}

$options = stripslashes(urldecode(required_param('options', PARAM_TEXT)));
$lat = optional_param('lat', 0, PARAM_TEXT);
$lng = optional_param('lng', 0, PARAM_TEXT);
$mapid = 'map_'.required_param('mapid', PARAM_TEXT);
$markers = optional_param('markers', PARAM_TEXT); // Array of markers

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
  html { height: 100% }
  body { height: 100%; margin: 0; padding: 0 }
  #map_canvas { height: 100% }
</style>
<script type="text/javascript"
    src="http://maps.googleapis.com/maps/api/js?sensor=false">
</script>
<script type="text/javascript">

    function initialize() {
        var latlng = new google.maps.LatLng(<?php echo $lat ?>, <?php echo $lng ?>);
        var myOptions = <?php echo $options ?>;
        var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
        map.setOptions({styles: administrative});
        for (i = 0; i < mks ; i++){
            marker[i].setMap(map);
        }
    }

<?php
    $markerimages = glob ($CFG->dataroot.'/'.$course->id.'/blockdata/dashboard/all/mk_*.png');
    $hasshadow = array();
    if (!empty($markerimages)) {
        foreach ($markerimages as $im) {
            $imname = basename($im, '.png');
            $shadowimname = str_replace('mk_', 'sh_', $imname);
            $classname = str_replace('mk_', '', $imname);
            $sizeinfo = getimagesize($im);
            $imfullpath = $CFG->wwwroot."/file.php/{$course->id}/blockdata/dashboard/all/{$imname}.png";
            echo "var image{$classname} = new google.maps.MarkerImage(\"$imfullpath\", new google.maps.Size({$sizeinfo[0]}, {$sizeinfo[1]}), new google.maps.Point(0, 0),new google.maps.Point(10, {$sizeinfo[1]}));\n";

            $shadowimagepath = $CFG->dataroot."/{$course->id}/blockdata/dashboard/all/{$shadowimname}.png";
            if (file_exists($shadowimagepath)) {
                $sizeinfo = getimagesize($shadowimagepath);
                // Todo reform to new pluginfile.php file wrapper
                $shadowfullpath = $CFG->wwwroot."/file.php/{$course->id}/blockdata/dashboard/all/{$shadowimname}.png";
                echo "var shadow{$classname} = new google.maps.MarkerImage(\"$shadowfullpath\", new google.maps.Size({$sizeinfo[0]}, {$sizeinfo[1]}), new google.maps.Point(0, 0),new google.maps.Point(10, {$sizeinfo[1]}));\n";
                $hasshadow[$classname] = true;
            } else {
                $hasshadow[$classname] = false;
            }
        }
    }
?>
/*
    var imagecertif = new google.maps.MarkerImage("pix/of1.png", new google.maps.Size(40, 39), new google.maps.Point(0, 0),new google.maps.Point(10, 39));
    var shadowcertif = new google.maps.MarkerImage("pix/of1_sh.png", new google.maps.Size(40, 39), new google.maps.Point(0, 0),new google.maps.Point(10, 39));
    var imagefc = new google.maps.MarkerImage("pix/of2.png", new google.maps.Size(40, 39), new google.maps.Point(0, 0),new google.maps.Point(10, 39));
    var shadowfc = new google.maps.MarkerImage("pix/of2_sh.png", new google.maps.Size(40, 39), new google.maps.Point(0, 0),new google.maps.Point(10, 39));
    var imagecertiffoad = new google.maps.MarkerImage("pix/of1_foad.png", new google.maps.Size(40, 39), new google.maps.Point(0, 0),new google.maps.Point(10, 39));
    var shadowcertiffoad = new google.maps.MarkerImage("pix/of1_foad_sh.png", new google.maps.Size(40, 39), new google.maps.Point(0, 0),new google.maps.Point(10, 39));
    var imagefcfoad = new google.maps.MarkerImage("pix/of2_foad.png", new google.maps.Size(40, 39), new google.maps.Point(0, 0),new google.maps.Point(10, 39));
    var shadowfcfoad = new google.maps.MarkerImage("pix/of2_foad_sh.png", new google.maps.Size(40, 39), new google.maps.Point(0, 0),new google.maps.Point(10, 39));
    var imagehq = new google.maps.MarkerImage("pix/hq.png", new google.maps.Size(13, 27), new google.maps.Point(0, 0),new google.maps.Point(5, 27));
    var shadowhq = new google.maps.MarkerImage("pix/hq_sh.png", new google.maps.Size(40, 39), new google.maps.Point(0, 0),new google.maps.Point(5, 27));
*/
    var latlngmarks = new Array();
    var marker = new Array();
    var mks = 0;

<?php
if (!empty($markers)) {
    foreach ($markers as $amarker) {
        $amarkerobj = json_decode(stripslashes(urldecode($amarker)));
?>
    // 48.020587,0.151405
    latlngmarks[mks] = new google.maps.LatLng(<?php echo $amarkerobj->lat ?>, <?php echo $amarkerobj->lng ?>);
    marker[mks] = new google.maps.Marker({
           position: latlngmarks[mks], 
           title:"<?php echo $amarkerobj->title ?>",
           <?php 
           if (!empty($amarkerobj->markerclass)) {
               if (@$hasshadow[$amarkerobj->markerclass]) {
           ?>
           shadow:shadow<?php echo $amarkerobj->markerclass ?>,
           <?php
            }
        ?>
           icon:image<?php echo $amarkerobj->markerclass ?>
        <?php
        }
        ?>
    }); 
    mks++;
<?php
    }
}
?>

    var administrative = [
    {
    featureType: "all",
    stylers: [
      { saturation: -40 }
    ]
  },
  {
    featureType: "administrative",
    stylers: [
      { hue: "#1300FF" },
      { saturation: 80 }
    ]
  },
  {
    featureType: "road.highway",
    stylers: [
      { gamma: "4.0" }
    ]
  },
  {
    featureType: "road.local",
    stylers: [
      { visibility: "off" }
    ]
  },
  {
    featureType: "road.arterial",
    stylers: [
      { gamma: "5.0" }
    ]
  },
  {
    featureType: "poi",
    stylers: [
      { visibility: "off" }
    ]
  },
  {
    featureType: "transit",
    stylers: [
      { visibility: "off" }
    ]
  },
  {
    featureType: "landscape.natural",
    stylers: [
      { visibility: "off" }
    ]
  },
  {
    featureType: "landscape.man_made",
    stylers: [
      { hue: "#FF4000" },
      { saturation: 80 }
    ]
  },
  {
    featureType: "administrative.country",
    stylers: [
      { hue: "#1300FF" },
      { saturation: 80 }
    ]
  },
  {
    featureType: "administrative.province",
    stylers: [
      { hue: "#00B9FF" },
      { saturation: 70 }
    ]
  }
];
</script>
</head>
<body onload="initialize()">
  <div id="map_canvas" style="width:100%; height:100%"></div>
</body>
</html>