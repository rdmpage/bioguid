<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:dcterms='http://purl.org/dc/terms/'
  xmlns:bibo='http://purl.org/ontology/bibo/'
  xmlns:prism='http://prismstandard.org/namespaces/2.0/basic/'
xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"

exclude-result-prefixes="bibo dcterms geo prism rdf "

>


<xsl:output method='html' version='1.0' encoding='utf-8' indent='yes'/>

<xsl:template match="/">


<!-- operations -->
<div id="nav">
<div>
<b>On the Web</b><br/>
<ul type="square">
<li>
<a>
<xsl:attribute name="href">
<xsl:text>http://dx.doi.org/</xsl:text>
<xsl:value-of select="//bibo:doi" />
</xsl:attribute>
<xsl:attribute name="target">_new</xsl:attribute>
<xsl:text>doi:</xsl:text>
<xsl:value-of select="//bibo:doi" />
</a>
</li>
</ul>
<b>Post to:</b><br/>
<ul type="square">
<li>Citeulike</li>
<li>Connotea</li>
<li>Mendeley</li>
</ul>
</div>

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

<!-- sequences -->
<div><span>Sequences </span>
<span style="font-size:36px">
<xsl:value-of select="count(//rdf:type[@rdf:resource = 'http://purl.uniprot.org/core/Molecule'])" />
</span>
</div>



</div>

<div id="content">

<h1 class="publication"><xsl:value-of select="//dcterms:title" /></h1>

<!-- authors -->
<h2 class="people">
<xsl:apply-templates select="//dcterms:creator" />
</h2>
<!-- metadata -->
<div>
<xsl:choose>
	<xsl:when test="//prism:issn != ''">
		<span class="internal_link">
		<xsl:attribute name="onclick">
		<xsl:text>lookahead('</xsl:text>
		<xsl:text>http://bioguid.info/issn:</xsl:text>
		<xsl:value-of select="//prism:issn" />
		<xsl:text>')</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="//prism:publicationName" />
		</span>
	</xsl:when>
	<xsl:otherwise>
		<xsl:value-of select="//prism:publicationName" />
	</xsl:otherwise>
</xsl:choose>

<xsl:text> </xsl:text>
<xsl:value-of select="//prism:volume" />
<xsl:text>: </xsl:text>
<xsl:value-of select="//prism:startingPage" />
<xsl:if test="//prism:endingPage != ''">
<xsl:text>-</xsl:text>
<xsl:value-of select="//prism:endingPage" />
</xsl:if>
<xsl:text> (</xsl:text>
<xsl:value-of select="//dcterms:date" />
<xsl:text>)</xsl:text>

<!-- external links -->
<xsl:text> </xsl:text>
<xsl:text>doi:</xsl:text>
<xsl:value-of select="//bibo:doi" />
</div>

<!-- abstract -->
<xsl:apply-templates select="//dcterms:abstract" />


<!-- topics -->
<div style="padding-top:10px;">
<ul type="square">
<xsl:apply-templates select="//dcterms:subject/@rdf:resource" />
</ul>
</div>

<!-- sequences -->
<h2 class="dna">Sequences</h2>
<ul type="square">
<xsl:apply-templates select="//dcterms:references/@rdf:resource" />
</ul>
<!-- sequences that list thsis pub -->
<!-- why doesn't rdf:about work? -->
<div>
<ul type="square">
<xsl:for-each select="//rdf:type[@rdf:resource='http://purl.uniprot.org/core/Molecule']">
<li>
<span class="internal_link">
<xsl:attribute name="onclick">
<xsl:text>lookahead('</xsl:text>
<xsl:text>http://bioguid.info/genbank:</xsl:text>
<xsl:value-of select="../dcterms:title" />
<xsl:text>')</xsl:text>
</xsl:attribute>
<xsl:value-of select="../dcterms:title" />
</span>
</li>
</xsl:for-each>
</ul>
</div>



</div>
</xsl:template>

<xsl:template match="//dcterms:creator">
<xsl:if test="position() != 1"><xsl:text>, </xsl:text></xsl:if>
<xsl:value-of select="." />
</xsl:template>

<!-- abstract -->
<xsl:template match="//dcterms:abstract">
<div class="abstract">
<xsl:value-of select="." />
</div>
</xsl:template>

<!-- tag -->
<xsl:template match="//dcterms:subject/@rdf:resource">
<li>
<a>
<xsl:attribute name="href">
<xsl:text>/~rpage/ispecies/www/search/topic/</xsl:text>
<xsl:value-of select="substring-after(., 'http://ispecies.org/topic/')" />
</xsl:attribute>
<xsl:value-of select="substring-after(., 'http://ispecies.org/topic/')" />
</a>
</li>
</xsl:template>

<!-- cited sequences -->
<xsl:template match="//dcterms:references/@rdf:resource">
<li>
<span class="internal_link">
<xsl:attribute name="onclick">
<xsl:text>lookahead('</xsl:text>
<xsl:value-of select="." />
<xsl:text>')</xsl:text>
</xsl:attribute>
<xsl:value-of select="substring-after(., 'http://bioguid.info/')" />
</span>
</li>
</xsl:template>

</xsl:stylesheet>