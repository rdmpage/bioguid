<?php

require_once('../config.inc.php');
require_once('../lib.php');

// fetch one title
$TitleID = 40214;

$want = array();

// Optional list of just the items we want
$want = array(102858, 103982,
103976,
103983,
103978,
103977,
103975);

$want = array(103975,103977);

$TitleID=3622;
$want=array(110822);

$TitleID=2192;
$want=array(102952);

$TitleID=2804;
$want=array();

// 10428




/*
$TitleID=6525;
$want=array(109055,
109056,
109057,
109058,
109060,
109062,
109063,
109066,
109067,
109068,
109114,
109150,
109151,
109152,
109153,
109163,
109166,
109203,
109566,

110726,
110727,
110718,
110770
);
*/

/*
$TitleID=8070;
$want=array(100655);

$TitleID=42670;
$want=array();

*/

$TitleID = 15774;
$want = array(102950,
107518,
102949,
102947,
102946,
102945,
102944);



$TitleID=46200;
$want=array();

$TitleID=9586;
$want=array(39564);

$TitleID=11325;
$want=array();

$TitleID=2356;
$want=array(55164);

$TitleID=2680;
$want=array(109365,
109442,
109446,
109510,
109570,
109445
);

$want = array(21557,21356);

$want = array(21356);

// TvE
$TitleID=10088;
$want=array(105919);
$want=array(105942);

// Novitates
$TitleID=3882;
$want=array();

$djvu_string = '';

$Titles = array(3066,2977,5133,7188,7192,3037,3082,2666,3529,3410,3368,3500,2689,3438,3580,3855,3421,5080,2708,3376,3395,5137,2930,3176,2808,3097,3020,3131,5644,5642,3127,3167,2849,2893,3329,3483,3403,3400,3408,2944,3190,3122,2844,3085,2871,2851,3000,3029,2823,2932,5578,3206,2966,3187,3002,2857,3044,2841,2951,2982,3181,3129,2801,2872,3125,3209,3042,2843,3109,3137,3126,3099,3189,2912,3205,2854,2865,3051,3551,3544,2692,3504,3128,3521,3409,3152,3265,3574,3370,3335,3533,3844,3351,2890,3336,3456,3526,5582,5359,3458,3146,3415,3419,3123,3148,2669,3478,3259,3452,3369,3276,3440,3555,3256,2653,2645,2654,2616,2634,2551,2588,2625,2563,2657,2580,2646,2548,2574,2609,2637,7139,7176,3816,5576,5575,2597,2556,2632,2576,5561,5564,5562,2596,2647,5565,5583,5573,5574,2585,2566,3235,2794,3217,2782,2723,3011,3201,3052,7186,7204,2546,3072,5580,5577,5579,3195,2827,2726,5570,2805,2891,2847,2724,2869,962,963,2815,2975,3210,2811,2959,2985,3200,2545,2728,2955,2980,2887,5641,2829,2842,2824,3084,3030,3212,3241,3038,2960,2965,2830,2798,3192,3007,2810,3074,3832,3197,3033,2974,2886,3171,2903,2562,7175,3045,3777,2994,3132,2973,3213,3089,2910,3001,3221,3223,3215,2908,3055,2870,3013,3225,3222,3238,2967,3040,2807,3096,3056,3026,2989,3812,2962,3008,2946,3100,2914,3237,3101,3012,2923,2789,2729,3069,3196,2727,2969,3248,3107,3828,2963,2790,3068,2913,3054,3824,3249,3239,3823,3113,2780,3229,2875,2993,3232,2984,3019,3835,2812,3014,2892,3133,3848,2915,3839,3071,3825,3801,3057,2862,3009,2778,3010,3841,3005,3245,3591,3593,3592,3594,2894,2995,2926,3043,2881,3098,3070,2898,3143,2725,3829,2896,3218,3060,2866,2968,2956,2924,3815,2987,2873,7174,2531,2533,7165,2584,2639,2610,7164,2661,2532,2592,7166,3799,2578,2640,2642,3842,5134,2983,2821,3075,2964,3199,3050,2703,2953,2900,3095,5127,3519,5085,3781,3557,3486,3814,3575,3791,5082,2920,5132,5569,5568,5566,5567,3247,2779,3244,2816,3094,2846,3228,2991,3821,2784,3226,2917,5076,3117,5136,5571,2730,3108,2814,3138,2831,2905,2918,3854,3193,2906,2864,2876,2858,3172,2817,3006,2802,3214,2992,3194,3073,2990,3211,3036,2879,2859,2825,2897,2947,3041,2863,2998,3017,3031,3233,3216,2834,3076,3027,3243,3246,3053,2878,2813,2797,3062,3034,7202,3834,3389,2717,2701,4818,3475,3323,3810,3357,3547,3803,3788,3792,2716,3795,3794,5280,3506,3417,3534,7187,3482,3454,3300,3787,3809,3372,3535,3562,3258,3365,2685,2697,3308,3373,3385,3294,3427,3517,3811,3536,3344,3398,3560,3531,2611,5079,6731,2941,2832,3106,3021,3253,3840,3236,4957,2979,3186,2868,2850,2888,3015,3204,3780,2970,2826,3180,3779,3763,3849,3254,3170,3124,2880,3178,2954,2852,3046,2907,2884,3105,2901,3202,2928,3227,3028,3800,3806,3182,3063,2919,3083,3804,2806,3047,3079,2935,2793,3169,2899,2999,2929,2839,3111,3104,2996,3077,3086,3147,2792,2997,3833,2904,2961,5138,3081,2939,5155,3184,3039,7189,3018,2822,2934,3067,2818,2800,2949,3252,5581,3065,2828,2937,3024,3003,3087,3175,2981,3103,2921,2848,2940,3165,2988,3853,3091,4754,2796,3224,3016,2819,2978,3173,2638,2595,2534,2558,2572,2589,2603,3761,2539,2581,2557,2583,2601,2799,2909,3048,7200,3136,2860,5131,5154,3121,3242,3058,3135,3240,3025,2856,903,5572,2544,3359,5433,3278,3554,5422,5336,5287,3272,3145,3159,3269,2713,3305,5454,5429,2631,2633,2554,2553,2535);


