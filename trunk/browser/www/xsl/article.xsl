<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:dcterms='http://purl.org/dc/terms/'
  xmlns:bibo='http://purl.org/ontology/bibo/'
  xmlns:prism='http://prismstandard.org/namespaces/2.0/basic/'
>


<xsl:output method='html' version='1.0' encoding='utf-8' indent='no'/>

<xsl:template match="/">


<!-- operations -->
<div id="rightnav">
<div>
<b>View</b><br/>
<ul type="square">
<li>
<a>
<xsl:attribute name="href">
<xsl:text>http://dx.doi.org/</xsl:text>
<xsl:value-of select="//bibo:doi" />
</xsl:attribute>
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
</div>

<div class="document">

<h1><xsl:value-of select="//dcterms:title" /></h1>

<!-- authors -->
<h2>
<xsl:apply-templates select="//dcterms:creator" />
</h2>
<!-- metadata -->
<div>
<xsl:value-of select="//prism:publicationName" />
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
<xsl:apply-templates select="//dcterms:subject/@rdf:resource" />
</div>

<!-- sequences -->
<div style="padding-top:10px;">
<xsl:apply-templates select="//dcterms:references/@rdf:resource" />
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
<span style="background-color:rgb(218,218,218);padding:4px;-webkit-border-radius:6">
<a>
<xsl:attribute name="href">
<xsl:text>/~rpage/ispecies/www/search/topic/</xsl:text>
<xsl:value-of select="substring-after(., 'http://ispecies.org/topic/')" />
</xsl:attribute>
<xsl:value-of select="substring-after(., 'http://ispecies.org/topic/')" />
</a>
</span>
</xsl:template>

<!-- cited sequences -->
<xsl:template match="//dcterms:references/@rdf:resource">
<span>
<xsl:attribute name="onclick">
<xsl:text>lookahead('</xsl:text>
<xsl:value-of select="." />
<xsl:text>')</xsl:text>
</xsl:attribute>
<xsl:value-of select="substring-after(., 'http://bioguid.info/')" />
</span>
<xsl:text> </xsl:text>
</xsl:template>

</xsl:stylesheet>