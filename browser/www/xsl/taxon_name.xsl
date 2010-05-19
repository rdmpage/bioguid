<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' 
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:dcterms='http://purl.org/dc/terms/'
xmlns:dc="http://purl.org/dc/elements/1.1/"
 xmlns:tname="http://rs.tdwg.org/ontology/voc/TaxonName#" xmlns:tpub="http://rs.tdwg.org/ontology/voc/PublicationCitation#"
  xmlns:tconcept="http://rs.tdwg.org/ontology/voc/TaxonConcept#"
xmlns:trank="http://rs.tdwg.org/ontology/voc/TaxonRank#" xmlns:tcommon="http://rs.tdwg.org/ontology/voc/Common#"

exclude-result-prefixes="dcterms rdf tname trank tcommon tpub tconcept"


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
<h1>
	<xsl:if test="//dc:title != ''">
		<xsl:value-of select="//dc:title" />
	</xsl:if>
	<xsl:if test="//dcterms:title != ''">
		<xsl:value-of select="//dcterms:title" />
	</xsl:if>
</h1>
</div>

</xsl:template>

</xsl:stylesheet>