$Titles=array(42310);

$Titles=array(42540);
//$want=array(93398);

$Titles=array(10603);

$Titles=array(3179);

$Titles=array(7383);

$Titles=array(10088);
$want=array(40981);

// Proc USNM
$Titles=array(7519);
$want=array();

// Bull Zool Soc FR

$Titles=array(7415);
$want=array();

$Titles=array(49174);
$want=array();

$Titles=array(11103);
$want=array();

// Ann Ent Soc Fr
$Titles=array(8188);
$want=array();

// D E Z.
$Titles=array(48608);
$want=array();

// Tijd Ned Dierk Ver
$Titles=array(8982);
$want=array();

// Entomological News
$Titles=array(2356);
$want=array();

$Titles=array(2359);
$want=array();

// Comptes rendus des séances de la Société de biologie et de ses filiales
$Titles=array(8070);
$want=array();

$Titles =array(15534);
$want=array();

// Bull MCZ
$Titles=array(2803);
$want=array();

// Tulane studies in zoology and botany
$Titles=array(5361);
$want=array();

$Titles=array(48421);
$want=array();

//Bulletin of the Southern California Academy of Sciences
$Titles=array(4949);
$want=array();

//The University of Kansas science bulletin.
$Titles=array(3179);
$want=array();

// Ann Soc ent Belge
$Titles=array(11933);
$want=array();


// Rec Ind Mus
$Titles=array(10294);
$want=array();

// Transaction of Linnaen Society series 2
$Titles=array(51416);
$want=array();

// B Z N
$Titles = array(51603);
$want=array();

// Insecutor inscitiae menstruus
$Titles=array(8145);
$want=array();

