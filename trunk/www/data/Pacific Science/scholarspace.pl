#!/usr/bin/perl
#
# OAI Harvester
#
use strict;
use warnings;

use Biblio::Citation::Parser::Standard;
use Biblio::Citation::Parser::Utils;
use Business::ISSN;
use HTML::Entities ();
use LWP;
use URI;
use XML::XPath;
use utf8;

#---------------------------------------------------------------------------------------------------
use DBI;

# To use this you need the details for connecting to the database,
# and the id of the user importing the files (I've been lazy here).
# setting $user_id to 1 is probably safe, as it assumes that the
# person doing a bulk import is the administrator of the database.

my $cstr = "DBI:mysql:plan9"; 	# Database connect string, e.g. DBI:mysql:mydatabase
my $user = "root";				# Database user name
my $pass = "";			# Database password
# 
my $dbh = DBI->connect($cstr, $user, $pass);
$dbh || &error("DBI connect failed : ",$dbh->errstr);


my $browser = LWP::UserAgent->new;

# Proxy if needed
#$browser->proxy(http  => 'http://wwwcache.gla.ac.uk:8080');

my $cit_parser = new Biblio::Citation::Parser::Standard;


my @sets = ('hdl_10125_371','hdl_10125_382','hdl_10125_385','hdl_10125_387','hdl_10125_389','hdl_10125_390','hdl_10125_392','hdl_10125_824','hdl_10125_825','hdl_10125_827','hdl_10125_828','hdl_10125_857','hdl_10125_914','hdl_10125_915','hdl_10125_916','hdl_10125_957','hdl_10125_958','hdl_10125_959','hdl_10125_960','hdl_10125_1037','hdl_10125_1038','hdl_10125_1039','hdl_10125_1040','hdl_10125_1042','hdl_10125_1043','hdl_10125_1044','hdl_10125_1045','hdl_10125_1047','hdl_10125_1048','hdl_10125_1049','hdl_10125_1050','hdl_10125_1052','hdl_10125_1053','hdl_10125_1054','hdl_10125_1055','hdl_10125_491','hdl_10125_492','hdl_10125_493','hdl_10125_494','hdl_10125_366','hdl_10125_367','hdl_10125_428','hdl_10125_429','hdl_10125_496','hdl_10125_497','hdl_10125_498','hdl_10125_499','hdl_10125_635','hdl_10125_636','hdl_10125_637','hdl_10125_639','hdl_10125_431','hdl_10125_432','hdl_10125_433','hdl_10125_434','hdl_10125_962','hdl_10125_969','hdl_10125_972','hdl_10125_973','hdl_10125_975','hdl_10125_976','hdl_10125_977','hdl_10125_978','hdl_10125_980','hdl_10125_981','hdl_10125_982','hdl_10125_983','hdl_10125_455','hdl_10125_456','hdl_10125_457','hdl_10125_458','hdl_10125_625','hdl_10125_626','hdl_10125_627','hdl_10125_628','hdl_10125_1086','hdl_10125_1087','hdl_10125_1088','hdl_10125_1089','hdl_10125_1091','hdl_10125_1092','hdl_10125_1093','hdl_10125_1094','hdl_10125_1116','hdl_10125_1117','hdl_10125_1118','hdl_10125_1119','hdl_10125_1121','hdl_10125_1122','hdl_10125_1123','hdl_10125_1124','hdl_10125_1126','hdl_10125_1127','hdl_10125_1128','hdl_10125_1129','hdl_10125_1131','hdl_10125_1132','hdl_10125_1133','hdl_10125_1134','hdl_10125_630','hdl_10125_631','hdl_10125_632','hdl_10125_633','hdl_10125_451','hdl_10125_452','hdl_10125_453','hdl_10125_454','hdl_10125_2369','hdl_10125_2370','hdl_10125_2371','hdl_10125_2372','hdl_10125_2384','hdl_10125_2385','hdl_10125_2386','hdl_10125_2387','hdl_10125_2389','hdl_10125_2390','hdl_10125_2391','hdl_10125_2392','hdl_10125_2394','hdl_10125_2395','hdl_10125_2396','hdl_10125_2397','hdl_10125_620','hdl_10125_621','hdl_10125_622','hdl_10125_623','hdl_10125_2399','hdl_10125_2400','hdl_10125_2401','hdl_10125_2402');

