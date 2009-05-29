<?php

$format = 'html';
$feed_prefix = 'http://bioguid.info/rss/';

if (isset($_GET['format']))
{
	$format = $_GET['format'];
	
	switch ($format)
	{
		case 'opml':
		case 'html':
			break;
			
		default:
			$format = 'html';
	}
}

// List of feeds I generate, placed in categories
$feeds = array(

	// New taxon feeds
	
	'New taxa' => array(
		array('title' => 'CiNii "sp.nov." search', 'url' => 'http://ci.nii.ac.jp/opensearch/search?q=sp.%20nov.&range=0sortorder=1&start=1&count=20&format=rss'),
		array('title' => 'PubMed "n. sp." search', 'url' => 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/erss.cgi?rss_guid=149V05c98i9HlVNyPr24_qmDKX6MT0D31yJ_v20ilbVHi8AQO'),
		array('title' => 'uBio', 'url' => 'ubio.php')
	),		

	// Barcodes
	
	'Barcodes' => array(
		array('title' => 'Birds', 'url' => 'barcode.php?taxon_id=8782'),
		array('title' => 'Amphibia', 'url' => 'barcode.php?taxon_id=8292'),
		array('title' => 'Fish', 'url' => 'barcode.php?taxon_id=7898'),
		array('title' => 'Insects', 'url' => 'barcode.php?taxon_id=6960'),
		array('title' => 'Crustacea', 'url' => 'barcode.php?taxon_id=6657'),
		array('title' => 'Fungi', 'url' => 'barcode.php?taxon_id=4751'),
		array('title' => 'Plants', 'url' => 'barcode.php?taxon_id=3193')
	),
	
	'GenBank' => array(
		array('title' => 'Geotagged sequences', 'url' => 'genbank.php')
	),	

	// Fungi
	
	'Fungi' => array(
		array('title' => 'Mycobank', 'url' => 'mycobank.php'),
		),
		
	// Animals
	
	'Animals' => array(
		array('title' => 'Zoobank', 'url' => 'zoobank.php'),
		array('title' => 'Zootaxa', 'url' => 'zootaxa.php'),
		
		// Zootaxa taxa
	array('title' => 'Zootaxa Acanthocephala', 'url' => 'zootaxabytaxon.php?taxon=Acanthocephala'),
	array('title' => 'Zootaxa Acari', 'url' => 'zootaxabytaxon.php?taxon=Acari'),
	array('title' => 'Zootaxa Amblypygi', 'url' => 'zootaxabytaxon.php?taxon=Amblypygi'),
	array('title' => 'Zootaxa Amphibia', 'url' => 'zootaxabytaxon.php?taxon=Amphibia'),
	array('title' => 'Zootaxa Annelida', 'url' => 'zootaxabytaxon.php?taxon=Annelida'),
	array('title' => 'Zootaxa Araneae', 'url' => 'zootaxabytaxon.php?taxon=Araneae'),
	array('title' => 'Zootaxa Ascidiacea', 'url' => 'zootaxabytaxon.php?taxon=Ascidiacea'),
	array('title' => 'Zootaxa Aves', 'url' => 'zootaxabytaxon.php?taxon=Aves'),
	array('title' => 'Zootaxa Blattodea', 'url' => 'zootaxabytaxon.php?taxon=Blattodea'),
	array('title' => 'Zootaxa Brachiopoda', 'url' => 'zootaxabytaxon.php?taxon=Brachiopoda'),
	array('title' => 'Zootaxa Bryozoa', 'url' => 'zootaxabytaxon.php?taxon=Bryozoa'),
	array('title' => 'Zootaxa Coelenterata', 'url' => 'zootaxabytaxon.php?taxon=Coelenterata'),
	array('title' => 'Zootaxa Coleoptera', 'url' => 'zootaxabytaxon.php?taxon=Coleoptera'),
	array('title' => 'Zootaxa Collembola', 'url' => 'zootaxabytaxon.php?taxon=Collembola'),
	array('title' => 'Zootaxa Crustacea', 'url' => 'zootaxabytaxon.php?taxon=Crustacea'),
	array('title' => 'Zootaxa Ctenophora', 'url' => 'zootaxabytaxon.php?taxon=Ctenophora'),
	array('title' => 'Zootaxa Diplura', 'url' => 'zootaxabytaxon.php?taxon=Diplura'),
	array('title' => 'Zootaxa Diptera', 'url' => 'zootaxabytaxon.php?taxon=Diptera'),
	array('title' => 'Zootaxa Echinodermata', 'url' => 'zootaxabytaxon.php?taxon=Echinodermata'),
	array('title' => 'Zootaxa Embioptera', 'url' => 'zootaxabytaxon.php?taxon=Embioptera'),
	array('title' => 'Zootaxa Ephemeroptera', 'url' => 'zootaxabytaxon.php?taxon=Ephemeroptera'),
	array('title' => 'Zootaxa Gnathostomulida', 'url' => 'zootaxabytaxon.php?taxon=Gnathostomulida'),
	array('title' => 'Zootaxa Hemiptera', 'url' => 'zootaxabytaxon.php?taxon=Hemiptera'),
	array('title' => 'Zootaxa Hymenoptera', 'url' => 'zootaxabytaxon.php?taxon=Hymenoptera'),
	array('title' => 'Zootaxa Isoptera', 'url' => 'zootaxabytaxon.php?taxon=Isoptera'),
	array('title' => 'Zootaxa Lepidoptera', 'url' => 'zootaxabytaxon.php?taxon=Lepidoptera'),
	array('title' => 'Zootaxa Mammalia', 'url' => 'zootaxabytaxon.php?taxon=Mammalia'),
	array('title' => 'Zootaxa Mantodea', 'url' => 'zootaxabytaxon.php?taxon=Mantodea'),
	array('title' => 'Zootaxa Mecoptera', 'url' => 'zootaxabytaxon.php?taxon=Mecoptera'),
	array('title' => 'Zootaxa Megaloptera', 'url' => 'zootaxabytaxon.php?taxon=Megaloptera'),
	array('title' => 'Zootaxa Mollusca', 'url' => 'zootaxabytaxon.php?taxon=Mollusca'),
	array('title' => 'Zootaxa Myriapoda', 'url' => 'zootaxabytaxon.php?taxon=Myriapoda'),
	array('title' => 'Zootaxa Nematoda', 'url' => 'zootaxabytaxon.php?taxon=Nematoda'),
	array('title' => 'Zootaxa Nemertea', 'url' => 'zootaxabytaxon.php?taxon=Nemertea'),
	array('title' => 'Zootaxa Neuroptera', 'url' => 'zootaxabytaxon.php?taxon=Neuroptera'),
	array('title' => 'Zootaxa Odonata', 'url' => 'zootaxabytaxon.php?taxon=Odonata'),
	array('title' => 'Zootaxa Onychophora', 'url' => 'zootaxabytaxon.php?taxon=Onychophora'),
	array('title' => 'Zootaxa Opiliones', 'url' => 'zootaxabytaxon.php?taxon=Opiliones'),
	array('title' => 'Zootaxa Orthoptera', 'url' => 'zootaxabytaxon.php?taxon=Orthoptera'),
	array('title' => 'Zootaxa Palpigradi', 'url' => 'zootaxabytaxon.php?taxon=Palpigradi'),
	array('title' => 'Zootaxa Phasmatodea', 'url' => 'zootaxabytaxon.php?taxon=Phasmatodea'),
	array('title' => 'Zootaxa Phthiraptera', 'url' => 'zootaxabytaxon.php?taxon=Phthiraptera'),
	array('title' => 'Zootaxa Pisces', 'url' => 'zootaxabytaxon.php?taxon=Pisces'),
	array('title' => 'Zootaxa Platyhelminthes', 'url' => 'zootaxabytaxon.php?taxon=Platyhelminthes'),
	array('title' => 'Zootaxa Plecoptera', 'url' => 'zootaxabytaxon.php?taxon=Plecoptera'),
	array('title' => 'Zootaxa Plectoptera', 'url' => 'zootaxabytaxon.php?taxon=Plectoptera'),
	array('title' => 'Zootaxa Porifera', 'url' => 'zootaxabytaxon.php?taxon=Porifera'),
	array('title' => 'Zootaxa Protozoa', 'url' => 'zootaxabytaxon.php?taxon=Protozoa'),
	array('title' => 'Zootaxa Protura', 'url' => 'zootaxabytaxon.php?taxon=Protura'),
	array('title' => 'Zootaxa Pseudoscorpiones', 'url' => 'zootaxabytaxon.php?taxon=Pseudoscorpiones'),
	array('title' => 'Zootaxa Psocoptera', 'url' => 'zootaxabytaxon.php?taxon=Psocoptera'),
	array('title' => 'Zootaxa Pycnogonida', 'url' => 'zootaxabytaxon.php?taxon=Pycnogonida'),
	array('title' => 'Zootaxa Reptilia', 'url' => 'zootaxabytaxon.php?taxon=Reptilia'),
	array('title' => 'Zootaxa Ricinulei', 'url' => 'zootaxabytaxon.php?taxon=Ricinulei'),
	array('title' => 'Zootaxa Rotifera', 'url' => 'zootaxabytaxon.php?taxon=Rotifera'),
	array('title' => 'Zootaxa Schizomida', 'url' => 'zootaxabytaxon.php?taxon=Schizomida'),
	array('title' => 'Zootaxa Scorpiones', 'url' => 'zootaxabytaxon.php?taxon=Scorpiones'),
	array('title' => 'Zootaxa Siphonaptera', 'url' => 'zootaxabytaxon.php?taxon=Siphonaptera'),
	array('title' => 'Zootaxa Sipuncula', 'url' => 'zootaxabytaxon.php?taxon=Sipuncula'),
	array('title' => 'Zootaxa Solifugae', 'url' => 'zootaxabytaxon.php?taxon=Solifugae'),
	array('title' => 'Zootaxa Strepsiptera', 'url' => 'zootaxabytaxon.php?taxon=Strepsiptera'),
	array('title' => 'Zootaxa Tardigrada', 'url' => 'zootaxabytaxon.php?taxon=Tardigrada'),
	array('title' => 'Zootaxa Thysanoptera', 'url' => 'zootaxabytaxon.php?taxon=Thysanoptera'),
	array('title' => 'Zootaxa Trichoptera', 'url' => 'zootaxabytaxon.php?taxon=Trichoptera'),
	array('title' => 'Zootaxa Uropygida', 'url' => 'zootaxabytaxon.php?taxon=Uropygida')	
		
		
		
		),
		
	'Plants' => array(
		// IPNI
		array('title' => 'IPNI Acanthaceae', 'url' => 'ipni.php?family=Acanthaceae'),
		array('title' => 'IPNI Aceraceae', 'url' => 'ipni.php?family=Aceraceae'),
		array('title' => 'IPNI Actinidiaceae', 'url' => 'ipni.php?family=Actinidiaceae'),
		array('title' => 'IPNI Aizoaceae', 'url' => 'ipni.php?family=Aizoaceae'),
		array('title' => 'IPNI Alismataceae', 'url' => 'ipni.php?family=Alismataceae'),
		array('title' => 'IPNI Amaranthaceae', 'url' => 'ipni.php?family=Amaranthaceae'),
		array('title' => 'IPNI Amaryllidaceae', 'url' => 'ipni.php?family=Amaryllidaceae'),
		array('title' => 'IPNI Amblystegiaceae', 'url' => 'ipni.php?family=Amblystegiaceae'),
		array('title' => 'IPNI Anacardiaceae', 'url' => 'ipni.php?family=Anacardiaceae'),
		array('title' => 'IPNI Aneuraceae', 'url' => 'ipni.php?family=Aneuraceae'),
		array('title' => 'IPNI Annonaceae', 'url' => 'ipni.php?family=Annonaceae'),
		array('title' => 'IPNI Apiaceae', 'url' => 'ipni.php?family=Apiaceae'),
		array('title' => 'IPNI Apocynaceae', 'url' => 'ipni.php?family=Apocynaceae'),
		array('title' => 'IPNI Aquifoliaceae', 'url' => 'ipni.php?family=Aquifoliaceae'),
		array('title' => 'IPNI Araceae', 'url' => 'ipni.php?family=Araceae'),
		array('title' => 'IPNI Araliaceae', 'url' => 'ipni.php?family=Araliaceae'),
		array('title' => 'IPNI Arecaceae', 'url' => 'ipni.php?family=Arecaceae'),
		array('title' => 'IPNI Aristolochiaceae', 'url' => 'ipni.php?family=Aristolochiaceae'),
		array('title' => 'IPNI Asclepiadaceae', 'url' => 'ipni.php?family=Asclepiadaceae'),
		array('title' => 'IPNI Aspleniaceae', 'url' => 'ipni.php?family=Aspleniaceae'),
		array('title' => 'IPNI Asteraceae', 'url' => 'ipni.php?family=Asteraceae'),
		array('title' => 'IPNI Balsaminaceae', 'url' => 'ipni.php?family=Balsaminaceae'),
		array('title' => 'IPNI Bartramiaceae', 'url' => 'ipni.php?family=Bartramiaceae'),
		array('title' => 'IPNI Begoniaceae', 'url' => 'ipni.php?family=Begoniaceae'),
		array('title' => 'IPNI Berberidaceae', 'url' => 'ipni.php?family=Berberidaceae'),
		array('title' => 'IPNI Betulaceae', 'url' => 'ipni.php?family=Betulaceae'),
		array('title' => 'IPNI Bignoniaceae', 'url' => 'ipni.php?family=Bignoniaceae'),
		array('title' => 'IPNI Blechnaceae', 'url' => 'ipni.php?family=Blechnaceae'),
		array('title' => 'IPNI Bombacaceae', 'url' => 'ipni.php?family=Bombacaceae'),
		array('title' => 'IPNI Boraginaceae', 'url' => 'ipni.php?family=Boraginaceae'),
		array('title' => 'IPNI Brachytheciaceae', 'url' => 'ipni.php?family=Brachytheciaceae'),
		array('title' => 'IPNI Brassicaceae', 'url' => 'ipni.php?family=Brassicaceae'),
		array('title' => 'IPNI Bromeliaceae', 'url' => 'ipni.php?family=Bromeliaceae'),
		array('title' => 'IPNI Bryaceae', 'url' => 'ipni.php?family=Bryaceae'),
		array('title' => 'IPNI Burseraceae', 'url' => 'ipni.php?family=Burseraceae'),
		array('title' => 'IPNI Cactaceae', 'url' => 'ipni.php?family=Cactaceae'),
		array('title' => 'IPNI Calymperaceae', 'url' => 'ipni.php?family=Calymperaceae'),
		array('title' => 'IPNI Campanulaceae', 'url' => 'ipni.php?family=Campanulaceae'),
		array('title' => 'IPNI Capparaceae', 'url' => 'ipni.php?family=Capparaceae'),
		array('title' => 'IPNI Caprifoliaceae', 'url' => 'ipni.php?family=Caprifoliaceae'),
		array('title' => 'IPNI Caryophyllaceae', 'url' => 'ipni.php?family=Caryophyllaceae'),
		array('title' => 'IPNI Celastraceae', 'url' => 'ipni.php?family=Celastraceae'),
		array('title' => 'IPNI Chenopodiaceae', 'url' => 'ipni.php?family=Chenopodiaceae'),
		array('title' => 'IPNI Chrysobalanaceae', 'url' => 'ipni.php?family=Chrysobalanaceae'),
		array('title' => 'IPNI Cistaceae', 'url' => 'ipni.php?family=Cistaceae'),
		array('title' => 'IPNI Clusiaceae', 'url' => 'ipni.php?family=Clusiaceae'),
		array('title' => 'IPNI Combretaceae', 'url' => 'ipni.php?family=Combretaceae'),
		array('title' => 'IPNI Commelinaceae', 'url' => 'ipni.php?family=Commelinaceae'),
		array('title' => 'IPNI Connaraceae', 'url' => 'ipni.php?family=Connaraceae'),
		array('title' => 'IPNI Convolvulaceae', 'url' => 'ipni.php?family=Convolvulaceae'),
		array('title' => 'IPNI Cornaceae', 'url' => 'ipni.php?family=Cornaceae'),
		array('title' => 'IPNI Crassulaceae', 'url' => 'ipni.php?family=Crassulaceae'),
		array('title' => 'IPNI Cucurbitaceae', 'url' => 'ipni.php?family=Cucurbitaceae'),
		array('title' => 'IPNI Cunoniaceae', 'url' => 'ipni.php?family=Cunoniaceae'),
		array('title' => 'IPNI Cupressaceae', 'url' => 'ipni.php?family=Cupressaceae'),
		array('title' => 'IPNI Cyatheaceae', 'url' => 'ipni.php?family=Cyatheaceae'),
		array('title' => 'IPNI Cycadaceae', 'url' => 'ipni.php?family=Cycadaceae'),
		array('title' => 'IPNI Cyperaceae', 'url' => 'ipni.php?family=Cyperaceae'),
		array('title' => 'IPNI Daltoniaceae', 'url' => 'ipni.php?family=Daltoniaceae'),
		array('title' => 'IPNI Dennstaedtiaceae', 'url' => 'ipni.php?family=Dennstaedtiaceae'),
		array('title' => 'IPNI Dicranaceae', 'url' => 'ipni.php?family=Dicranaceae'),
		array('title' => 'IPNI Dilleniaceae', 'url' => 'ipni.php?family=Dilleniaceae'),
		array('title' => 'IPNI Dioscoreaceae', 'url' => 'ipni.php?family=Dioscoreaceae'),
		array('title' => 'IPNI Dipsacaceae', 'url' => 'ipni.php?family=Dipsacaceae'),
		array('title' => 'IPNI Ditrichaceae', 'url' => 'ipni.php?family=Ditrichaceae'),
		array('title' => 'IPNI Dryopteridaceae', 'url' => 'ipni.php?family=Dryopteridaceae'),
		array('title' => 'IPNI Ebenaceae', 'url' => 'ipni.php?family=Ebenaceae'),
		array('title' => 'IPNI Elaeocarpaceae', 'url' => 'ipni.php?family=Elaeocarpaceae'),
		array('title' => 'IPNI Entodontaceae', 'url' => 'ipni.php?family=Entodontaceae'),
		array('title' => 'IPNI Ericaceae', 'url' => 'ipni.php?family=Ericaceae'),
		array('title' => 'IPNI Eriocaulaceae', 'url' => 'ipni.php?family=Eriocaulaceae'),
		array('title' => 'IPNI Erythroxylaceae', 'url' => 'ipni.php?family=Erythroxylaceae'),
		array('title' => 'IPNI Euphorbiaceae', 'url' => 'ipni.php?family=Euphorbiaceae'),
		array('title' => 'IPNI Fabaceae', 'url' => 'ipni.php?family=Fabaceae'),
		array('title' => 'IPNI Fagaceae', 'url' => 'ipni.php?family=Fagaceae'),
		array('title' => 'IPNI Fissidentaceae', 'url' => 'ipni.php?family=Fissidentaceae'),
		array('title' => 'IPNI Flacourtiaceae', 'url' => 'ipni.php?family=Flacourtiaceae'),
		array('title' => 'IPNI Funariaceae', 'url' => 'ipni.php?family=Funariaceae'),
		array('title' => 'IPNI Gentianaceae', 'url' => 'ipni.php?family=Gentianaceae'),
		array('title' => 'IPNI Geraniaceae', 'url' => 'ipni.php?family=Geraniaceae'),
		array('title' => 'IPNI Gesneriaceae', 'url' => 'ipni.php?family=Gesneriaceae'),
		array('title' => 'IPNI Gleicheniaceae', 'url' => 'ipni.php?family=Gleicheniaceae'),
		array('title' => 'IPNI Grammitidaceae', 'url' => 'ipni.php?family=Grammitidaceae'),
		array('title' => 'IPNI Grimmiaceae', 'url' => 'ipni.php?family=Grimmiaceae'),
		array('title' => 'IPNI Hippocrateaceae', 'url' => 'ipni.php?family=Hippocrateaceae'),
		array('title' => 'IPNI Hookeriaceae', 'url' => 'ipni.php?family=Hookeriaceae'),
		array('title' => 'IPNI Hydrophyllaceae', 'url' => 'ipni.php?family=Hydrophyllaceae'),
		array('title' => 'IPNI Hymenophyllaceae', 'url' => 'ipni.php?family=Hymenophyllaceae'),
		array('title' => 'IPNI Hypnaceae', 'url' => 'ipni.php?family=Hypnaceae'),
		array('title' => 'IPNI Icacinaceae', 'url' => 'ipni.php?family=Icacinaceae'),
		array('title' => 'IPNI Iridaceae', 'url' => 'ipni.php?family=Iridaceae'),
		array('title' => 'IPNI Jubulaceae', 'url' => 'ipni.php?family=Jubulaceae'),
		array('title' => 'IPNI Juncaceae', 'url' => 'ipni.php?family=Juncaceae'),
		array('title' => 'IPNI Jungermanniaceae', 'url' => 'ipni.php?family=Jungermanniaceae'),
		array('title' => 'IPNI Lamiaceae', 'url' => 'ipni.php?family=Lamiaceae'),
		array('title' => 'IPNI Lauraceae', 'url' => 'ipni.php?family=Lauraceae'),
		array('title' => 'IPNI Lecythidaceae', 'url' => 'ipni.php?family=Lecythidaceae'),
		array('title' => 'IPNI Lejeuneaceae', 'url' => 'ipni.php?family=Lejeuneaceae'),
		array('title' => 'IPNI Lentibulariaceae', 'url' => 'ipni.php?family=Lentibulariaceae'),
		array('title' => 'IPNI Lepidoziaceae', 'url' => 'ipni.php?family=Lepidoziaceae'),
		array('title' => 'IPNI Leskeaceae', 'url' => 'ipni.php?family=Leskeaceae'),
		array('title' => 'IPNI Leucodontaceae', 'url' => 'ipni.php?family=Leucodontaceae'),
		array('title' => 'IPNI Liliaceae', 'url' => 'ipni.php?family=Liliaceae'),
		array('title' => 'IPNI Linaceae', 'url' => 'ipni.php?family=Linaceae'),
		array('title' => 'IPNI Loasaceae', 'url' => 'ipni.php?family=Loasaceae'),
		array('title' => 'IPNI Loganiaceae', 'url' => 'ipni.php?family=Loganiaceae'),
		array('title' => 'IPNI Lomariopsidaceae', 'url' => 'ipni.php?family=Lomariopsidaceae'),
		array('title' => 'IPNI Loranthaceae', 'url' => 'ipni.php?family=Loranthaceae'),
		array('title' => 'IPNI Lycopodiaceae', 'url' => 'ipni.php?family=Lycopodiaceae'),
		array('title' => 'IPNI Lythraceae', 'url' => 'ipni.php?family=Lythraceae'),
		array('title' => 'IPNI Magnoliaceae', 'url' => 'ipni.php?family=Magnoliaceae'),
		array('title' => 'IPNI Malpighiaceae', 'url' => 'ipni.php?family=Malpighiaceae'),
		array('title' => 'IPNI Malvaceae', 'url' => 'ipni.php?family=Malvaceae'),
		array('title' => 'IPNI Marantaceae', 'url' => 'ipni.php?family=Marantaceae'),
		array('title' => 'IPNI Melastomataceae', 'url' => 'ipni.php?family=Melastomataceae'),
		array('title' => 'IPNI Meliaceae', 'url' => 'ipni.php?family=Meliaceae'),
		array('title' => 'IPNI Menispermaceae', 'url' => 'ipni.php?family=Menispermaceae'),
		array('title' => 'IPNI Meteoriaceae', 'url' => 'ipni.php?family=Meteoriaceae'),
		array('title' => 'IPNI Mniaceae', 'url' => 'ipni.php?family=Mniaceae'),
		array('title' => 'IPNI Monimiaceae', 'url' => 'ipni.php?family=Monimiaceae'),
		array('title' => 'IPNI Moraceae', 'url' => 'ipni.php?family=Moraceae'),
		array('title' => 'IPNI Myristicaceae', 'url' => 'ipni.php?family=Myristicaceae'),
		array('title' => 'IPNI Myrsinaceae', 'url' => 'ipni.php?family=Myrsinaceae'),
		array('title' => 'IPNI Myrtaceae', 'url' => 'ipni.php?family=Myrtaceae'),
		array('title' => 'IPNI Neckeraceae', 'url' => 'ipni.php?family=Neckeraceae'),
		array('title' => 'IPNI Nyctaginaceae', 'url' => 'ipni.php?family=Nyctaginaceae'),
		array('title' => 'IPNI Ochnaceae', 'url' => 'ipni.php?family=Ochnaceae'),
		array('title' => 'IPNI Olacaceae', 'url' => 'ipni.php?family=Olacaceae'),
		array('title' => 'IPNI Oleaceae', 'url' => 'ipni.php?family=Oleaceae'),
		array('title' => 'IPNI Onagraceae', 'url' => 'ipni.php?family=Onagraceae'),
		array('title' => 'IPNI Orchidaceae', 'url' => 'ipni.php?family=Orchidaceae'),
		array('title' => 'IPNI Orobanchaceae', 'url' => 'ipni.php?family=Orobanchaceae'),
		array('title' => 'IPNI Orthotrichaceae', 'url' => 'ipni.php?family=Orthotrichaceae'),
		array('title' => 'IPNI Oxalidaceae', 'url' => 'ipni.php?family=Oxalidaceae'),
		array('title' => 'IPNI Papaveraceae', 'url' => 'ipni.php?family=Papaveraceae'),
		array('title' => 'IPNI Passifloraceae', 'url' => 'ipni.php?family=Passifloraceae'),
		array('title' => 'IPNI Pinaceae', 'url' => 'ipni.php?family=Pinaceae'),
		array('title' => 'IPNI Piperaceae', 'url' => 'ipni.php?family=Piperaceae'),
		array('title' => 'IPNI Pittosporaceae', 'url' => 'ipni.php?family=Pittosporaceae'),
		array('title' => 'IPNI Plagiochilaceae', 'url' => 'ipni.php?family=Plagiochilaceae'),
		array('title' => 'IPNI Plagiotheciaceae', 'url' => 'ipni.php?family=Plagiotheciaceae'),
		array('title' => 'IPNI Plantaginaceae', 'url' => 'ipni.php?family=Plantaginaceae'),
		array('title' => 'IPNI Plumbaginaceae', 'url' => 'ipni.php?family=Plumbaginaceae'),
		array('title' => 'IPNI Poaceae', 'url' => 'ipni.php?family=Poaceae'),
		array('title' => 'IPNI Podocarpaceae', 'url' => 'ipni.php?family=Podocarpaceae'),
		array('title' => 'IPNI Podostemaceae', 'url' => 'ipni.php?family=Podostemaceae'),
		array('title' => 'IPNI Polemoniaceae', 'url' => 'ipni.php?family=Polemoniaceae'),
		array('title' => 'IPNI Polygalaceae', 'url' => 'ipni.php?family=Polygalaceae'),
		array('title' => 'IPNI Polygonaceae', 'url' => 'ipni.php?family=Polygonaceae'),
		array('title' => 'IPNI Polypodiaceae', 'url' => 'ipni.php?family=Polypodiaceae'),
		array('title' => 'IPNI Polytrichaceae', 'url' => 'ipni.php?family=Polytrichaceae'),
		array('title' => 'IPNI Portulacaceae', 'url' => 'ipni.php?family=Portulacaceae'),
		array('title' => 'IPNI Potamogetonaceae', 'url' => 'ipni.php?family=Potamogetonaceae'),
		array('title' => 'IPNI Pottiaceae', 'url' => 'ipni.php?family=Pottiaceae'),
		array('title' => 'IPNI Primulaceae', 'url' => 'ipni.php?family=Primulaceae'),
		array('title' => 'IPNI Proteaceae', 'url' => 'ipni.php?family=Proteaceae'),
		array('title' => 'IPNI Pteridaceae', 'url' => 'ipni.php?family=Pteridaceae'),
		array('title' => 'IPNI Pterobryaceae', 'url' => 'ipni.php?family=Pterobryaceae'),
		array('title' => 'IPNI Ranunculaceae', 'url' => 'ipni.php?family=Ranunculaceae'),
		array('title' => 'IPNI Restionaceae', 'url' => 'ipni.php?family=Restionaceae'),
		array('title' => 'IPNI Rhamnaceae', 'url' => 'ipni.php?family=Rhamnaceae'),
		array('title' => 'IPNI Rosaceae', 'url' => 'ipni.php?family=Rosaceae'),
		array('title' => 'IPNI Rubiaceae', 'url' => 'ipni.php?family=Rubiaceae'),
		array('title' => 'IPNI Rutaceae', 'url' => 'ipni.php?family=Rutaceae'),
		array('title' => 'IPNI Salicaceae', 'url' => 'ipni.php?family=Salicaceae'),
		array('title' => 'IPNI Santalaceae', 'url' => 'ipni.php?family=Santalaceae'),
		array('title' => 'IPNI Sapindaceae', 'url' => 'ipni.php?family=Sapindaceae'),
		array('title' => 'IPNI Sapotaceae', 'url' => 'ipni.php?family=Sapotaceae'),
		array('title' => 'IPNI Saxifragaceae', 'url' => 'ipni.php?family=Saxifragaceae'),
		array('title' => 'IPNI Scrophulariaceae', 'url' => 'ipni.php?family=Scrophulariaceae'),
		array('title' => 'IPNI Selaginellaceae', 'url' => 'ipni.php?family=Selaginellaceae'),
		array('title' => 'IPNI Sematophyllaceae', 'url' => 'ipni.php?family=Sematophyllaceae'),
		array('title' => 'IPNI Simaroubaceae', 'url' => 'ipni.php?family=Simaroubaceae'),
		array('title' => 'IPNI Solanaceae', 'url' => 'ipni.php?family=Solanaceae'),
		array('title' => 'IPNI Sphagnaceae', 'url' => 'ipni.php?family=Sphagnaceae'),
		array('title' => 'IPNI Sterculiaceae', 'url' => 'ipni.php?family=Sterculiaceae'),
		array('title' => 'IPNI Styracaceae', 'url' => 'ipni.php?family=Styracaceae'),
		array('title' => 'IPNI Symplocaceae', 'url' => 'ipni.php?family=Symplocaceae'),
		array('title' => 'IPNI Theaceae', 'url' => 'ipni.php?family=Theaceae'),
		array('title' => 'IPNI Thelypteridaceae', 'url' => 'ipni.php?family=Thelypteridaceae'),
		array('title' => 'IPNI Thuidiaceae', 'url' => 'ipni.php?family=Thuidiaceae'),
		array('title' => 'IPNI Thymelaeaceae', 'url' => 'ipni.php?family=Thymelaeaceae'),
		array('title' => 'IPNI Tiliaceae', 'url' => 'ipni.php?family=Tiliaceae'),
		array('title' => 'IPNI Ulmaceae', 'url' => 'ipni.php?family=Ulmaceae'),
		array('title' => 'IPNI Urticaceae', 'url' => 'ipni.php?family=Urticaceae'),
		array('title' => 'IPNI Valerianaceae', 'url' => 'ipni.php?family=Valerianaceae'),
		array('title' => 'IPNI Velloziaceae', 'url' => 'ipni.php?family=Velloziaceae'),
		array('title' => 'IPNI Verbenaceae', 'url' => 'ipni.php?family=Verbenaceae'),
		array('title' => 'IPNI Violaceae', 'url' => 'ipni.php?family=Violaceae'),
		array('title' => 'IPNI Viscaceae', 'url' => 'ipni.php?family=Viscaceae'),
		array('title' => 'IPNI Vitaceae', 'url' => 'ipni.php?family=Vitaceae'),
		array('title' => 'IPNI Xyridaceae', 'url' => 'ipni.php?family=Xyridaceae'),
		array('title' => 'IPNI Zingiberaceae', 'url' => 'ipni.php?family=Zingiberaceae'),
		array('title' => 'IPNI Zygophyllaceae', 'url' => 'ipni.php?family=Zygophyllaceae')	
		),
		
		
	'Journals (provided by publisher)' => array(
		// IPNI
		array('title' => 'Molecular Phylogenetics and Evolution', 'url' => 'http://rss.sciencedirect.com/publication/science/6963'),
		)
		
	);

switch ($format)
{
	case 'opml':
		// header
		$doc = new DomDocument('1.0');
		$opml = $doc->createElement('opml');
		$opml->setAttribute('version', '1.0');
		$opml = $doc->appendChild($opml);

		// head
		$head = $opml->appendChild($doc->createElement('head'));

		// title
		$title = $head->appendChild($doc->createElement('title'));
		$title->appendChild($doc->createTextNode('bioGUID RSS feeds'));
		
		// body
		$body = $opml->appendChild($doc->createElement('body'));
	
		foreach ($feeds as $category => $list)
		{
			$outline = $body->appendChild($doc->createElement('outline'));
			$outline->setAttribute('title', $category);	
			$outline->setAttribute('text', $category);	
			
			foreach ($list as $item)
			{
			
				$feed = $outline->appendChild($doc->createElement('outline'));
				$feed->setAttribute('type', 'atom');
			
				foreach ($item as $k => $v)
				{
					switch ($k)
					{
						case 'title':
							$feed->setAttribute('title', $v);
							$feed->setAttribute('text', $v);
							break;
						case 'url':
							if (preg_match('/^http:/', $v))
							{
								$feed->setAttribute('xmlUrl', $v);
							}
							else
							{								
								$feed->setAttribute('xmlUrl', $feed_prefix . $v);
							}
							break;
						default:
							break;
					}
				}
			}
		}
		
		header("Content-type: text/xml");
		echo $doc->saveXML();
		
		break;
		
	default:
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<link rel="subscriptions" type="text/x-opml" title="bioGUID RSS feeds"
 ref="?format=opml" />

  <title>bioGUID RSS feeds</title>
  
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
  <p><a href="../">Home</a></p>

  <h1>Feeds</h1>
  <p>RSS feeds for sites (databases and journals) that don't have them (yet).</p>
  <p><a href="?format=opml"><img src="images/opml-icon-32x32.png" border="0"/></a> <a href="?format=opml">OPML listing of feeds</a></p>

<?
	
		foreach ($feeds as $category => $list)
		{
			echo "<h2>$category</h2>\n";
			echo "<ul>\n";
			
			foreach ($list as $item)
			{
				$url_text = '';
				$title_text = '';
				foreach ($item as $k => $v)
				{
					switch ($k)
					{
						case 'title':
							$title_text = $v;
							break;
						case 'url':
							if (preg_match('/^http:/', $v))
							{
								$url_text = '<a href="' . $v . '">';
							}
							else
							{								
								$url_text = '<a href="' . $feed_prefix . $v . '">';
							}
							break;
							break;
						default:
							break;
					}
				}
				
				echo '<li>' . $url_text . $title_text . '</a></li>';
			}
			echo "</ul>\n";
			
		}
		echo '</body>';
		echo '</html>';
	
	
		break;
}

?>