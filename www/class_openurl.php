<?php

// Basic OpenURL objects

require_once('identifier.php');

//--------------------------------------------------------------------------------------------------
class opObject
{
	var $id = array();
	var $values = array();
	var $version;
	
	function opObject()
	{
		$this->version = 0.1;
	}
	
	function GetParameters($parameters)
	{
	}
}

//--------------------------------------------------------------------------------------------------
// This is the entity we want to resolve 
// "what"
class opReferent extends opObject
{
	function opReferent()
	{
	}
	
	function Dump()
	{
		echo "<h3>Referent (&quot;what&quot;)</h3>";
		echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
		echo "Identifiers\n";
		print_r($this->id);
		echo "Key-value pairs\n";
		print_r($this->values);
		echo "</pre>";
	}
	
	function StoreIdentifier($id)
	{
		$i = IdentifierKind($id);
		switch($i['identifier_type'])
		{
			case IDENTIFIER_DOI:
				$this->id['doi'] = $i['identifier_string'];
				break;

			case IDENTIFIER_SICI:
				$this->id['sici'] = $i['identifier_string'];
				break;

			case IDENTIFIER_HANDLE:
				$this->id['hdl'] = $i['identifier_string'];
				break;
				
			case IDENTIFIER_PUBMED:
				$this->id['pmid'] = $i['identifier_string'];
				break;

			case IDENTIFIER_URL:
				$this->id['url'] = $i['identifier_string'];
				break;

			case IDENTIFIER_GENBANK:
				$this->id['genbank'] = $i['identifier_string'];
				break;

			case IDENTIFIER_GI:
				$this->id['gi'] = $i['identifier_string'];
				break;


			default:
				break;
		}
	}		
	

	function GetParameters($parameters)
	{
		//print_r($parameters);
		foreach ($parameters as $k => $v)
		{
			switch ($k)
			{
				case 'submit':
					break;
					
				case 'rft_id':
					$this->StoreIdentifier($v);
					break;
	
				default:
					if (preg_match('/^rft_/', $k))
					{
						switch ($k)
						{
							case 'rft_val_fmt':
								switch ($v)
								{
									case 'info:ofi/fmt:kev:mtx:journal':
										$this->values['genre'] = 'article';
										break;
									default:
										$this->values['genre'] = 'article';
										break;
								}
								break;
								
							case 'rft_jtitle':
								$this->values['title'] = trim($v);
								break;
							
							
							default:
								$key = $k;
								$key = str_replace("rft_", '', $key);
								$this->values[$key] = trim($v);				
								break;
						}
					} 
					else if (preg_match('/^rfr/', $k))
					{
						// eat referrer info
					}					
					else
					{
						if ($this->version == 0.1)
						{
							$key = '';
							switch ($k)
							{
								case 'title':
									if (isset($parameters['genre']))
									{
										if ($parameters['genre'] == 'book')
										{
											$key = 'btitle';
										}
										if ($parameters['genre'] == 'article')
										{
											$key = 'title';
										}
										if ($parameters['genre'] == 'journal')
											{
											$key = 'title';
										}
									}
									else
									{
										// assume it's an article
										$key = 'title';
										$this->values['genre'] = 'article';
									}
									break;
									
								case 'id':
									$this->StoreIdentifier($v);
									break;
									
								case 'vol':
									$key = 'volume'; // liberal input (catch case of vol=)
									break;
		
								default:
									$key = $k;
									break;
							}
							if ($key != '')
							{
								$this->values[$key] = trim($v);
							}
						}
					}
					break;
			}
		}
		
		// Clean
		
		// Endnote may have leading "-" as it splits spage-epage to generate OpenURL
		if (isset($this->values['epage']))
		{
			$this->values['epage'] = str_replace("-", "", $this->values['epage']);
		}
		// If an article has an issue but not a volume, we will use the issue as the volume
		if (!isset($this->values['volume']))
		{
			if (isset($this->values['issue']))
			{
				$this->values['volume'] = $this->values['issue'];
				unset($this->values['issue']);
			}
		}
		
		
	}


}

//--------------------------------------------------------------------------------------------------
// This is the entity that refers to (e.g., cites) the entity we
// want to resolve (the referent, which may not itself be supplied)
// "where"
class opReferringEntity extends opObject
{
	function opReferringEntity()
	{
	}

}


	
	

?>