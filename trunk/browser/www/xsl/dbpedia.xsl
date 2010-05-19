<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'

xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
	xmlns:owl="http://www.w3.org/2002/07/owl#"
  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:dbpedia-owl="http://dbpedia.org/ontology/"
  xmlns:dbpprop="http://dbpedia.org/property/"
  xmlns:foaf="http://xmlns.com/foaf/0.1/"
xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"

exclude-result-prefixes="dcterms dbpedia-owl dbpprop foaf geo rdf rdfs "

>

<xsl:output method='html' version='1.0' encoding='utf-8' indent='yes'/>

<xsl:template match="/">


<!-- operations -->
<div id="rightnav">

<h4>View</h4>
<ul type="square">
<li>
<a>
<xsl:attribute name="href">
<xsl:value-of select="//foaf:page/@rdf:resource" />
</xsl:attribute>
<xsl:text>Wikipedia</xsl:text>
</a>
</li>
<xsl:apply-templates select="//owl:sameAs/@rdf:resource" />
</ul>

<!-- image -->
<div>
<!-- temporary hack as Dbpedia has broken CAS image -->
<xsl:choose>
	<xsl:when test="//dbpedia-owl:thumbnail/@rdf:resource = 'http://upload.wikimedia.org/wikipedia/commons/thumb/9/9c/CAS_new_logo.png/200px-CAS_new_logo.png'">
		<img src="http://upload.wikimedia.org/wikipedia/en/thumb/9/9c/CAS_new_logo.png/200px-CAS_new_logo.png" />
	</xsl:when>
	<xsl:otherwise>
		<xsl:apply-templates select="//dbpedia-owl:thumbnail/@rdf:resource" />
	</xsl:otherwise>
</xsl:choose>
</div>

<!-- status -->
<xsl:if test="//dbpedia-owl:conservationStatus != ''">
<div>
<h4>Status</h4>
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
			<!-- <img src="images/wikipedia/200px-Status_iucn3.1_blank.svg.png" /> -->
		</xsl:otherwise> 
	</xsl:choose>

</div>
</xsl:if>

<!-- map -->
<xsl:if test="//geo:lat != ''">
<div>
<h4>Map</h4>
<div>
	<xsl:value-of select="//geo:lat" />
	<xsl:text>, </xsl:text>
	<xsl:value-of select="//geo:long" />
</div>
<img>
<xsl:attribute name="src">
<xsl:text>http://maps.google.com/maps/api/staticmap?size=200x200&amp;maptype=terrain&amp;zoom=3
&amp;markers=color:red|</xsl:text>
<xsl:value-of select="//geo:lat" />
<xsl:text>,</xsl:text>
<xsl:value-of select="//geo:long" />
<xsl:text>&amp;sensor=false</xsl:text>
</xsl:attribute>
</img>
</div>
</xsl:if>

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

<xsl:template match="owl:sameAs/@rdf:resource">
	<xsl:choose>
	<xsl:when test="contains(., 'http://rdf.freebase.com/')">
	<li>
	<a>
	<xsl:attribute name="href">
	<xsl:value-of select="." />
	</xsl:attribute>
	<xsl:text>Freebase</xsl:text>
	</a>
	</li>
	</xsl:when>
	<xsl:otherwise>
	</xsl:otherwise>
	</xsl:choose>
</xsl:template>

</xsl:stylesheet>