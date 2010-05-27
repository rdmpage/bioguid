
function lookahead(uri)
{
	element = $("horizon");
			
	var elementDims = $("progress").getDimensions();
	var viewPort = document.viewport.getDimensions();
	var offsets = document.viewport.getScrollOffsets();
	var centerY = viewPort.height / 2 + offsets.top - elementDims.height / 2;
	element.setStyle( { position: 'absolute', top: Math.floor(centerY) + 'px' } );
	

	$("progress").show(); 
	
	var loading	= function(t){lookaheadLoading();}
	var failure	= function(t){lookaheadFailure();}
	var success	= function(t){lookaheadSuccess(t);}

	var url = "lookahead.php";
	var pars = "uri=" + encodeURI(uri);
	var myAjax = new Ajax.Request(url, {method:"get", parameters:pars, onLoading: loading, onFailure: failure, onSuccess:success});
}

function lookaheadLoading()
{
	$("progress").innerHTML ='<img src="images/12740500515.gif" \/><br />Resolving';
}

function lookaheadFailure()
{
	$("progress").innerHTML ="×<br />Failed";
	setTimeout('$("progress").hide()', 500);
}

function lookaheadSuccess (t)
{
	var s = t.responseText.evalJSON();
	if (s.ntriples > 0)
	{
		// we\'ve loaded URI, so go see
		$("progress").innerHTML ="✓<br />Found";
		setTimeout('$("progress").hide()', 500);
		window.location=gWebRoot + 'uri/' + s.uri;
	}
	else
	{
		$("progress").innerHTML ="No triples found";
		setTimeout('$("progress").hide()', 500);
	}
}