// Proc Zool Soc Lond
$Titles=array(44963);
$want=array();

// Revision of the caddisfly genus Psilotreta (Trichoptera: Odontoceridae) 
$Titles=array(52238);
$want=array();

$Titles=array(52368);
$want=array();

$Titles=array(2202);
$want=array();


// Revue suisse de zoologie
$Titles=array(8981);
$want=array();

// Bulletin du Muséum national d'histoire naturelle
$Titles=array(5943);
$want=array();

$Titles=array(49359);
$want=array();

// Proceedings of the California Academy of Sciences, 4th series
$Titles=array(3943);
$want=array();

// Bulletin of entomological research
$Titles=array(10305);
$want=array();

// Bulletin de la Société philomathique de Paris
$Titles=array(9580);
$want=array();

$Titles=array(51416);
$want=array();

$Titles=array(6928);
$want=array();

// Discovery Reports
$Titles=array(6168);
$want=array();


$Titles=array(8128);
$want=array();

$Titles=array(15727);
$want=array(84911);


$Titles=array(45493);
$want=array();

$Titles=array(46204);
$want=array();

$Titles=array(52289);
$want=array();

// Notes from the Leyden Museum
$Titles=array(8740);
$want=array();

// Tulane
$Titles=array(3119);
$want=array();

// Rec Ind Mus 1922 vol. 24
$Titles=array(53477);
$want=array();

// Mem Indian Museum
$Titles=array(52566);
$want=array();

// Nouvelles archives du Muséum d'histoire naturelle
$Titles=array(52015);
$want=array();


// Revue d'entomologie
$Titles=array(10428);
$want=array();

// Archiv für Naturgeschichte
$Titles=array(6638);
$want=array();

//Archiv für Naturgeschichte. Abteilung B.
//$Titles=array(12937);
//$want=array();

//Archiv für Naturgeschichte. Abteilung A.
//$Titles=array(12938);
//$want=array();

$Titles =array(51603);
$want=array(44798);

$Titles =array(8942);
$want=array();

$Titles =array(3179);
$want=array(40698);

// Annali del Museo civico di storia naturale di Genova
$Titles =array(7929);
$want=array(33428);

// Mitteilungen der Münchner Entomologischen Gesellschaft
$Titles = array(15739);
$want=array();

// Archivos do Museu Nacional do Rio de Janeiro
$Titles = array(6524);
$want=array();

// Bull Z Nom
$Titles=array(51603);
$want=array(44292);

$Titles = array(50753);
$want=array();

// Arkiv
$Titles = array(6919);
$want=array();

// 	 Journal of shellfish research
$Titles = array(2179);
$want=array();

// Transactions of the Entomological Society of London
$Titles = array(11516);
$want=array();

$Titles =array(2510);
$want=array(89742);

// Transactions of the San Diego Society of Natural History
$Titles=array(3144);
$want=array();

$Titles=array(16284);
$want=array();

$Titles=array(16268);
$want=array();

$Titles=array(4050);
$want=array(55046,55069,55047,25735);

$Titles = array(13264);
$want=array();

// Bullettino della Società entomologica italiana
$Titles =array(9612);
$want=array(39875);

// Entomological News
$Titles =array(2356);
$want=array(113866,113867,113865);

// Verhandlungen der Naturforschenden Gesellschaft in Basel
$Titles =array(46540);
$want=array(106380);

// Great Basin Naturalist
$Titles=array(7928);
$want=array();

// Occasional papers of the California Academy of Sciences
$Titles = array(7410);
$want=array();

$Titles = array(3179);
$want=array(25828);

// Zool Jahr Ab Syst
$Titles = array(8980);
$want=array();

// Terra Nova
$Titles = array(42665);
$want=array();
$Titles = array(18281);
$want=array();

$Titles=array(7928);
$want=array(33366);

$Titles=array(7922);
$want=array();

//$Titles=array(14107);
//$want=array();

$Titles=array(7933);
$want = array(63349);

// American Journal of Science
$Titles=array(14965);
$want = array(113468);

