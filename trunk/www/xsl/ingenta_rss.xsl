<?xml version="1.0" encoding="utf-8"?>
<!--
  Title: RSS 1.0 XSL Template
  Author: Rich Manalang (http://manalang.com)
  Description: This sample XSLT will convert any valid RSS 1.0 feed to HTML.
-->
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:foo="http://purl.org/rss/1.0/"
 xmlns:prism="http://prismstandard.org/namespaces/1.2/basic/"
 xmlns:foaf="http://xmlns.com/foaf/0.1/">

    <xsl:output method="text" encoding="utf-8" indent="yes"/>

<!-- global variables -->
<xsl:variable name="issn" select="/rdf:RDF/foo:channel/prism:issn"/>
<xsl:variable name="title" select="/rdf:RDF/foo:channel/prism:publicationName"/>

<!-- date is a pain because Ingenta may include months, and it may be a range of months -->

<!-- year is the last four digits of date string -->
<xsl:variable name="len" select="string-length(/rdf:RDF/foo:channel/prism:coverDisplayDate) - 3"/>
<xsl:variable name="year" select="substring(/rdf:RDF/foo:channel/prism:coverDisplayDate, $len)"/>


	<xsl:template match="/">
<xsl:text>{</xsl:text>

		<xsl:apply-templates select="/rdf:RDF/foo:channel"/>
<xsl:text>}</xsl:text>
	</xsl:template>
	<xsl:template match="/rdf:RDF/foo:channel">








<xsl:text>&#xD;&#x09;"items":[&#xD;</xsl:text>
				<xsl:apply-templates select="/rdf:RDF/foo:item"/>
<xsl:text>&#x09;]&#xD;</xsl:text>
	</xsl:template>

	<xsl:template match="/rdf:RDF/foo:item">

<xsl:if test="position() != 1"><xsl:text>,&#xD;</xsl:text></xsl:if>

<xsl:text>&#x09;&#x09;&#x09;{&#xD;</xsl:text>

	<!-- title -->

	<xsl:text>&#x09;&#x09;"atitle":"</xsl:text>
		<xsl:variable name="atitle" select="foo:title"/>		<xsl:call-template name="cleanQuote">			<xsl:with-param name="string" select="$atitle"/>		</xsl:call-template>


	<xsl:text>",&#xD;</xsl:text>
	<!-- link -->	<xsl:text>&#x09;&#x09;"url":"</xsl:text>	<xsl:value-of select="foo:link"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- doi -->
	<xsl:apply-templates select="dc:identifier/@rdf:resource"/> 


	<!-- journal name -->
	<xsl:text>&#x09;&#x09;"title":"</xsl:text>	<xsl:value-of select="$title"/>
	<xsl:text>",&#xD;</xsl:text>

	<!-- issn -->
	<xsl:text>&#x09;&#x09;"issn":"</xsl:text>	<xsl:value-of select="$issn"/>
	<xsl:text>",&#xD;</xsl:text>

	<!-- volume -->
	<xsl:text>&#x09;&#x09;"volume":"</xsl:text>	<xsl:value-of select="prism:volume"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- issue -->
	<xsl:text>&#x09;&#x09;"issue":"</xsl:text>	<xsl:value-of select="prism:number"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- spage -->
	<xsl:text>&#x09;&#x09;"spage":"</xsl:text>	<xsl:value-of select="prism:startingPage"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- epage -->
	<xsl:text>&#x09;&#x09;"epage":"</xsl:text>	<xsl:value-of select="prism:endingPage"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- year -->
	<xsl:text>&#x09;&#x09;"year":"</xsl:text>	<xsl:value-of select="$year"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- authors -->

	<!-- array of authors as objects -->
	<xsl:text>&#x09;&#x09;"authors":[</xsl:text>
		<xsl:apply-templates select="foaf:maker"/>
	<xsl:text>&#xD;&#x09;&#x09;&#x09;]&#xD;</xsl:text>




<xsl:text>&#x09;&#x09;&#x09;}</xsl:text>

	</xsl:template>


    <xsl:template match="foaf:maker">        <xsl:if test="position() != 1">            <xsl:text>,</xsl:text>        </xsl:if>        <xsl:text>&#xD;&#09;&#09;&#09;{</xsl:text>         <xsl:text>"lastname":"</xsl:text>        <xsl:value-of select="foaf:Person/foaf:family_name"/>        <xsl:text>",</xsl:text>        <xsl:text>"forename":"</xsl:text>        <xsl:value-of select="foaf:Person/foaf:givenname"/>        <xsl:text>"</xsl:text>        <xsl:text>}</xsl:text>    </xsl:template>


<xsl:template match="dc:identifier/@rdf:resource">
                <xsl:if test="contains(., 'doi:')">
	<xsl:text>&#x09;&#x09;"doi":"</xsl:text>	<xsl:value-of select="substring-after(.,'doi:')"/>	<xsl:text>",&#xD;</xsl:text>
                </xsl:if>

</xsl:template>

    <!-- From http://www.dpawson.co.uk/xsl/sect2/StringReplace.html#d10992e82 --><xsl:template name="cleanQuote"><xsl:param name="string" /><xsl:if test="contains($string, '&#x22;')">
<xsl:value-of    select="substring-before($string, '&#x22;')" /><xsl:text>\"</xsl:text>
<xsl:call-template name="cleanQuote">	<xsl:with-param name="string">
		<xsl:value-of select="substring-after($string, '&#x22;')" />    </xsl:with-param></xsl:call-template></xsl:if><xsl:if test="not(contains($string, '&#x22;'))"><xsl:value-ofselect="$string" /></xsl:if></xsl:template>

</xsl:stylesheet>