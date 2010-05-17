
function lookahead(uri)
{
	element = $("horizon");
			
	var elementDims = $("content").getDimensions();
	var viewPort = document.viewport.getDimensions();
	var offsets = document.viewport.getScrollOffsets();
	var centerY = viewPort.height / 2 + offsets.top - elementDims.height / 2;
	element.setStyle( { position: 'absolute', top: Math.floor(centerY) + 'px' } );
	

	$("content").show(); 
	
	
	
	var loading	= function(t){lookaheadLoading();}
	var failure	= function(t){lookaheadFailure();}
	var success	= function(t){lookaheadSuccess(t);}

	var url = "lookahead.php";
	var pars = "uri=" + encodeURI(uri);
	var myAjax = new Ajax.Request(url, {method:"get", parameters:pars, onLoading: loading, onFailure: failure, onSuccess:success});
}

function lookaheadLoading()
{
	$("content").innerHTML ='<img src="images/12740500515.gif" \/><br />Resolving';
}

function lookaheadFailure()
{
	$("content").innerHTML ="×<br />Failed";
	setTimeout('$("content").hide()', 500);
}

function lookaheadSuccess (t)
{
	var s = t.responseText.evalJSON();
	if (s.ntriples > 0)
	{
		// we\'ve loaded URI, so go see
		$("content").innerHTML ="✓<br />Found";
		setTimeout('$("content").hide()', 500);
		window.location=gWebRoot + 'uri/' + s.uri;
	}
	else
	{
		$("content").innerHTML ="No triples found";
		setTimeout('$("content").hide()', 500);

	}
}