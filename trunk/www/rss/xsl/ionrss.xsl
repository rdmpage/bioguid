<?xml version="1.0"?>
<!--
  Title: RSS 2.0 XSL Template
  Author: Rich Manalang (http://manalang.com)
  Description: This sample XSLT will convert any valid RSS 2.0 feed to HTML.
-->
<xsl:stylesheet version="1.0"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:wfw="http://wellformedweb.org/CommentAPI/">
    <xsl:output method="text" encoding="utf-8" indent="yes"/>
<xsl:text>{</xsl:text>
		<xsl:apply-templates select="/rss/channel"/>
<xsl:text>}</xsl:text>
	</xsl:template>

	<xsl:template match="/rss/channel">

<xsl:text>&#xD;&#x09;"items":[&#xD;</xsl:text>
<xsl:text>&#x09;]&#xD;</xsl:text>
	</xsl:template>


	<xsl:template match="/rss/channel/item">


<xsl:if test="position() != 1"><xsl:text>,&#xD;</xsl:text></xsl:if>

	<!-- link -->

	<!-- pubDate -->

	<!-- description -->

		<xsl:variable name="desc" select="description"/>
	<xsl:text>",&#xD;</xsl:text>

	<!-- guid -->

<xsl:text>&#x09;&#x09;&#x09;}&#xD;</xsl:text>

	</xsl:template>

    <!-- From http://www.dpawson.co.uk/xsl/sect2/StringReplace.html#d10992e82 -->
<xsl:value-of
<xsl:call-template name="cleanQuote">
		<xsl:value-of select="substring-after($string, '&#x22;')" />


</xsl:stylesheet>