foreach my $set (@sets)
{



#---------------------------------------------------------------------------------------------------
# Construct URL

my $metadata_prefix = 'oai_dc'; # For DSpace and most OAI


# DSpace repository
my $repository = 'http://scholarspace.manoa.hawaii.edu/dspace-oai/';
my $baseUrl = $repository;
#my $set = 'hdl_10125_371';


# DSpace respositories have a standard query
$baseUrl .=  'request?verb=ListRecords';

my $doi_lookup = 0;


#---------------------------------------------------------------------------------------------------
my $safe_filename_characters = "a-zA-Z0-9_.-";
my $baseOutputName = $set;
$baseOutputName =~ tr/ /_/;
$baseOutputName =~ s/[^$safe_filename_characters]//g;


# LINK file
open( LINKFILE, "> $baseOutputName.txt") or die "Can't open LINKS file : $!";


# RIS file
open( RISFILE, "> $baseOutputName.ris") or die "Can't open RIS file : $!";


#---------------------------------------------------------------------------------------------------
# Get date we last accessed this repository
my $last_accessed = '';

my $sql = 'SELECT * FROM oai WHERE '
	. '(repository = ' . $dbh->quote($repository) . ') ';
	
if ($set ne '')
{
	$sql .= ' AND (repository_set = ' . $dbh->quote($set) .')';
}
$sql .= ' LIMIT 1';

print $sql, "\n";
my $sth = $dbh->prepare($sql)
	or die "can't prepare $sql: $dbh->errstr\n";
	
my $rv = $sth->execute
	or die "can't execute the query $sth->errstr\n";
	
while(my @row = $sth->fetchrow_array)
{
	$last_accessed = $row[2];
}

#---------------------------------------------------------------------------------------------------
# Harvest records
my $call_count = 0;







my $resumptionToken = '';

do {

	my $url = $baseUrl;

	# If no resumption toekn we need to specify metadata prefix
	if ($resumptionToken eq '')
	{
		$url .= '&metadataPrefix=' . $metadata_prefix;
		if ($set ne '')
		{
			if ($set ne 'naturalis')
			{
				$url .= '&set=' . $set;
			}
		}
	}
	else
	{
		$url .= '&resumptionToken=' . $resumptionToken;
	}
	
	# If we've previously accessed this repository then limit search to newly added/modified records
	if ($last_accessed ne '')
	{
		$last_accessed =~ s/ /T/g;
		$url .= '&from=' . $last_accessed;
	}
	
	# Debugging
	print $url;
		
	# Make the call
	my $response = $browser->get ($url);
	my $xml = $response->content;
	
	# Clean new lines
	$xml =~ s/&#xD;/ /gm;
	$xml =~ s/&#13;/ /gm;

	# XML file	
	my $dirName = $set;
	$dirName =~ tr/ /_/;
	$dirName =~ s/[^$safe_filename_characters]//g;
	mkdir ($dirName, 0777);
	
	my $xml_file_name .= "$call_count";
	$xml_file_name =~ tr/ /_/;
	$xml_file_name =~ s/[^$safe_filename_characters]//g;
	open( XMLFILE, "> $dirName/$xml_file_name.xml") or die "Can't open XML file : $!";
	print XMLFILE $xml, "\n";
	close XMLFILE;	

	# XPath
	my $xp = XML::XPath->new($xml);

	# Extract resumption token
	my $nodeset = $xp->find('//resumptionToken');
	foreach my $node ($nodeset->get_nodelist)
	{
		$resumptionToken= $node->string_value;
		print "\nResumption Token = ", $resumptionToken, "\n";		
	}	
	
	# Process individual records	
	my $recordTag = '//record';	
	
	$nodeset = $xp->find($recordTag);
	foreach my $node ($nodeset->get_nodelist)
	{
		my @authorList  = ();
		my @keywords    = ();
		
		my $id 			= '';
		my $aufirst 	= '';
		my $aulast 		= '';
		my $title 		= '';
		my $handle 		= '';
		my $journal		= 'Pacific Science';
		my $issn 		= '0030-8870';
		my $year 		= '';
		my $date 		= '';
		my $spage 		= '';
		my $epage 		= '';
		my $volume 		= '';
		my $issue 		= '';
		my $citation 	= '';
		my $url 		= '';
		my $abstract 	= '';
		my $doi 		= '';
		my $fulltext	= '';
		
		#-------------------------------------------------------------------------------------------
		# Local OAI record identifier
		my $ns = $xp->find('header/identifier', $node);
		foreach my $n ($ns->get_nodelist)
		{
			$id = $n->string_value;
		}
			
		#-------------------------------------------------------------------------------------------
		# First author (AULAST)
		$ns = $xp->find('metadata/oai_dc:dc/dc:creator[1]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			my $creator = $n->string_value;
			if ($creator =~ m/^([A-Za-z]+),/)
			{
				$aulast = $1;
				$creator =~ s/^([A-Za-z]+),\s//g;
				$aufirst = $creator;
			}
		}
		
		#-------------------------------------------------------------------------------------------
		# Authors
		$ns = $xp->find('metadata/oai_dc:dc/dc:creator', $node);
		foreach my $n ($ns->get_nodelist)
		{
			my $creator = $n->string_value;
			
			# Clean up crap
			$creator =~ s/, [0-9]{4}-([0-9]{4})?//g;
			$creator =~ s/. \(\w*( \w*)*\)/./g;
			
			if ($set eq 'hdl_2324_25') # Esakia
			{
				# Esakia has English, Kana, and an alternative version of the author name
				if ($creator =~ m/^[A-Z]/)
				{
					push (@authorList ,$creator);
				}

			}
			else
			{
				push (@authorList ,$creator);
			}
		}
		
		#-------------------------------------------------------------------------------------------
		# Title
		$ns = $xp->find('metadata/oai_dc:dc/dc:title[1]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			$title = $n->string_value;
			
			# Clean up crap
			$title =~ s/\.$//g;
			$title =~ s/\n//g;
			$title =~ s/(\s+)/ /g;

		}

		#-------------------------------------------------------------------------------------------
		# Identifier (such as a handle, but may be all sorts of crap...)
		$ns = $xp->find('metadata/oai_dc:dc/dc:identifier[1]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			my $identifier = $n->string_value;	
			if ($identifier =~ m/http:\/\/hdl.handle.net\//)
			{
				$handle = $identifier;
				$handle =~ s/http:\/\/hdl.handle.net\///g;
			}
			if ($identifier =~ m/^http:\/\//)
			{
				$url = $identifier;
			}
			else
			{
				$citation = $identifier;
				
				# parse
				if ($citation =~ m/Pac Sci (\d+)\((\d+)\): (\d+)(-(\d+))/)
				{
					$volume = $1;
					$issue = $2;
					$spage = $3;
					$epage = $5;
				}
				
				
			}
		}
		
		
		#-------------------------------------------------------------------------------------------
		# Esakia
		$ns = $xp->find('metadata/oai_dc:dc/dc:identifier[5]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			if ($set eq 'hdl_2324_25') # Esakia
			{
				$volume = $n->string_value;	
			}
		}
		$ns = $xp->find('metadata/oai_dc:dc/dc:identifier[6]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			if ($set eq 'hdl_2324_25') # Esakia
			{
				$spage = $n->string_value;	
			}
		}
		$ns = $xp->find('metadata/oai_dc:dc/dc:identifier[7]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			if ($set eq 'hdl_2324_25') # Esakia
			{
				$epage = $n->string_value;	
			}
		}
		$ns = $xp->find('metadata/oai_dc:dc/dc:description', $node);
		foreach my $n ($ns->get_nodelist)
		{
			if ($set eq 'hdl_2324_25') # Esakia
			{
				$abstract = $n->string_value;	
				$abstract =~ s/\n/ /g;
			}
		}
		

		#-------------------------------------------------------------------------------------------
		# Identifier (such as a handle, but may be all sorts of crap...)
		$ns = $xp->find('metadata/oai_dc:dc/dc:identifier[2]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			my $identifier = $n->string_value;
			
			# Might be an ISSN
			my $issn_object = new Business::ISSN($identifier);
			if ($issn_object)
			{
				if ($issn_object->is_valid)
				{
					$issn = $issn_object->as_string;
					
					if ($issn eq '0071-1268')
					{
						$journal = 'Esakia';
					}
				}
			}
			
			# Might be a Handle
			if ($identifier =~ m/http:\/\/hdl.handle.net\//)
			{
				$handle = $identifier;
				$handle =~ s/http:\/\/hdl.handle.net\///g;
			}
			
			# Might be an OpenURL 
			if ($identifier =~ m/http:\/\/www.doaj.org\//)
			{
				my $openurl = $identifier;
				$openurl =~ s/http:\/\/www.doaj.org\/doaj\?func=openurl&//g;
				my @parts = split(/&/, $openurl);
				foreach my $part (@parts)
				{
					#print $part, "\n";
					my ($key, $value) = split(/=/, $part);
					#print $key, '-', $value, "\n";
					
					if ($key eq 'spage')
					{
						$spage = $value;
					}
					if ($key eq 'volume')
					{
						$volume = $value;
					}
					if ($key eq 'issue')
					{
						$issue = $value;
					}
					if ($key eq 'date')
					{
						$year = $value;
					}
					if ($key eq 'issn')
					{
						$issn = $value;
						# Ensure it is in nnnn-nnnn format
						if ($issn =~ m/[0-9]{7}([0-9]|X)/i)
						{
							$issn =~ s/([0-9]{4})([0-9]{3}([0-9]|X))/$1-$2/i;
						}
					}
				}
			}
		}
		
		#-------------------------------------------------------------------------------------------
		# Identifier (such as a handle, but may be all sorts of crap...)
		$ns = $xp->find('metadata/oai_dc:dc/dc:identifier[3]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			my $identifier = $n->string_value;	
			if ($identifier =~ m/http:\/\/hdl.handle.net\//)
			{
				$handle = $identifier;
				$handle =~ s/http:\/\/hdl.handle.net\///g;
			}
		}

		#-------------------------------------------------------------------------------------------
		$ns = $xp->find('metadata/oai_dc:dc/dc:relation[1]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			my $relation = $n->string_value;			
			if ($relation =~ m/Miscellaneous Publications/)
			{
				$issn = '0076-8405';
				$journal = 'Miscellaneous Publications Museum of Zoology University of Michigan';
			}
			if ($relation =~ m/Occasional Papers/)
			{
				$issn = '0076-8413';
				$journal = 'Occasional Papers of the Museum of Zoology University of Michigan';
			}
		}
		#-------------------------------------------------------------------------------------------
		$ns = $xp->find('metadata/oai_dc:dc/dc:relation[2]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			if ($baseUrl =~ m/deepblue.lib.umich.edu/)
			{
				$volume = $n->string_value;			
			}
		}

		#-------------------------------------------------------------------------------------------
		$ns = $xp->find('metadata/oai_dc:dc/dc:date[3]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			$year = $n->string_value;	
			$year =~ s/([0-9]{4})\-[0-9]{2}/$1/g;
			$date = $n->string_value . "-00";
		}

		#-------------------------------------------------------------------------------------------
		$ns = $xp->find('metadata/oai_dc:dc/dc:source[1]', $node);
		foreach my $n ($ns->get_nodelist)
		{
			$journal = $n->string_value;	
		}
			
		#-------------------------------------------------------------------------------------------
		$ns = $xp->find('metadata/oai_dc:dc/dc:description', $node);
		foreach my $n ($ns->get_nodelist)
		{
			$abstract = $n->string_value;	
			$abstract =~ s/ABSTRACT: //g;
			$abstract =~ s/\n/ /g;
			$abstract =~ s/\r/ /g;
			$abstract =~ s/\s\s+/\s/g;
		}


		# Post process
 		if ($citation ne '')
		{
			# Due to limitations of Dublin Core, the bibliogrpahic details (paginations, etc.) may
			# be expressed as text string, which we need to parse.
			
			
			# Clean up
			$citation =~ s/\.$//g;
			print "Parsing citation...\n";
			my $metadata = $cit_parser->parse($citation);
			
			if ($metadata->{match} ne '') 
			{
				$journal = $metadata->{title};
				
				# Cleanup journal
				# SI has hyphens in some journal names (sigh).
				
				$journal =~ s/,$//g;
				
				if ($journal ne 'Amphibia-Reptilia')
				{
					$journal =~ s/\-/ /g;
				}

 				$volume = $metadata->{volume};
 				$issue = $metadata->{issue};
 				$spage = $metadata->{spage};
 				$epage = $metadata->{epage};
# 				
# 				# Local ISSN lookup				
# 				my $sql = 'SELECT * FROM issn WHERE (title = ' . $dbh->quote($journal) . ') LIMIT 1';				
# 				my $sth = $dbh->prepare($sql)
# 					or die "Can't prepare $sql: $dbh->errstr\n";			
# 				my $rv = $sth->execute
# 					or die "can't execute the query: $sth->errstr\n";
# 					
# 				#print $sql, "\n";
# 
# 				if ($sth->rows == 1)
# 				{
# 					($journal, $issn) = $sth->fetchrow_array;
# 				}


				# DOI lookup
				if ($doi_lookup)
				{
					my $query = 'http://www.crossref.org/openurl?';
					$query .=  "pid=ourl_rdmpage:peacrab";
					$query .= "genre=article";
					$query .= "&spage=" . $spage;
					$query .= "&volume=" . $volume;
					$query .= "&title=" . $journal;
					$query .= "&noredirect=true";
			
					print $query, "\n";
					
					my $response = $browser->get ($query);
					my $crossref_xml = $response->content;
					
					#print $xml;
					
					# If we got a result, extract DOI
					
					if ($crossref_xml =~ /<\?xml version = "1.0" encoding = "UTF-8"\?>/i)
					{
						# Examine XML result using XPath queries
						my $xp = XML::XPath->new($crossref_xml);
					
						my $nodeset = $xp->find('//crossref_result/query_result/body/query');
					
						foreach my $node ($nodeset->get_nodelist)
						{
							$doi= $node->find('doi');
							print $doi, "\n";
							
						}
					}
				}


			}
		}

		
		# Dump
		print "----------------Dump---------------\n";
		print $title, "\n";
		foreach my $a (@authorList)
		{	
			$a =~ s/^\s*//g;
			$a =~ s/\s*$//g;
			print "-", $a, "\n";
		}
		print "handle=$handle\n";
		print "title=$title\n";
		print "issn=$issn\n";
		print "journal=$journal\n";
		print "year=$year\n";
		print "spage=$spage\n";
		print "volume=$volume\n";
		print "citation=$citation\n";
		print "doi=$doi\n";
		print "url=$url\n";
		
		#-------------------------------------------------------------------------------------------
		# Full text				
		if ($issn eq '0076-8405')
		{		
			$fulltext = sprintf("http://deepblue.lib.umich.edu/bitstream/%s/1/MP%03d.pdf", $handle, $volume);
		}
		if ($issn eq '0076-8413')
		{		
			$fulltext = sprintf("http://deepblue.lib.umich.edu/bitstream/%s/1/OP%03d.pdf", $handle, $volume);
		}
		print "fulltext=", $fulltext, "\n";
		print "-----------------------------------\n";
		
		#-------------------------------------------------------------------------------------------
		# RIS
		# Export in RIS format for inport into bibliographic databases
		binmode RISFILE, ":encoding(utf8)";
		
		print RISFILE "TY  - JOUR\n";
#		print RISFILE "Y1  - $year\n";
		print RISFILE "Y1  - $date\n";
		foreach my $a (@authorList)
		{
			$a =~ s/^\s*//g;
			$a =~ s/\s*$//g;
			print RISFILE "AU  - $a\n";
		}
		print RISFILE "ID  - $id\n";
		print RISFILE "T1  - $title\n";
		print RISFILE "JO  - $journal\n";
		print RISFILE "SN  - $issn\n";
		print RISFILE "VL  - $volume\n";
		print RISFILE "IS  - $issue\n" if ($issue);
		if ($spage)
		{
			print RISFILE "SP  - $spage\n";
		}
		else
		{
			print RISFILE "SP  - 1\n";
		}
		print RISFILE "EP  - $epage\n" if ($epage);
		print RISFILE "N2  - $abstract\n" if ($abstract);
		
		foreach my $k (@keywords)
		{
			print RISFILE "KW  - $k\n";
		}		
		
		print RISFILE "M3  - doi:$doi\n" if ($doi);
		
		# Store the citation as it might be useful for debugging
		print RISFILE "M1  - $citation\n" if ($citation);
		
		if ($handle)
		{
			print RISFILE "UR  - http://hdl.handle.net/$handle\n";
		}
		else
		{
			print RISFILE "UR  - $url\n" if ($url);
		}
		
		print RISFILE "L2  - $fulltext\n" if ($fulltext);		
		print RISFILE "AV  - Open access\n";
		print RISFILE "ER  - \n\n";
		
		
		#-------------------------------------------------------------------------------------------
		# Dump for OpenURL lookup
		
		$year =~ s/\/[0-9]{2}\/[0-9]{2}//g;
		
		print   LINKFILE "$issn\t$journal\t$volume\t$issue\t$spage\t$year\t$title\t$aulast\t$aufirst\t$handle\n";
		
		
		
		
	}
		
	$call_count++;
	

} while ($resumptionToken ne '');



#---------------------------------------------------------------------------------------------------
# Store last time accessed
if ($last_accessed eq '')
{
	# We don't have this repository
	$sql = 'INSERT INTO oai (repository, repository_set, accessed) VALUES('
		. $dbh->quote($repository) . ','
		. $dbh->quote($set) . ','
		. 'NOW()'
		. ')';
	
	print $sql, "\n";
	my $sth = $dbh->prepare($sql)
		or die "can't prepare $sql: $dbh->errstr\n";
		
	my $rv = $sth->execute
		or die "can't execute the query $sth->errstr\n";
}
else
{
	# Update 
	$sql = 'UPDATE oai SET '
		. 'accessed = NOW() '
		. 'WHERE (repository = ' . $dbh->quote($repository) . ')'
		. ' AND (repository_set = ' . $dbh->quote($set) . ')';

	print $sql, "\n";
	my $sth = $dbh->prepare($sql)
		or die "can't prepare $sql: $dbh->errstr\n";
		
	my $rv = $sth->execute
		or die "can't execute the query $sth->errstr\n";

}



close LINKFILE;
close RISFILE;

}

$dbh->disconnect;

