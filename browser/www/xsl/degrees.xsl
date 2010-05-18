<?xml version="1.0" encoding='UTF-8'?>

<!-- $Id: $ -->

<!-- Convert decimal degrees to degrees minutes seconds -->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"   
		xmlns="http://www.w3.org/1999/xhtml"
                xmlns:doc="http://xsltsl.org/xsl/documentation/1.0"
                exclude-result-prefixes="doc"
		version = "1.0">

     <xsl:output method="xml"/>

    <xsl:template name="format-degrees">
        <xsl:param name="decimal"/>
        <xsl:variable name="degrees" select="floor($decimal)"/>
        <xsl:variable name="minutes" select="floor(60 * ($decimal - $degrees))"/>
        <xsl:variable name="seconds" select="round(60 *(60 * ($decimal - $degrees) - $minutes))"/>
        <xsl:value-of select="$degrees"/>&#176;<xsl:value-of select="$minutes"/>'<xsl:value-of
        select="$seconds"/>''
    </xsl:template>
        
        
</xsl:stylesheet>