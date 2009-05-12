<?xml version="1.0"?>
<!--
  Title: RSS 2.0 XSL Template
  Author: Rich Manalang (http://manalang.com)
  Description: This sample XSLT will convert any valid RSS 2.0 feed to HTML.
-->
<xsl:stylesheet version="1.0"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:wfw="http://wellformedweb.org/CommentAPI/">
    <xsl:output method="text" encoding="utf-8" indent="yes"/>	<xsl:template match="/">
<xsl:text>{</xsl:text>
		<xsl:apply-templates select="/rss/channel"/>
<xsl:text>}</xsl:text>
	</xsl:template>

	<xsl:template match="/rss/channel">

<xsl:text>&#xD;&#x09;"items":[&#xD;</xsl:text>				<xsl:apply-templates select="item"/>
<xsl:text>&#x09;]&#xD;</xsl:text>
	</xsl:template>


	<xsl:template match="/rss/channel/item">


<xsl:if test="position() != 1"><xsl:text>,&#xD;</xsl:text></xsl:if><xsl:text>&#x09;&#x09;&#x09;{&#xD;</xsl:text>	<!-- title -->	<xsl:text>&#x09;&#x09;"title":"</xsl:text>		<xsl:value-of select="title"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- link -->	<xsl:text>&#x09;&#x09;"link":"</xsl:text>		<xsl:value-of select="link"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- pubDate -->	<xsl:text>&#x09;&#x09;"pubDate":"</xsl:text>		<xsl:value-of select="pubDate"/>	<xsl:text>",&#xD;</xsl:text>

	<!-- description -->	<xsl:text>&#x09;&#x09;"description":"</xsl:text>

		<xsl:variable name="desc" select="description"/>		<xsl:call-template name="cleanQuote">			<xsl:with-param name="string" select="$desc"/>		</xsl:call-template>
	<xsl:text>",&#xD;</xsl:text>

	<!-- guid -->	<xsl:text>&#x09;&#x09;"guid":"</xsl:text>		<xsl:value-of select="guid"/>	<xsl:text>"&#xD;</xsl:text>

<xsl:text>&#x09;&#x09;&#x09;}&#xD;</xsl:text>

	</xsl:template>

    <!-- From http://www.dpawson.co.uk/xsl/sect2/StringReplace.html#d10992e82 --><xsl:template name="cleanQuote"><xsl:param name="string" /><xsl:if test="contains($string, '&#x22;')">
<xsl:value-of    select="substring-before($string, '&#x22;')" /><xsl:text>\"</xsl:text>
<xsl:call-template name="cleanQuote">	<xsl:with-param name="string">
		<xsl:value-of select="substring-after($string, '&#x22;')" />    </xsl:with-param></xsl:call-template></xsl:if><xsl:if test="not(contains($string, '&#x22;'))"><xsl:value-ofselect="$string" /></xsl:if></xsl:template>


</xsl:stylesheet>