<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
   >
 
   <xsl:output method="text" version="1.0" encoding="utf-8" indent="yes"/>



    <xsl:template match="/">
  
<xsl:text>{&#x0D;</xsl:text>

       <xsl:apply-templates select="eLinkResult/LinkSet/IdUrlList/IdUrlSet"/>
<xsl:text>&#x0D;}</xsl:text>


    </xsl:template>

    <xsl:template match="eLinkResult/LinkSet/IdUrlList/IdUrlSet">
<xsl:text>"linkouts":[</xsl:text>
        <xsl:apply-templates select="ObjUrl"/>
<xsl:text>]</xsl:text>
	</xsl:template>

   <xsl:template match="ObjUrl">

<xsl:if test="position() != 1"><xsl:text>,</xsl:text></xsl:if>
	<xsl:text>&#x0D;</xsl:text>
<xsl:text>{&#x0D;</xsl:text>

	<xsl:text>&#x09;"ProviderName":"</xsl:text>
		<xsl:value-of select="Provider/Name"/>
	<xsl:text>"</xsl:text>

	<xsl:text>,&#x0D;</xsl:text>
	
	<xsl:text>&#x09;"NameAbbr":"</xsl:text>
		<xsl:value-of select="Provider/NameAbbr"/>
	<xsl:text>"</xsl:text>
	<xsl:text>,&#x0D;</xsl:text>

	<xsl:text>&#x09;"Url":"</xsl:text>
		<xsl:value-of select="Url"/>
	<xsl:text>"</xsl:text>

<xsl:text>&#x0D;}</xsl:text>

	</xsl:template>


 </xsl:stylesheet>
