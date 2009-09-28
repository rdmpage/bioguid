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
    <xsl:output method="text" encoding="utf-8" indent="yes"/>
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


		<xsl:variable name="atitle" select="foo:title"/>


	<xsl:text>",&#xD;</xsl:text>
	<!-- link -->

	<!-- description -->

		<xsl:variable name="desc" select="foo:description"/>
	<xsl:text>",&#xD;</xsl:text>

	<!-- identifier -->
	<xsl:text>&#x09;&#x09;"identifier":"</xsl:text>

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

    <!-- From http://www.dpawson.co.uk/xsl/sect2/StringReplace.html#d10992e82 -->
<xsl:value-of
<xsl:call-template name="cleanQuote">
		<xsl:value-of select="substring-after($string, '&#x22;')" />

</xsl:stylesheet>