$Titles=array(14924);
$want = array();

// Discovery Reports (one set)
$Titles=array(15981);
$want = array();

// Journal of the College of Agriculture
$Titles=array(8662);
$want = array();

// Papers and proceedings of the Royal Society of Tasmania
$Titles=array(9494);
$want = array();

// Liangqi baxing dongwu yanjiu = Acta herpetologica Sinica
$Titles=array(53832);
$want = array();

$Titles=array(53833);
$want = array();

$Titles=array(3141);
$want = array();

// Annali del Museo civico di storia naturale di Genova
$Titles =array(7929);
$want=array();

// Memoirs of the Queensland Museum
$Titles=array(12912);
$want=array();

// Comptes rendus des séances de la Société de biologie et de ses filiales
$Titles=array(8070);
$want=array(108557);

// Bericht über die Senckenbergische Naturforschende Gesellschaft in Frankfurt am Main
$Titles=array(8745);
$want=array(33793);

// Bollettino dei musei di zoologia ed anatomia comparata della R. Università di Torino
$Titles=array(10776);
$want=array();


$Titles=array(6335);
$want=array();



$Titles = array(50753);
$want=array();

// Proc USNM
$Titles=array(7519);
$want=array(32319);

$Titles=array(2744);
$want=array();

$Titles = array(49442);
$want=array(104470);

$Titles = array(48522);
$want=array();

$Titles = array(4252);
$want=array();

$Titles = array(46202);
$want=array();

$Titles=array(10241);
$want=array();

// Proceedings of the New England Zoölogical Club.
$Titles=array(10605);
$want=array();

// Jahrbuch der Hamburgischen Wissenschaftlichen Anstalten
$Titles=array(9594);
$want=array();

// Entomologische Blätter
$Titles=array(50899);
$want=array();

// Memoirs of the American Entomological Society
$Titles=array(6193);
$want=array();

// Annali del Museo civico di storia naturale Giacomo Doria
$Titles=array(43408);
$want=array();

// Verhandlungen der Naturforschenden Gesellschaft in Basel.
$Titles = array(46540);
$want=array();

// Spolia zeylanica
$Titles = array(10229);
$want=array();

// Spolia zeylanica
$Titles = array(10229);
$want=array();

// Journal of the Asiatic Society of Bengal
$Titles = array(51678);
$want=array();

$Titles = array(13353);
$want = array(49895);

// Proceedings of the Malacological Society of London
$Titles = array(15224);
$want=array();

// Archives de zoologie expérimentale et générale.
$Titles = array(5559);
$want=array(27784);

// Pomona College journal of entomology
$Titles = array(8154);
$want=array();


$Titles = array(7519);
$want=array(53452);

//Bulletin of the British Ornithologists' Club.
$Titles = array(46639);
$want=array();

$Titles=array(43431);
$want=array();

// Arbeiten aus dem Zoologischen Instituten der Universität Wien und der Zoologischen Station in Triest
$Titles=array(6106);
$want=array();

// Decapod crustacea of Bermuda
$Titles=array(23648);
$want=array(64731);

$Titles=array(48602);
$want=array();

// Biologia Centrali-Americana
$Titles=array(730);
$want=array();


$Titles = array(17344);
$want=array();

$Titles = array(20608);
$want=array();

$Titles = array(2087);
$want=array();

$Titles = array(44766);
$want=array();

$Titles = array(11687);
$want=array();

$Titles=array(1730);
$want=array();

// Archives de zoologie expérimentale et générale.
$Titles = array(5559);
$want=array();


