<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:dcterms='http://purl.org/dc/terms/'
  xmlns:bibo='http://purl.org/ontology/bibo/'
  xmlns:toccurrence="http://rs.tdwg.org/ontology/voc/TaxonOccurrence#"
  xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
  xmlns:uniprot="http://purl.uniprot.org/core/"

exclude-result-prefixes="bibo dcterms geo rdf toccurrence uniprot"


>

<xsl:output method='html' version='1.0' encoding='utf-8' indent='yes'/>

    <xsl:include href="date.xsl" />
    <xsl:include href="degrees.xsl" />


<xsl:template match="/">

<!-- operations -->
<div id="rightnav">

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


<!-- sequences -->
<xsl:if test="//rdf:type[@rdf:resource = 'http://purl.uniprot.org/core/Molecule']">
<div>
<h4>Sequences
<xsl:text> [</xsl:text>
<xsl:value-of select="count(//rdf:type[@rdf:resource = 'http://purl.uniprot.org/core/Molecule'])" />
<xsl:text>]</xsl:text>
</h4>
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
</xsl:if>

<!-- publications -->
<xsl:if test="//rdf:type[@rdf:resource = 'http://purl.org/ontology/bibo/Article']">
<div>
<h4>Publications
<xsl:text> [</xsl:text>
<xsl:value-of select="count(//rdf:type[@rdf:resource = 'http://purl.org/ontology/bibo/Article'])" />
<xsl:text>]</xsl:text>
</h4>
<ul type="square">
<xsl:for-each select="//rdf:type[@rdf:resource = 'http://purl.org/ontology/bibo/Article']">
<li>
<span class="internal_link">
<xsl:attribute name="onclick">
<xsl:text>lookahead('</xsl:text>
<xsl:value-of select="../../rdf:Description/@rdf:about" />
<xsl:text>')</xsl:text>
</xsl:attribute>
<xsl:value-of select="//bibo:doi" /></span>
</li>
</xsl:for-each>
</ul>
</div>
</xsl:if>

</div>


<div class="document">
<h1>
<xsl:choose>
	<xsl:when test="//toccurrence:institutionCode = 'casent'">
	</xsl:when>
	<xsl:otherwise>
	<xsl:value-of select="//toccurrence:institutionCode" />
	<xsl:text> </xsl:text>
	</xsl:otherwise>
	</xsl:choose>
<xsl:value-of select="//toccurrence:catalogNumber" />
</h1>

	<div><xsl:value-of select="//toccurrence:identifiedToString" /></div>

	<!-- locality -->
	<div><xsl:text> </xsl:text> <!-- empty div breaks webkit rendering -->
		<xsl:if test="//toccurrence:country != ''">
			<xsl:value-of select="//toccurrence:country" /><xsl:text>: </xsl:text>
		</xsl:if>
		<xsl:if test="//toccurrence:stateProvince != ''">
			<xsl:value-of select="//toccurrence:stateProvince" />
		</xsl:if>
		<xsl:if test="//toccurrence:locality != ''">
			<xsl:value-of select="//toccurrence:locality" />
		</xsl:if>

	<xsl:if test="//geo:lat != ''">
		<xsl:text> </xsl:text>
			<xsl:variable name="lat" select="//geo:lat"/>
			<xsl:choose>
				<xsl:when test="$lat &lt; 0">
					<xsl:variable name="decimal" select=" -1* $lat"/>
					<xsl:call-template name="format-degrees">
						<xsl:with-param name="decimal" select="$decimal"/>
					</xsl:call-template>
					<xsl:text>S</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:variable name="decimal" select="$lat"/>
					<xsl:call-template name="format-degrees">
						<xsl:with-param name="decimal" select="$decimal"/>
					</xsl:call-template>
					<xsl:text>N</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:text>,&#160;</xsl:text>
							   
			<!-- longitude -->
			<xsl:variable name="long" select="//geo:long"/>
			<xsl:choose>
				<xsl:when test="$long &lt; 0">
					<xsl:variable name="decimal" select=" -1* $long"/>
					<xsl:call-template name="format-degrees">
						<xsl:with-param name="decimal" select="$decimal"/>
					</xsl:call-template>
					<xsl:text>W</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:variable name="decimal" select="$long"/>
					<xsl:call-template name="format-degrees">
						<xsl:with-param name="decimal" select="$decimal"/>
					</xsl:call-template>
					<xsl:text>E</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
	</xsl:if>


	</div>

	<div><xsl:text> </xsl:text> <!-- empty div breaks webkit rendering -->
		<xsl:if test="//toccurrence:collector != ''">
			<xsl:text>Collected by </xsl:text>
			<xsl:value-of select="//toccurrence:collector" />
			<xsl:text> </xsl:text>
		</xsl:if>

		<xsl:if test="//toccurrence:verbatimCollectingDate != ''">
			<xsl:value-of select="//toccurrence:verbatimCollectingDate" />
		</xsl:if>
	</div>

	<div><xsl:text> </xsl:text> <!-- empty div breaks webkit rendering -->
		<xsl:if test="//dcterms:identifier != ''">
			<xsl:text>Voucher </xsl:text>
			<xsl:value-of select="//dcterms:identifier" />
		</xsl:if>
	</div>


</div>





</xsl:template>



</xsl:stylesheet>