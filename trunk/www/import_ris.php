<?php

require_once('ris.php');

$filename = 'Linnaeus.ris';
$filename = 'import/memvic.ris';
$filename = 'import/zoores.ris';
$filename = 'import/memqueens.ris';
$filename = 'import/raffles.ris';
$filename = 'import/hdl_2246_7.ris';
$filename = 'import/phyllomedusa.ris';
$filename = 'import/snl.ris';
$filename = 'import/raffles-extra.ris';
$filename = 'import/Nuytsia.ris';
$filename = 'import/hdl_2027.42_49534.ris';
$filename = 'import/scz.ris';
$filename = 'import/eurjent.ris';
$filename = 'import/zoomed.ris';
$filename = 'import/zoover.ris';
$filename = 'import/nematologica.ris';
$filename = 'import/0737-4038.ris';
$filename = 'import/contrib.ris';
$filename = 'import/1114.ris';
$filename = 'import/hdl_2027.42_49534.ris';
$filename = 'import/0016-6731.ris';
$filename = 'import/hdl_2324_25.ris';
$filename = 'import/pca.ris';
$filename = 'import/anales.ris';
$filename = 'import/rev.ris';
$filename = 'import/bullmarsci.ris';
$filename = 'import/occkansas.ris';

$filename = 'import/0020-7713.ris';

$filename = 'import/extrapacsci.ris';


/*$files = array('hdl_10125_371','hdl_10125_382','hdl_10125_385','hdl_10125_387','hdl_10125_389','hdl_10125_390','hdl_10125_392','hdl_10125_824','hdl_10125_825','hdl_10125_827','hdl_10125_828','hdl_10125_857','hdl_10125_914','hdl_10125_915','hdl_10125_916','hdl_10125_957','hdl_10125_958','hdl_10125_959','hdl_10125_960','hdl_10125_1037','hdl_10125_1038','hdl_10125_1039','hdl_10125_1040','hdl_10125_1042','hdl_10125_1043','hdl_10125_1044','hdl_10125_1045','hdl_10125_1047','hdl_10125_1048','hdl_10125_1049','hdl_10125_1050','hdl_10125_1052','hdl_10125_1053','hdl_10125_1054','hdl_10125_1055','hdl_10125_491','hdl_10125_492','hdl_10125_493','hdl_10125_494','hdl_10125_366','hdl_10125_367','hdl_10125_428','hdl_10125_429','hdl_10125_496','hdl_10125_497','hdl_10125_498','hdl_10125_499','hdl_10125_635','hdl_10125_636','hdl_10125_637','hdl_10125_639','hdl_10125_431','hdl_10125_432','hdl_10125_433','hdl_10125_434','hdl_10125_962','hdl_10125_969','hdl_10125_972','hdl_10125_973','hdl_10125_975','hdl_10125_976','hdl_10125_977','hdl_10125_978','hdl_10125_980','hdl_10125_981','hdl_10125_982','hdl_10125_983','hdl_10125_455','hdl_10125_456','hdl_10125_457','hdl_10125_458','hdl_10125_625','hdl_10125_626','hdl_10125_627','hdl_10125_628','hdl_10125_1086','hdl_10125_1087','hdl_10125_1088','hdl_10125_1089','hdl_10125_1091','hdl_10125_1092','hdl_10125_1093','hdl_10125_1094','hdl_10125_1116','hdl_10125_1117','hdl_10125_1118','hdl_10125_1119','hdl_10125_1121','hdl_10125_1122','hdl_10125_1123','hdl_10125_1124','hdl_10125_1126','hdl_10125_1127','hdl_10125_1128','hdl_10125_1129','hdl_10125_1131','hdl_10125_1132','hdl_10125_1133','hdl_10125_1134','hdl_10125_630','hdl_10125_631','hdl_10125_632','hdl_10125_633','hdl_10125_451','hdl_10125_452','hdl_10125_453','hdl_10125_454','hdl_10125_2369','hdl_10125_2370','hdl_10125_2371','hdl_10125_2372','hdl_10125_2384','hdl_10125_2385','hdl_10125_2386','hdl_10125_2387','hdl_10125_2389','hdl_10125_2390','hdl_10125_2391','hdl_10125_2392','hdl_10125_2394','hdl_10125_2395','hdl_10125_2396','hdl_10125_2397','hdl_10125_620','hdl_10125_621','hdl_10125_622','hdl_10125_623','hdl_10125_2399','hdl_10125_2400','hdl_10125_2401','hdl_10125_2402');

foreach ($files as $filename)
{
	$filename = 'import/' . $filename . '.ris';

*/


$filename = 'import/tanner.ris';

$filename = 'import/2008.ris';
$filename = 'import/2009a.ris';
$filename = 'import/2009-07-06.ris';

$filename = 'import/acta.ris';
//$filename = 'import/a.ris';

$filename = 'import/zotero.ris';

$filename ='import/0166-0616.ris';

$filename = 'import/2009-08-06.ris';

$filename = 'import/afro.ris';

$filename = 'import/az.ris';
$filename = 'import/recamus.ris';
$filename = 'import/procentsoc.ris';

$filename = 'import/caribion.ris';

$filename = 'import/bzn-zotero.ris';
//$filename = 'import/zoostudies.ris';
//$filename = 'import/z.ris';

$filename = 'import/HerpetologicalMonographs.ris';

$filename = 'import/jhymen.ris';

echo $filename, "\n";

$file = @fopen($filename, "r") or die("could't open file \"$filename\"");
$ris = @fread($file, filesize ($filename));
fclose($file);

import_ris($ris);

/*} */

?>

