<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' 
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:dcterms='http://purl.org/dc/terms/'
  xmlns:bibo='http://purl.org/ontology/bibo/'
  xmlns:tconcept="http://rs.tdwg.org/ontology/voc/TaxonConcept#"
  xmlns:uniprot="http://purl.uniprot.org/core/"
 xmlns:tcommon="http://rs.tdwg.org/ontology/voc/Common#"
xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"

exclude-result-prefixes="bibo dcterms geo rdf tcommon tconcept uniprot"


>

<xsl:output method='html' version='1.0' encoding='utf-8' indent='yes'/>

<xsl:template match="/">

<!-- operations -->
<div id="nav">

<div>
<h4>On the Web</h4>
<ul type="square">
<xsl:for-each select="//rdf:Description">
<xsl:if test="rdf:type[@rdf:resource = 'http://rs.tdwg.org/ontology/voc/TaxonConcept#TaxonConcept']">
<li>
<a>
<xsl:attribute name="href">
<xsl:text>http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=</xsl:text>
<xsl:value-of select="substring-after(@rdf:about, 'http://bioguid.info/taxonomy:')" />
</xsl:attribute>
<xsl:attribute name="target">
<xsl:text>_new</xsl:text>
</xsl:attribute>
<xsl:value-of select="substring-after(@rdf:about, 'http://bioguid.info/taxonomy:')" />
</a>
</li>
</xsl:if>
</xsl:for-each>
</ul>

</div>

<h4>Map</h4>
<div>

<img>
<xsl:attribute name="src">
<xsl:text>http://maps.google.com/maps/api/staticmap?size=200x200&amp;maptype=terrain&amp;zoom=1
&amp;markers=color:red</xsl:text>
<xsl:for-each select="//rdf:type[@rdf:resource = 'http://rs.tdwg.org/ontology/voc/TaxonOccurrence#TaxonOccurrence']">
<xsl:text>|</xsl:text>
<xsl:value-of select="../geo:lat" />
<xsl:text>,</xsl:text>
<xsl:value-of select="../geo:long" />
</xsl:for-each>
<xsl:text>&amp;sensor=false</xsl:text>
</xsl:attribute>
</img>


</div>

<!-- sequences -->
<div><span>Sequences </span>
<span style="font-size:36px">
<xsl:value-of select="count(//rdf:type[@rdf:resource = 'http://purl.uniprot.org/core/Molecule'])" />
</span>
</div>

<!-- publications -->
<div><span>Publications </span>
<span style="font-size:36px">
<xsl:value-of select="count(//rdf:type[@rdf:resource = 'http://purl.org/ontology/bibo/Article'])" />
</span>
</div>

</div>

<div id="content">

<div class="document">
<h1>[Taxon] <xsl:value-of select="//tconcept:nameString" /></h1>
<div><xsl:value-of select="//tcommon:taxonomicPlacementFormal" /></div>
</div>

<div class="document">
<h2>Sequences</h2>
<ul type="square">
<xsl:for-each select="//rdf:type[@rdf:resource = 'http://purl.uniprot.org/core/Molecule']">
<li>
<span class="internal_link">
<xsl:attribute name="onclick">
<xsl:text>lookahead('</xsl:text>
<xsl:value-of select="../../rdf:Description/@rdf:about" />
<xsl:text>')</xsl:text>
</xsl:attribute>
<xsl:value-of select="../dcterms:title" />
</span>
</li>
</xsl:for-each>
</ul>
</div>

<div class="document">
<h2>Publications</h2>
<ul type="square">
<xsl:for-each select="//rdf:type[@rdf:resource = 'http://purl.org/ontology/bibo/Article']">
<li>
<span class="internal_link">
<xsl:attribute name="onclick">
<xsl:text>lookahead('</xsl:text>
<xsl:value-of select="../../rdf:Description/@rdf:about" />
<xsl:text>')</xsl:text>
</xsl:attribute>
<xsl:value-of select="../bibo:doi" />
</span>
</li>
</xsl:for-each>
</ul>
</div>


</div>



</xsl:template>

</xsl:stylesheet>