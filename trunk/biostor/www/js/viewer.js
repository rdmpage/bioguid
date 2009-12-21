// BHL viewer

// Current page id
var gPageID = 0;
  
//--------------------------------------------------------------------------------------------------
// Called when user clicks on thumbnail. We display the image for the corresponding
// page.
function show_page(page_image_url, PageID) 
{
	if (gPageID != PageID)
	{
		// Toggle selection in thumbnail list
		// Note that padding and borders must sum to same figure, and this matches
		// the default CSS in viewer.css
		$('thumbnail_image_' + PageID).setStyle({ border:'3px solid rgb(56,117,215)'});
		$('thumbnail_image_' + PageID).setStyle({ padding:'0px'});
		if (gPageID != 0)
		{
			$('thumbnail_image_' + gPageID).setStyle({ border:'1px solid rgb(146,146,146)'});
			$('thumbnail_image_' + gPageID).setStyle({ padding:'2px'});
		}
		gPageID = PageID;
			
		// Display page image
		$('page_image').src = page_image_url; 
		
		// Ensure thumbnail is shown (this doesn't seem to work)
		//window.location = '#';
		
		// Need function to scroll thumbnail into view...
	}
}