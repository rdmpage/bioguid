<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
  xmlns:dcterms='http://purl.org/dc/terms/'
  xmlns:bibo='http://purl.org/ontology/bibo/'
	xmlns:foaf="http://xmlns.com/foaf/0.1/"
>

<xsl:output method='html' version='1.0' encoding='utf-8' indent='yes'/>

<xsl:template match="/">

<!-- operations -->
<div id="rightnav">
<div>
<b>View</b><br/>
<ul type="square">
<li>
<a>
<xsl:attribute name="href">
<xsl:text>http://www.worldcat.org/issn/</xsl:text>
<xsl:value-of select="//bibo:issn" />
</xsl:attribute>
<xsl:text>ISSN:</xsl:text>
<xsl:value-of select="//bibo:issn" />
</a>
</li>
</ul>
</div>

<div>
<xsl:if test="//foaf:depiction[@rdf:resource != '']">
<img>
<xsl:attribute name="width">
<xsl:text>120</xsl:text>
</xsl:attribute>
<xsl:attribute name="src">
<xsl:value-of select="//foaf:depiction/@rdf:resource" />
</xsl:attribute>
</img>
</xsl:if>
</div>

<!-- articles -->
<div><span>Articles </span>
<span style="font-size:36px">
<xsl:value-of select="count(//rdf:type[@rdf:resource = 'http://purl.org/ontology/bibo/Article'])" />
</span>
</div>

</div>

<div class="document">
<h1><xsl:value-of select="//dcterms:title" /></h1>
<ul>
<li><xsl:value-of select="//dcterms:title" /><xsl:text> (</xsl:text><xsl:value-of select="//dcterms:title/@xml:lang" /><xsl:text>)</xsl:text></li>
<xsl:apply-templates select="//bibo:shortTitle" />
</ul>
</div>


<div class="document">
<h2>Articles in this journal</h2>
<ul type="square">

<xsl:for-each select="//rdf:Description">
<xsl:if test="rdf:type[@rdf:resource = 'http://purl.org/ontology/bibo/Article']">
<li>
<span class="internal_link">
<xsl:attribute name="onclick">
<xsl:text>lookahead('</xsl:text>
<xsl:value-of select="@rdf:about" />
<xsl:text>')</xsl:text>
</xsl:attribute>
<xsl:value-of select="dcterms:title" />
</span>
</li>
</xsl:if>
</xsl:for-each>
</ul>
</div>



</xsl:template>

<xsl:template match="//bibo:shortTitle">
<li><xsl:value-of select="." /><xsl:text> (</xsl:text><xsl:value-of select="@xml:lang" /><xsl:text>)</xsl:text></li>
</xsl:template>

</xsl:stylesheet>