<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:owl="http://www.w3.org/2002/07/owl#"
  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:tcollection="http://rs.tdwg.org/ontology/voc/Collection#"
  xmlns:vcard="http://www.w3.org/2001/vcard-rdf/3.0#"
xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"

exclude-result-prefixes=" dcterms geo owl rdf tcollection vcard"
>

<xsl:output method='html' version='1.0' encoding='utf-8' indent='yes'/>

<xsl:template match="/">

<!-- operations -->
<div id="rightnav">

<h4>Map</h4>
<div>

<img>
<xsl:attribute name="src">
<xsl:text>http://maps.google.com/maps/api/staticmap?size=200x200&amp;maptype=terrain&amp;zoom=0
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

<!-- specimens -->
<div><span>Specimens </span>
<span style="font-size:36px">
<xsl:value-of select="count(//rdf:type[@rdf:resource = 'http://rs.tdwg.org/ontology/voc/TaxonOccurrence#TaxonOccurrence'])" />
</span>
</div>

</div>


<div class="document">
<h1><xsl:value-of select="//dcterms:title" /></h1>
<div><xsl:value-of select="//dcterms:identifier" /></div>
<div><xsl:value-of select="//tcollection:geospatialCoverage" /></div>
</div>

<div class="document">
<h2>Specimens in this collection</h2>
<ul type="square">

<xsl:for-each select="//rdf:Description">
<xsl:if test="rdf:type[@rdf:resource = 'http://rs.tdwg.org/ontology/voc/TaxonOccurrence#TaxonOccurrence']">
<li>
<span class="internal_link">
<xsl:attribute name="onclick">
<xsl:text>lookahead('</xsl:text>
<xsl:value-of select="@rdf:about" />
<xsl:text>')</xsl:text>
</xsl:attribute>
<xsl:value-of select="@rdf:about" />
</span>
</li>
</xsl:if>
</xsl:for-each>
</ul>
</div>


</xsl:template>

</xsl:stylesheet>