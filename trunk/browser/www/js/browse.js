function validate_input(browse_id)
{
	var s = $(browse_id).value;
	
	// check it's OK...
	
	var check_doi = /^(doi:)?10.(.*)/i;
	var check_pmid = /^pmid:[0-9]+$/i;
	var check_http_uri = /^http:\/\//;
	var check_lsid = /^urn:lsid:/;
	
	var uri = '';
	
	// HTTP URI
	if (uri == 0)
	{	
		if (check_http_uri.test(s))
		{
			uri = s;
		}
	}	
	
	// DOI
	if (uri == 0)
	{	
		// DOI
		if (check_doi.test(s))
		{
		}
	}
	
	// PMID
	if (uri == 0)
	{	
		if (check_pmid.test(s))
		{
			uri = 'http://bioguid.info/' + s;
		}
	}

	// LSID
	if (uri == 0)
	{	
		if (check_lsid.test(s))
		{
			uri = 'http://bioguid.info/' + s;
		}
	}
	
	// ISSN
	
	// GenBank
	
	// LSID
	
	// etc.

	if (uri == '')
	{
		$("content").show(); 
		$("content").innerHTML ="×<br />&quot;" + s + "&quot; is not a valid URI";
		setTimeout('$("content").hide()', 1000);
	}
	
	return uri;
}

function handleKeyPress(e,browse_id)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		browseUri(browse_id);
	}
}

function browseUri(browse_id)
{
	var uri = validate_input(browse_id);
	if (uri != '')
	{
		lookahead(uri);
	}
}