foreach ($Titles as $TitleID)
{
 //$want=array();

$use_bhl_au = false;

if ($use_bhl_au)
{
	// BHL AU
	//$url = 'http://bhl.ala.org.au/api/rest?op=GetTitleMetadata&titleid=' . $TitleID . '&items=true&apikey=' . $config['bhl_api_key'] . '&format=json';
}
else
{
	// BHL
	$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetTitleMetadata&titleid=' . $TitleID . '&items=true&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';
}

//echo $url . "\n";

$json = get($url);
$title_obj = json_decode($json);

//print_r($title_obj);
//exit();



if ($title_obj->Status == 'ok')
{

	$sql = "REPLACE INTO bhl_title(TitleID, FullTitle, ShortTitle) VALUES ("
		. $title_obj->Result->TitleID . ",'" . addslashes($title_obj->Result->FullTitle) . "','" . addslashes($title_obj->Result->ShortTitle) . "');";
	echo $sql . "\n";

	foreach ($title_obj->Result->Items as $item_obj)
	{
		$go = false;
		if (count($want) == 0)
		{
			$go = true;
		}
		else
		{
			if (in_array($item_obj->ItemID, $want))
			{
				$go = true;
			}
		}
		if ($go)
		{
		
			if ($use_bhl_au)
			{
				$url = 'http://bhl.ala.org.au/api/rest?op=GetItemMetadata&itemid=' . $item_obj->ItemID . '&pages=t&apikey=' . $config['bhl_api_key'] . '&format=json';
			}
			else
			{
				$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetItemMetadata&itemid=' . $item_obj->ItemID . '&pages=t&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';
			}
			
			$json = get($url);
			
			$obj = json_decode($json);
			
			//print_r($obj);
			//exit();
			
			if (($obj->Status == 'ok') && ($obj->Result != '')) // ok doesn't mean we have anything :(
			{
				
				$djvu_string .= "'" . $obj->Result->ItemID . "' => '" .  $obj->Result->SourceIdentifier . "',\n";
			
				
				// Item
				$sql = "DELETE FROM bhl_item WHERE ItemID=" . $obj->Result->ItemID . ";";
				echo $sql . "\n";
				
				$sql = "INSERT INTO bhl_item(ItemID,TitleID,VolumeInfo) VALUES("
				. $obj->Result->ItemID . ',' . $obj->Result->PrimaryTitleID . ",'" . addslashes($obj->Result->Volume) . "');";
				
				echo $sql . "\n";
				
				$sql = 'DELETE FROM bhl_page WHERE ItemID=' . $obj->Result->ItemID . ";";
				echo $sql;
		
		
				// Pages
				foreach ($obj->Result->Pages as $k => $v)
				{
					// Metadata about pages
					$keys = array();
					$values = array();
					
					// PageID
					$keys[] = 'PageID';
					$values[] = $v->PageID;
					
					// ItemID
					$keys[] = 'ItemID';
					$values[] = $v->ItemID;
			
					// Is page numbered?
					if (count($v->PageNumbers) > 0)
					{
						$keys[] = 'PagePrefix';
						$values[] = '"' . $v->PageNumbers[0]->Prefix . '"';
			
						$keys[] = 'PageNumber';
						$values[] = '"' . $v->PageNumbers[0]->Number . '"';
					}
			
					if (count($v->PageTypes) > 0)
					{
						$keys[] = 'PageTypeName';
						$values[] = '"' . $v->PageTypes[0]->PageTypeName . '"';
					}
					//$sql = 'DELETE FROM bhl_page WHERE PageID=' . $v->PageID . ';';
					//echo $sql . "\n";
					$sql = 'INSERT INTO bhl_page (' . implode (",", $keys) . ') VALUES (' . implode (",", $values) . ');';
					echo $sql . "\n";
				
				
					// Order of pages
					// pages has PageID as primary key
					$sql = 'REPLACE INTO page (PageID,ItemID,FileNamePrefix,SequenceOrder) VALUES ('
						.        $v->PageID
						. ','  . $v->ItemID
						. ',"' . $obj->Result->SourceIdentifier . sprintf("_%04d",  ($k+1)) . '"'
						. ','  . ($k+1)
						. ');';
						
					echo $sql . "\n";
						
				} 
			}
		}
	}	
}

}

file_put_contents($TitleID . 'djvu.txt', $djvu_string);

?>