<?php

/**
 * @file geocoding.php
 *
 * Functions to extract geographical coordinates from text, and to convert and format
 * coordinates.
 *
 */
 

require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/bhl_text.php');

//------------------------------------------------------------------------------
/**
 * @brief Convert a decimal latitude or longitude to deg° min' sec'' format in HTML
 *
 * @param decimal Latitude or longitude as a decimal number
 *
 * @return Degree format
 */
function decimal_to_degrees($decimal)
{
	$decimal = abs($decimal);
	$degrees = floor($decimal);
	$minutes = floor(60 * ($decimal - $degrees));
	$seconds = round(60 * (60 * ($decimal - $degrees) - $minutes));
	
	if ($seconds == 60)
	{
		$minutes++;
		$seconds = 0;
	}
	
	// &#176;
	$result = $degrees . '&deg;' . $minutes . '&rsquo;';
	if ($seconds != 0)
	{
		$result .= $seconds . '&rdquo;';
	}
	return $result;
}

//------------------------------------------------------------------------------
/**
 * @brief Convert decimal latitude, longitude pair to deg° min' sec'' format in HTML
 *
 * @param latitude Latitude as a decimal number
 * @param longitude Longitude as a decimal number
 *
 * @return Degree format
 */
function format_decimal_latlon($latitude, $longitude)
{
	$html = decimal_to_degrees($latitude);
	$html .= ($latitude < 0.0 ? 'S' : 'N');
	$html .= '&nbsp;';
	$html .= decimal_to_degrees($longitude);
	$html .= ($longitude < 0.0 ? 'W' : 'E');
	return $html;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Convert degrees, minutes, seconds to a decimal value
 *
 * @param degrees Degrees
 * @param minutes Minutes
 * @param seconds Seconds
 * @param hemisphere Hemisphere (optional)
 *
 * @result Decimal coordinates
 */
function degrees2decimal($degrees, $minutes=0, $seconds=0, $hemisphere='N')
{
	$result = $degrees;
	$result += $minutes/60.0;
	$result += $seconds/3600.0;
	
	if ($hemisphere == 'S')
	{
		$result *= -1.0;
	}
	if ($hemisphere == 'W')
	{
		$result *= -1.0;
	}
	return $result;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief
 *
 * @param text Text which may contain latitude and longitudes
 *
 * @return Array of points (as class with fields latitude and longitude)
 */
function points_from_text($text)
{
	
	$text = str_replace("\n", " ", $text);
	$text = str_replace("\\n", " ", $text);
	
	$points = array();
	 
	//echo $text;
	
	$matched = false;
	
	if (!$matched)
	{
		// lat 13.869°N, long 89.620°W
		if (preg_match_all('/(
			lat
			\s*
			(?<latitude_degrees>([0-9]{1,2}))
			(?<latitude_minutes>(\.[0-9]+))
			°
			(?<latitude_hemisphere>[N|S])
			,
			\s*
			long
			\s*
			(?<longitude_degrees>([0-9]{1,2}))
			(?<longitude_minutes>(\.[0-9]+))
			°
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$degrees = $matches['latitude_degrees'][$i] + $matches['latitude_minutes'][$i];
				$pt->latitude = $degrees;
				
				if ($matches['latitude_hemisphere'][$i] == 'S')
				{
					$pt->latitude *= -1;
				}
				
				$degrees = $matches['longitude_degrees'][$i] + $matches['longitude_minutes'][$i];
				$pt->longitude = $degrees;
				
				if ($matches['longitude_hemisphere'][$i] == 'W')
				{
					$pt->longitude *= -1;
				}
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	if (!$matched)
	{		
		// http://www.biodiversitylibrary.org/page/40824927
		// 48 30' 33" E, 13 00' 01" S (long first then lat)
		if (preg_match_all('/(
			(?<longitude_degrees>([0-9]{1,2}))
			\s+
			(?<longitude_minutes>([0-9]+))
			\'
			\s+
			(?<longitude_seconds>\d+)
			"
			\s+
			(?<longitude_hemisphere>[W|E])
			,
			\s+
			(?<latitude_degrees>([0-9]{1,2}))
			\s+
			(?<latitude_minutes>([0-9]+))
			\'
			\s+
			(?<latitude_seconds>\d+)
			"
			\s+
			(?<latitude_hemisphere>[N|S])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	
	if (!$matched)
	{		
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))([°])
			\s*
			((?<latitude_minutes>([0-9]+)(\.[0-9]+)?))?
			[\']?
			\s*
			(?<latitude_hemisphere>[N|S])
			[,]
			\s*
			((?<longitude_degrees>([0-9]{1,3}))([°])?)?
			\s*
			(?<longitude_minutes>([0-9]+)(\.[0-9]+)?)
			[\']?
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
		
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['latitude_degrees'][$i];
				if (isset($matches['latitude_minutes'][$i]))
				{
					$minutes = $matches['latitude_minutes'][$i];
				}
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['longitude_degrees'][$i];
				if (isset($matches['longitude_minutes'][$i]))
				{
					$minutes = $matches['longitude_minutes'][$i];
				}
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}
	}
	
	
	// http://www.biodiversitylibrary.org/page/3387645
	
	if (!$matched)
	{		
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))\.(?<latitude_minutes>([0-9]+))
			(?<latitude_hemisphere>[N|S])
			[,]\s*
			(?<longitude_degrees>([0-9]{1,3}))\.(?<longitude_minutes>([0-9]+))
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['latitude_degrees'][$i];
				if (isset($matches['latitude_minutes'][$i]))
				{
					$minutes = $matches['latitude_minutes'][$i];
				}
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['longitude_degrees'][$i];
				if (isset($matches['longitude_minutes'][$i]))
				{
					$minutes = $matches['longitude_minutes'][$i];
				}
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}		
	}
	
	
	if (!$matched)
	{		
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))([°])
			(?<latitude_hemisphere>[N|S])
			[,]
			\s*
			((?<longitude_degrees>([0-9]{1,3}))([°])?)?
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			//print_r($matches);
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	if (!$matched)
	{		
	
		// 54° 20' 80" N.; y9°09'30"E
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))[°|�]
			\s*
			(?<latitude_minutes>([0-9]+))\'
			\s*
			((?<latitude_seconds>([0-9]+))")?
			\s*
			(?<latitude_hemisphere>[N|S])\.?
			[,|;]\s*
			(?<longitude_degrees>([0-9]{1,3}))[°|�]
			\s*
			(?<longitude_minutes>([0-9]+))\'
			\s*
			((?<longitude_seconds>([0-9]+))")?
			\s*
			(?<longitude_hemisphere>[W|E])\.?
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	if (!$matched)
	{		
		// 38 18' 30" N, 123 4' W
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			\s+
			(?<latitude_minutes>([0-9]+))\'
			\s+
			((?<latitude_seconds>([0-9]+))")?
			\s*
			(?<latitude_hemisphere>[N|S])
			[,|;]\s*
			(?<longitude_degrees>([0-9]{1,3}))
			\s+
			(?<longitude_minutes>([0-9]+))\'
			\s+
			((?<longitude_seconds>([0-9]+))")?
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	if (!$matched)
	{		
		// 0224' N 4259' E
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{2}))
			(?<latitude_minutes>([0-9]{2}))\'
			\s*
			(?<latitude_hemisphere>[N|S])
			\s+
			(?<longitude_degrees>([0-9]{2}))
			(?<longitude_minutes>([0-9]{2}))\'
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = '';
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = '';
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}
	}
	
	if (!$matched)
	{		
		
		//http://biostor.org/reference/14507
		// No hemisphere, but for this example it's N and E
		// 36°58', 127 °57'
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			[°]
			\s?
			(?<latitude_minutes>([0-9]+))\'
			[,]\s
			(?<longitude_degrees>([0-9]{1,3}))
			[°]
			\s?
			(?<longitude_minutes>([0-9]+))\'
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, 'N');
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, 'E');
				
				$points[] = $pt;
			}
			$matched = true;
		}		
	}
	
	
	if (!$matched)
	{		
		
		// http://biostor.org/reference/4047
		// 34°49'N 24°32'W
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			[°]
			(?<latitude_minutes>([0-9]+))\'
			(?<latitude_hemisphere>[N|S])
			\s+
			(?<longitude_degrees>([0-9]{1,3}))
			[°]
			(?<longitude_minutes>([0-9]+))\'
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
	
				$seconds = '';
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = '';
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}
	}	
	
	if (!$matched)
	{			
		// http://biostor.org/reference/55225
		// N10°23'31"; W 83°58'04"
		if (preg_match_all('/(
			(?<latitude_hemisphere>[N|S])
			\s*
			(?<latitude_degrees>([0-9]{1,2}))[°]
			(?<latitude_minutes>([0-9]+))\'
			(?<latitude_seconds>([0-9]+))"
			\s*
			;
			\s+
			(?<longitude_hemisphere>[W|E])
			\s*
			(?<longitude_degrees>([0-9]{1,2}))[°]
			(?<longitude_minutes>([0-9]+))\'
			(?<longitude_seconds>([0-9]+))"
		)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	
	
	return $points;
}


if (0)
{
	$text = "YAQUINA).-2 males, ML 74-79 mm, Cr. Y7102B haul 262, 
	 45°38.3'N, 126°43.8'W in 2,721 m, 17 Feb. 1971, USNM 
	 817580.-1 female, ML 66 mm, Cr. Y7105B haul 276, 
	 45°56.7'N, 127°38.6'W in 2,761 m, 17 May 1971, SBMNH 
	 35 142. - 1 female, ML 67 mm, Cr. Y7 1 023 haul 263, 45°36.4'N, 
	 ";
	 
	 $text = "Material Examined (15 specimens all collected by R/V 
	 YAQUINA).-Holotype: male, ML 50 mm, Cr. Y7210A haul 
	 308,45°01.rN, 135°12.0'W in 3,932 m,USNM 7307 15. Para- 
	 types: 2 males ML 46.5-48 mm, 1 female ML 31 mm, Cr. 
	 Y7210A haul 300, 44°58.rN,132\"'14.7'W in 3,585 m, 10 June 
	 1972, CAS 067789.-1 male, ML 42 mm, Cr. Y7105B haul 
	 281,44°38.5'N, 127°39.5'W in 2,816 m, 19May 1971,SBMNH 
	 35 144.- 1 male ML 30 mm, 1 female ML 85 mm, Cr. Y72 lOA 
	 haul 303, 45°05.rN, 133°10.9'W in 3,700 m, 10 July 1972, 
	 UMML 31.1938.-1 female, ML 57 mm, Cr. Y7005C haul 
	 232, 44°40.2'N, 1 33°35.7'W in 3,742 m, 3 June 1970, SBMNH 
	 35145.-2 males, ML 17.5-53 mm, Cr. Y7210A haul 299, 
	 44''56.8'N, 132°11.5'W in 3,580 m, 10 June 1972, UMML 
	 31.1937.-1 male, ML 17 mm, Cr. Y7210A haul 307, 
	 45'^3.5'N, 134°45.0'W in 3,900 m, 10 Oct. 1972, CAS 
	 067790.-2 males ML 30-36 mm, 1 female ML 29 mm, Cr. 
	 Y7210A haul 305, 45°05.2'N, 134°43.4'W in 3,900 m, 9 Oct. 
	 1972, USNM 817582.-1 male, ML 28 mm, Cr. Y7206Bhaul 
	 288, 44°06.2'N, 125°22.7'W in 2,940 m. 14 June 1972, CAS 
	 067791. ";
	 
	 $text = "Material Examined (5 specimens). — Holotype: male, ML 
	 93 mm, R/V YAQUINA Cr. 6606, 44°37.0'N, 125°01.0'W in 
	 1,260 m, 6 Aug. 1966, USNM 729991. Paratypes: 1 female, 
	 ML 99 mm, R/V ACONA, 44°24.2'N, 125°10.3'W in 1,000 
	 m, 14 Aug. 1964, CAS 06 1430.-1 female, ML 115 mm, R/V 
	 ACONA. 44°3I.3'N, 125°05.4'W in 1,250 m, 15 Jan. 1965. 
	 Other material (2 specimens in very poor condition, one partly 
	 eaten and both mauled in the net); 1 male, ML 62 mm, R/V 
	 ACONA, 44°36'N, 126°06.9'W in 3,000 m, 30 Dec. 1963, 
	 UMML 31.1 943. - 1 male, ML 56 mm, R/V ACONA, 44°36'N, 
	 126-06. 9' W in 3,000 m, 30 Dec. 1963. ";
	 
	 //$text = "2 UNIV. KANSAS MUS. NAT. HIST. OCC. PAP. No. 150 \nvent length is abbreviated SVL. The Museum of Natural History, The \n University of Kansas is abbreviated KU. \nI take pleasure in naming this distinctive new species for Professor Robert \n C. Bearse, Associate Vice Chancellor for Research, Graduate Studies, and \n Public Service, The University of Kansas; his enlightened and imaginative \n administrative actions have continuously enhanced the programs of the \n Museum of Natural History. \nEleutherodactylus bearsei new species \nHolotype. — KU 2 12268, a gravid female, from the CataratasAhuashiyacu \n (06°30'S, 76°20'W, 730 m), 14 km (by road) northeast of Tarapoto, Provincia \n San Martin, Departamento San Martin, Peru, obtained on 8 February 1 989 by \n William E. Duellman. \nParatypes. — KU 212269-71 and 212273, three adult males and one \n gravid female, collected with the holotype. \nReferred specimens. — KU 212272, a subadult female, and 212274 and \n 2 1 73 14-15, juveniles, from the type locality; KU 2 1 2275-76, juveniles, from \n 30 km (by road) southwest of Zapatero (ca. 10 km NE of San Jose de Sisa), \n 500 m, Provincia Lamas, Departamento San Martin, Peru. \nDiagnosis. — A member of the Eleutherodactylus unistrigatus group, as \n defined by Lynch (1976), characterized by: (1) skin of dorsum shagreened \n (scattered low tubercles in males), lacking folds; skin on venter areolate; (2) \n tympanum distinct, vertically ovoid, its diameter about one-third diameter of \n eye; (3) snout acutely rounded in dorsal view, bluntly rounded in profile; \n canthus rostralis sharp; (4) upper eyelid broader than interorbital distance, not \n bearing tubercles; cranial crests absent; (5) vomerine dentigerous processes \n prominent, transverse; (6) males with vocal slits and subgular vocal sac; \n nuptial excrescense absent; (7) first finger shorter than second; discs truncate, \n largest on Fingers II— IV; (8) fingers bearing lateral keels; (9) ulnar tubercles \n diffuse; (10) low tubercles on tarsus; tubercles absent on heel; (11) two \n metatarsal tubercles; inner elliptical, 8-10 times size of outer tubercle; (12) \n toes unwebbed, bearing narrow lateral keels and toepads nearly as large as \n those on outer fingers; ( 13) dorsum brown with darker brown marks on back \n and transverse bars on limbs; posterior surfaces of thighs and flanks uniform \n brown; dark brown labial bars; venter brown with cream flecks; (14) adults \n moderate-sized; three males 22.7-25.5 mm SVL, two females 38.0 and 38.8 \n mm SVL. \nEleutherodactylus bearsei most closely resembles E. platydactylus from \n the Amazonian slopes of the Andes in central and southern Peru, E. diadematus \n in the upper Amazon Basin, and a new species from Panguana in Amazonian \n Peru being described by Hedges and Schliiter. Eleutherodactylus platydactylus \n differs from E. bearsei by having larger, conical tubercles on the dorsum, \n";
	 
	 //$text = "HYLID FROGS FROM THE GUIANA HIGHLANDS 3 \nMuseum of Comparative Zoology at Harvard University (MCZ), Museum of \n Natural History at The University of Kansas (KU), National Museum of \n Natural History (USNM), Nationaal Natuurhistorisch Museum (formerly \n Rijksmuseum van Natuurlijke Historie) (RMNH). the University of Guyana \n Department of Biology (UGDB), and the University of Puerto Rico at \n Mayagiiez (UPR-M ). Measurements and structural features follow Duellman \n (1970), except that webbing formula is that of Savage and Heyer (1967). as \n modified by Myers and Duellman ( 1982). Snout-vent length is abbreviated \n SVL. \nHyla Lalrenti, 1768 \nNearly 300 species, most of which are placed in one of more than 40 \n phenetic groups, are recognized in the paraphyletic genus Hyla. Seven of \n these groups occur in the Guianan Region. These are: ( 1 ) Hyla boans group \n (Duellman. 1970). (2) Hyla geographica group (Duellman. 1973). (3) Hyla \n granosa group ( Hoogmoed. 1 979a) , (4) Hyla leucophyllata group ( Duellman, \n 1 970), (5 ) Hyla marmorata group ( Bokermann, 1 964 ), (6) Hyla microcephalia \n group (Duellman. 1970). and (7) Hyla parviceps group (Duellman and \n Crump. 1974). \nTwo of the three new species described herein cannot be relegated to any \n of these recognized species groups. The other new species is a member of the \n Hyla geographica group. For ease of comparison, comparable features are \n numbered sequentially in the diagnoses. \nHyla hadroeeps new species \nHolotype. — KU 69720, an adult male, from area north of Acarai Moun- \n tains, west of New River (ca. 02°N, 58°W). Rupununi District, Guyana, \n obtained in January 1962 by William A. Bently. \nDiagnosis. — The single male has a SVL of 53.9 mm and the following \n characteristics: ( 1 ) body robust; head blunt; ( 2 ) skin on dorsum bearing many \n large, round tubercles: skin of head not co-ossified with underlying dermal \n bones; (3) tympanum distinct; (4) fingers about two-thirds webbed; (5) toes \n nearly fully webbed; (6) fringes and calcars absent on limbs; (7) axillary \n membrane extending to midlength of upper arm; (8) dorsum brown with \n irregular darker brown markings; venter cream with brown flecks; (9) \n vomerine odontophores short, diagonal. \nA subgular vocal sac immediately distinguishes Hyla hadroeeps from \n species of Phrynohyas and Osteocephalus, some of which it resembles \n superficially. The thick tubercular skin, large size, and absence of black and \n orange or yellow flash colors distinguish it from members of the Hyla \n marmorata group. The absence of dermal fringes on the limbs distinguishes \n H. hadroeeps from H. tuberculosa. \n";
	 
	 $text="Station 4760: 53° 53^ N.; 144° 53^ W.; 2,200 fathoms; May 21 Gonatusfabricii. 



Station 4763: 53° 46' N.; 164° 29' W.; 56 fathoms; May 28 Gonatus fubridi. 



Station 4705: 53° 12' N.; 171° 37' W.; 1,217 Mhoms;(Gonatus fubridi. 



May 29. XCrystalloteuthisberingicma. 



Station 4768: 54° 20' 80\" N.; y9°09'30\"E.; 764 fathoms; lime 3. Galiteuthis armata. ";

$text = 'Homestead (25.08S, 116.54E) in West- as long as scape';
	 
	 $text = 'Horseshoe Cove, Bodega Head, Sonoma County (38 18\' 30" N, 123 4\' W). ';
	 
	 $text = '19� 09\' S., 36� 55\' E.';
	 
	 $text = 'St. 3998\'". 7°34\'S., 8°48\'W. 1. III. 1930. ';
	 
	 $text = "Azores Material. 109522, RA^ Atlantis II 49, RHB1916, 
35°30'N 2r46'W, 39-41 m, 2229-0003 h, 24-25.VI.I969, 8:42- 
48 mm SL; 109523, RA' Atlantis II 49, RHB1919, 35°56'N 
22°40'W, 650-750 m, 0708-1030 h, 25.VI.I969, 3:40-43 mm 
SL; 109524, RA^ Atlantis II 49, RHB1920, 36°23'N 23°35'W, 63- 
65 m, 2045-2218 h, 25.VI.1969, 3:42-44 mm SL; 109591, RA' 
Chain 105, RHB2551, 34°49'N 24°32'W, 700-740 m, 1620-1845 
h, 08. VII. 1972, 1:45 mm SL; 109592, R/V Chain 105, RHB2552, 
34°17'N 24°05'W, 60-70 m, 2158-2305 h, 08.VII.1972, 2:40-43 
mm SL; 109671, RA' Delaware 7/63-04, DL63-04:012, 36°57'N 
24°50'W, 180 m, 1730-1815 h, 12.V.1963, 1:42 mm SL. 
Loweina rara (Lutken 1892) ";

$text = '48 30\' 33" E, 13 00\' 01" S';

$text = 'lat 13.869°N, long 89.620°W';

$text= 'COSTA RICA: Heredia 
 Province, unnamed creek at Hwy. 4, ca. 
3 Km from jet. with Hwy. 32 (N10°15\'10", 
 W83°55\'ll"; elev. 200 m) 10.vi.2001, DEB 
 (DB 01-28), 22 larvae (TAMU); La Selva 
 Biological Station, SW Puerto Viejo, Sura 
 Creek at Rio Puerto Viejo (N10°25\'49"; 
 W84°00\'06", elev. 33 m), 09.vi.2001, 8L, 
 DEB (DB 01-26), 8L (5L TAMU, 3L 
 FAMU); Rio Isla Grande at Hwy. 4, ca. 
 5 Km. W. of Rio Frio (N10°23\'31"; 
 W 83°58\'04", elev. 65 m), 10.vi.2001, 
 DEB (DB 01-27), IL (PERC)';
	 
	print_r(points_from_text($text));
	
}

?>