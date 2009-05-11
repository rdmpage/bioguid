<?xml version="1.0"?>
<!--
  Title: RSS 2.0 XSL Template
  Author: Rich Manalang (http://manalang.com)
  Description: This sample XSLT will convert any valid RSS 2.0 feed to HTML.
-->
<xsl:stylesheet version="1.0"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:wfw="http://wellformedweb.org/CommentAPI/">
    <xsl:output method="text" version="1.0" encoding="utf-8" indent="no"/>
	<xsl:template match="/">
<xsl:text>{
</xsl:text>
		<xsl:apply-templates select="/rss/channel"/>
<xsl:text>}
</xsl:text>
	</xsl:template>
	<xsl:template match="/rss/channel">
        <xsl:text>&#x0D;"items":[</xsl:text>

			<xsl:apply-templates select="item"/>
        <xsl:text>&#x0D;]&#x0D;</xsl:text>

	</xsl:template>
	<xsl:template match="/rss/channel/item">

        <xsl:if test="position() != 1">
            <xsl:text>,</xsl:text>
        </xsl:if>
        <xsl:text>&#x0D;{&#x0D;</xsl:text>

		<!-- link -->
        <xsl:text>"link":"</xsl:text>
 		<xsl:value-of select="link"/>       
		<xsl:text>"</xsl:text>

		<!-- guid -->
		<xsl:apply-templates select="guid"/>

	<!-- description -->
	<xsl:text>,&#xD;&#x09;&#x09;"description":"</xsl:text>		<xsl:variable name="content" select="description"/>
		<xsl:call-template name="cleanQuote">
			<xsl:with-param name="string" select="$content"/>
		</xsl:call-template>
<xsl:text>"</xsl:text>

        <xsl:text>&#x0D;}&#x0D;</xsl:text>



	</xsl:template>


	<xsl:template match="guid">
        <xsl:text>,&#x0D;"guid":"</xsl:text>
		<xsl:value-of select="."/>       
		<xsl:text>"&#x0D;</xsl:text>
	</xsl:template>

    <!-- From http://www.dpawson.co.uk/xsl/sect2/StringReplace.html#d10992e82 --><xsl:template name="cleanQuote"><xsl:param name="string" /><xsl:if test="contains($string, '&#x22;')">
<xsl:value-of    select="substring-before($string, '&#x22;')" /><xsl:text>\"</xsl:text>
<xsl:call-template name="cleanQuote">	<xsl:with-param name="string">
		<xsl:value-of select="substring-after($string, '&#x22;')" />    </xsl:with-param></xsl:call-template></xsl:if><xsl:if test="not(contains($string, '&#x22;'))"><xsl:value-ofselect="$string" /></xsl:if></xsl:template>

</xsl:stylesheet>