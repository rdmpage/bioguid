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
  xmlns:foo="http://purl.org/rss/1.0/">
    <xsl:output method="text" encoding="utf-8" indent="yes"/>	<xsl:template match="/">
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

	<xsl:text>&#x09;&#x09;"title":"</xsl:text>
		<xsl:variable name="atitle" select="foo:title"/>		<xsl:call-template name="cleanQuote">			<xsl:with-param name="string" select="$atitle"/>		</xsl:call-template>


	<xsl:text>",&#xD;</xsl:text>
	<!-- link -->	<xsl:text>&#x09;&#x09;"link":"</xsl:text>	<xsl:value-of select="foo:link"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- description -->	<xsl:text>&#x09;&#x09;"description":"</xsl:text>

		<xsl:variable name="desc" select="foo:description"/>		<xsl:call-template name="cleanQuote">			<xsl:with-param name="string" select="$desc"/>		</xsl:call-template>
	<xsl:text>",&#xD;</xsl:text>

	<!-- identifier -->
	<xsl:text>&#x09;&#x09;"identifier":"</xsl:text>	<xsl:value-of select="dc:identifier"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- keywords -->
	<xsl:text>&#x09;&#x09;"keywords":[&#xD;</xsl:text>
		<xsl:apply-templates select="dc:keyword"/>
	<xsl:text>&#xD;&#x09;&#x09;]&#xD;</xsl:text>




<xsl:text>&#x09;&#x09;&#x09;}</xsl:text>

	</xsl:template>
<xsl:template match="dc:keyword">
<xsl:if test="position() != 1"><xsl:text>,&#xD;</xsl:text></xsl:if>

	<xsl:text>"</xsl:text>
<xsl:value-of select="."/>
<xsl:text>"</xsl:text>

</xsl:template>

    <!-- From http://www.dpawson.co.uk/xsl/sect2/StringReplace.html#d10992e82 --><xsl:template name="cleanQuote"><xsl:param name="string" /><xsl:if test="contains($string, '&#x22;')">
<xsl:value-of    select="substring-before($string, '&#x22;')" /><xsl:text>\"</xsl:text>
<xsl:call-template name="cleanQuote">	<xsl:with-param name="string">
		<xsl:value-of select="substring-after($string, '&#x22;')" />    </xsl:with-param></xsl:call-template></xsl:if><xsl:if test="not(contains($string, '&#x22;'))"><xsl:value-ofselect="$string" /></xsl:if></xsl:template>

</xsl:stylesheet>