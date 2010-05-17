<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'

xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"

  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:dbpedia-owl="http://dbpedia.org/ontology/"
  xmlns:dbpprop="http://dbpedia.org/property/"
  xmlns:foaf="http://xmlns.com/foaf/0.1/"

exclude-result-prefixes="dcterms dbpedia-owl dbpprop foaf  rdf rdfs "

>

<xsl:output method='html' version='1.0' encoding='utf-8' indent='yes'/>

<xsl:template match="/">


<!-- operations -->
<div id="rightnav">

<b>View</b><br/>
<ul type="square">
<li>
<!-- wikipedia -->
<a>
<xsl:attribute name="href">
<xsl:value-of select="//foaf:page/@rdf:resource" />
</xsl:attribute>
<xsl:text>Wikipedia</xsl:text>
</a>
</li>
</ul>

<!-- image -->
<div>
<xsl:apply-templates select="//dbpedia-owl:thumbnail/@rdf:resource" />
</div>

<!-- status -->
<div>
	<xsl:choose>
		<xsl:when test="//dbpedia-owl:conservationStatus = 'LC'">
			<img src="images/wikipedia/200px-Status_iucn3.1_LC.svg.png" />
		</xsl:when>

		<xsl:when test="//dbpedia-owl:conservationStatus = 'CR'">
			<img src="images/wikipedia/200px-Status_iucn3.1_CR.svg.png" />
		</xsl:when>

		<xsl:when test="//dbpedia-owl:conservationStatus = 'EW'">
			<img src="images/wikipedia/200px-Status_iucn3.1_EW.svg.png" />
		</xsl:when>

		<xsl:when test="//dbpedia-owl:conservationStatus = 'NT'">
			<img src="images/wikipedia/200px-Status_iucn3.1_NT.svg.png" />
		</xsl:when>

		<xsl:when test="//dbpedia-owl:conservationStatus = 'VU'">
			<img src="images/wikipedia/200px-Status_iucn3.1_VU.svg.png" />
		</xsl:when>

		<xsl:when test="//dbpedia-owl:conservationStatus = 'EN'">
			<img src="images/wikipedia/200px-Status_iucn3.1_EN.svg.png" />
		</xsl:when>

		<xsl:when test="//dbpedia-owl:conservationStatus = 'EX'">
			<img src="images/wikipedia/200px-Status_iucn3.1_EX.svg.png" />
		</xsl:when>

		<xsl:otherwise>
			<img src="images/wikipedia/200px-Status_iucn3.1_blank.svg.png" />
		</xsl:otherwise> 
	</xsl:choose>

</div>

</div>

<div class="document">

<h1><xsl:value-of select="//rdfs:label[@xml:lang='en']" /></h1>

	<xsl:value-of select="//dbpedia-owl:abstract[@xml:lang='en']" />
</div>

</xsl:template>

<xsl:template match="dbpedia-owl:thumbnail/@rdf:resource">
<img>
<xsl:attribute name="src">
<xsl:value-of select="." />
</xsl:attribute>
</img>
</xsl:template>


</xsl:stylesheet>