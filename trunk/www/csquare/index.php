<?php

require_once(dirname(dirname(__FILE__)) . '/config.inc.php');
require_once(dirname(__FILE__) . '/csq.php');

$csquare = '';
$format = 'html';

if (isset($_GET['csquare']))
{
        $csquare = trim($_GET['csquare']);
}
if (isset($_GET['format']))
{
        switch($format)
        {
                case 'html':
				case 'xml':
                case 'rdf':
                        $format = $_GET['format'];
                        break;
                        
                default:
                        $format = 'html';
                        break;
        }
}

if ($csquare == '')
{
        header("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>c-squares</title>
        <meta name="generator" content="BBEdit 9.0" />
        
        <script type="application/javascript" language="javascript">
        function validateFormOnSubmit(theForm) 
        {
                if (theForm.csquare.value == '')
                {
                        alert("Please enter a c-square");
                        return false;
                }
                return true;
        }
        </script>
            <style type="text/css">
        body 
        {
                font-family: Verdana, Arial, sans-serif;
                font-size: 12px;
                padding:30px;
        
        }
        
.blueRect {
        background-color: rgb(239, 239, 239);
        border:1px solid rgb(239, 239, 239);
        background-repeat: repeat-x;
        color: #000;
        width: 400px;
}
.blueRect .bottom {
        height: 10px;
}
.blueRect .middle {
        margin: 10px 12px 0px 12px;
}
.blueRect .cn {
        background-image: url(../images/c6.png);
        background-repeat: no-repeat;
        height: 10px;
        line-height: 10px;
        position: relative;
        width: 10px;
}
.blueRect .tl {
        background-position: top left;
        float: left;
        margin: -2px 0px 0px -2px;
}
.blueRect .tr {
        background-position: top right;
        float: right;
        margin: -2px -2px 0px 0px;
}
.blueRect .bl {
        background-position: bottom left;
        float: left;
        margin: 2px 0px -2px -2px;
}
.blueRect .br {
        background-position: bottom right;
        float: right;
        margin: 2px -2px -2px 0px;
}               
    
        #details
        {
                display: none;
                position:absolute;
                background-color:white;
                border: 1px solid rgb(128,128,128);
        }
    </style>    
</head>
<body>

<p><a href="/">Home</a></p>

  <h1>c-square URI</h1>
<p>A <a href="http://www.marine.csiro.au/csquares/">c-square</a> as a URI. Supports only 10°×10° and 1°×1° c-squares.</p>

<div class="blueRect" style="width:100%">
        <div class="top">
                <div class="cn tl"></div>
                <div class="cn tr"></div>
        </div>
        <div class="middle">


<form action="index.php" method="get" onsubmit="return validateFormOnSubmit(this)">
<label>c-square:</label><br/>
 <input id="csquare" name="csquare" size="30" value="3317:364"/>
 <select name="format">
        <option value="html" "selected">HTML</option>
        <option value="xml">XML</option> 
        <option value="rdf">RDF/XML</option>
</select>
<input type="submit" value="Go">
</form>


        </div>
        <div class="bottom">
                <div class="cn bl"></div>
                <div class="cn br"></div>
        </div>
</div>

</body>
</html>
<?php
}
else
{
        $state = 404; // not found
        
        $box = new stdclass;
        $resolution = 10;
        if (preg_match('/^(?<ten>[1357][0-9]{3})(:(?<one>[0-9]{3}))?$/', $csquare, $matches))
        {
        	if (isset($matches['one']))
        	{
        		$resolution = 1;
        	}
			unpack_csquare($csquare, $box, $resolution);   
		
			$state = 200;        	
        }
        
        switch ($state)
        {
                        
                case 400:
                        ob_start();
                        header('HTTP/1.0 400');
                        header('Status: 400');
                        $_SERVER['REDIRECT_STATUS'] = 400;
                        break;
                        
                case 200:
                        switch ($format)
                        {
                                case 'rdf':
                                case 'xml':
                                      $feed = new DomDocument('1.0');
                                        $rdf = $feed->createElement('rdf:RDF');
                                        $rdf->setAttribute('xmlns:rdf',         'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
                                        $rdf->setAttribute('xmlns:rdfs',        'http://www.w3.org/2000/01/rdf-schema#');
                                        $rdf->setAttribute('xmlns:dcterms', 	'http://purl.org/dc/terms/');
                                        $rdf->setAttribute('xmlns:dwc',   		'http://rs.tdwg.org/dwc/terms/');
                                        $rdf->setAttribute('xmlns:geom',        'http://fabl.net/vocabularies/geometry/1.1/');
 
                                        $rdf = $feed->appendChild($rdf);
                                        
                                        $location = $rdf->appendChild($feed->createElement('dcterms:Location'));
                                        $location->setAttribute('rdf:about', 'http://bioguid.info/csquare:' . $csquare);
                                        				
 
                                        // Label
                                        $rdfs_label = $location->appendChild($feed->createElement('rdfs:label'));
                                        $rdfs_label->appendChild($feed->createTextNode($csquare));
                                        
                                        if (0)
                                        {
                                        	// nested, which needs b-node
											
											// Box (note attribute parseType=Resource)
											$geom_box = $location->appendChild($feed->createElement('geom:Box'));
											$geom_box->setAttribute('rdf:parseType', 'Resource');
																					
											$geom_xmin = $geom_box->appendChild($feed->createElement('geom:xmin'));
											$geom_xmin->appendChild($feed->createTextNode($box->MINX));
											
											$geom_ymin = $geom_box->appendChild($feed->createElement('geom:ymin'));
											$geom_ymin->appendChild($feed->createTextNode($box->MINY));
											
											$geom_xmax = $geom_box->appendChild($feed->createElement('geom:xmax'));
											$geom_xmax->appendChild($feed->createTextNode($box->MAXX));
	 
											$geom_ymax = $geom_box->appendChild($feed->createElement('geom:ymax'));
											$geom_ymax->appendChild($feed->createTextNode($box->MAXY));
                                        }
                                        else
                                        {
                                        	// not nested
											$geom_xmin = $location->appendChild($feed->createElement('geom:xmin'));
											$geom_xmin->appendChild($feed->createTextNode($box->MINX));
											
											$geom_ymin = $location->appendChild($feed->createElement('geom:ymin'));
											$geom_ymin->appendChild($feed->createTextNode($box->MINY));
											
											$geom_xmax = $location->appendChild($feed->createElement('geom:xmax'));
											$geom_xmax->appendChild($feed->createTextNode($box->MAXX));
	 
											$geom_ymax = $location->appendChild($feed->createElement('geom:ymax'));
											$geom_ymax->appendChild($feed->createTextNode($box->MAXY));
                                        	
                                        }
                                        
                                        
                                        // WKT
                                        $dwc_footprintWKT = $location->appendChild($feed->createElement('dwc:footprintWKT'));
                                        $dwc_footprintWKT->appendChild($feed->createTextNode($box->wkt));
                                        
                                        
                                         $h = 'Content-type: application/';
                                        if ($format == 'rdf')
                                        {
                                                $h .= 'rdf+';
                                        }
                                        $h .= "xml; charset=utf-8\n\n";
                                        
                                        header($h);     
                                        $feed->encoding='utf-8';
                                        echo $feed->saveXML();                                
                                	break;
                               	case 'html':
                                       header("Content-type: text/html; charset=utf-8\n\n");   
                                        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' 
                                                . "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
                                        echo '<head>';
										echo '<title>' . $csquare . '</title>';   
										echo '<style type="text/css">
                                        body 
                                        {
                                                font-family: Verdana, Arial, sans-serif;
                                                font-size: 12px;
                                                padding:30px;
                                        
                                        }
                                        </style>';										
										echo '   <script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $config['gmap'] . '"></script>
    <script type="text/javascript">
function initialize() 
{
  if (GBrowserIsCompatible()) 
  {
		var map = new GMap2(document.getElementById("map_canvas"));
		map.setCenter(new GLatLng(0, -0), 1);
		map.addControl(new GSmallMapControl());
		map.addControl(new GOverviewMapControl());
		map.addControl(new GMapTypeControl());        
		map.addMapType(G_PHYSICAL_MAP);
		map.setMapType(G_PHYSICAL_MAP);

		// Bounding box to contain points, from http://www.svennerberg.com/2008/11/bounding-box-in-google-maps/
		// see especially comment by Aiska http://www.svennerberg.com/2008/11/bounding-box-in-google-maps/#comment-1546
		var bounds = new GLatLngBounds();


		<!--Polygon corresponding to location-->
		var yX = new GLatLng('. $box->MINY .',' . $box->MAXX .');
		var yx = new GLatLng('. $box->MINY .',' . $box->MINX .');
		var Yx = new GLatLng('. $box->MAXY .',' . $box->MINX .');
		var YX = new GLatLng('. $box->MAXY .',' . $box->MAXX .');
		
		<!--Add polgon to map-->
		var polygon = new GPolygon([yX, yx, Yx, YX, yX], "#000000", 2);
		map.addOverlay(polygon);
		
		<!--Update bounds of Google Map-->
		bounds.extend(yX);
		bounds.extend(yx);
		bounds.extend(Yx);
		bounds.extend(YX);

	}		
	// Center map in the center of the bounding box
	// and calculate the appropriate zoom level 
	map.setCenter(bounds.getCenter(), map.getBoundsZoomLevel(bounds));
}
</script>';
                               	  		echo '</head>';
										echo '<body onload="initialize()" onunload="GUnload()">';
										echo '<h1>c-square ' . $csquare . '</h1>';
										echo '<div id="map_canvas" style="width: 500px; height: 300px; border:1px solid black;"></div>';
										echo '<h2>Bounding box</h2>';
										echo '<p>';
										echo 'Longitude ' . $box->MINX . '°';
										if ($box->MINX < 0) { echo 'W'; } else { echo 'E'; }
										echo '<br/>'; 
										echo 'Longitude ' . $box->MAXX . '°'; 
										if ($box->MAXX < 0) { echo 'W'; } else { echo 'E'; }
										echo '<br/>'; 
										echo 'Latitude ' . $box->MINY . '°'; 
										if ($box->MINY < 0) { echo 'S'; } else { echo 'N'; }
										echo '<br/>'; 
										echo 'Latitude ' . $box->MAXY . '°'; 
										if ($box->MAXY < 0) { echo 'S'; } else { echo 'N'; }
										echo '<br/>'; 
										echo '</p>';
										echo '<h2>Well-known text</h2>';
										echo $box->wkt;
                                        echo '</body>';
                                        echo '</html>';
                               		break;
                               	
                                default:
                                	
                                	break;
                        }
                        
         }
         
         
}
?>
                              
                                
                           