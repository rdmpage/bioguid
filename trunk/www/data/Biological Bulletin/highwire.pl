#!/usr/bin/perl -slw


use strict;
use warnings;

use LWP;

my $browser = LWP::UserAgent->new;

# Be nice to avoid 503 response
# Get an indication of this from robots.txt crawl-delay value
my $crawlDelay = 10;

# Journal specific
my $issn = '0002-9122';
my $root = 'http://www.amjbot.org';
my $journal_code = 'amjbot';
my $max_issues = 12;
my $start_volume= 0;
my $end_volume = 0;


# Biological Bulletin
$issn = '0006-3185';
$root = 'http://www.biolbull.org';
$journal_code = 'biolbull';
$max_issues = 6;
$start_volume= 1;
$end_volume = 208;

# URLs
my $webRoot 	= "$root/content/";
my $citationUrl = "$root/cgi/citmgr?type=refman&gca=" . $journal_code . ";";


# Fetch issues
my $v;
my $i;



#84 - 94
for ($v = $start_volume; $v <= $end_volume; $v++)
{
	#print $v, "\n";
	for ($i = 1; $i <= $max_issues; $i++)
	{
		#print $i, "\n";
		my $issueUrl = $webRoot . "vol$v/issue$i/";
		my $response = $browser->get ($issueUrl);
		
		#print "Getting web page response: ", $response->code, "\n";
		
		if ($response->code eq '200' )
		{
			my @lines = split(/\n/, $response->content);
			
			foreach my $line (@lines)
			{
				chomp $line;
				
 				if ($line =~ m/<A HREF="\/cgi\/content\/abstract\/([0-9]+\/[0-9]+\/[0-9]+)">/gi)
 				{
 					my $id = $1;
					my $url = $citationUrl . $id;
 				
					my $response = $browser->get ($url);
					#print $response->code, " ", $url, "\n";
					
					if ($response->code eq '200' )
					{			
						print $response->content;
					}

					#print "Pausing...\n";
					sleep($crawlDelay);

 				}
			}
		}
	}
}

