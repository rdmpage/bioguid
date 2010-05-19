<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' 
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:dcterms='http://purl.org/dc/terms/'
  xmlns:bibo='http://purl.org/ontology/bibo/'
  xmlns:tconcept="http://rs.tdwg.org/ontology/voc/TaxonConcept#"
  xmlns:uniprot="http://purl.uniprot.org/core/"
 xmlns:tcommon="http://rs.tdwg.org/ontology/voc/Common#"

exclude-result-prefixes="bibo dcterms rdf tcommon tconcept uniprot"


>

<xsl:output method='html' version='1.0' encoding='utf-8' indent='yes'/>

<xsl:template match="/">

<!-- operations -->
<div id="rightnav">
<div>
<h4>On the Web</h4>
<ul type="square">
<li>
<a>
<xsl:attribute name="href">
<xsl:text>http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=</xsl:text>
<xsl:value-of select="substring-after(@rdf:resource, 'http://bioguid.info/')" />
</xsl:attribute>
<xsl:value-of select="//dcterms:title" />
</a>
</li>
</ul>
</div>

</div>

<div class="document">
<h1><xsl:value-of select="//tconcept:nameString" /></h1>
<div><xsl:value-of select="//tcommon:taxonomicPlacementFormal" /></div>
</div>

</xsl:template>

</xsl:stylesheet>