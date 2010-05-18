<?xml version="1.0" encoding='UTF-8'?>

<!-- $Id: $ -->

<!-- Output YYYY-MM-DD date in user friendly format -->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"   
		xmlns="http://www.w3.org/1999/xhtml"
                xmlns:doc="http://xsltsl.org/xsl/documentation/1.0"
                exclude-result-prefixes="doc"
		version = "1.0">

     <xsl:output method="xml"/>

    <!-- Based on http://www.experts-exchange.com/Web/Web_Languages/XML/Q_20844888.html -->
    <xsl:template name="format-date">
        <xsl:param name="date"/>
        <xsl:param name="format" select="0"/>
        <xsl:variable name="yyyymmdd" select="substring-before($date, 'T')"/> 
        <xsl:variable name="year" select="substring-before($date, '-')"/>
        <xsl:variable name="month" select="substring-before(substring-after($date, '-'), '-')"/>
        <xsl:variable name="day" select="substring-before(substring-after(substring-after($date, '-'), '-'), 'T')"/>
        <!-- Remove leading '0' in day -->
        <xsl:variable name="day2" select="concat(translate(substring($day,1,1), '0', ''), substring($day,2,1))"/>
        <!-- Output nicely -->
        <xsl:choose>
            <xsl:when test="$month='01'">January</xsl:when>
            <xsl:when test="$month='02'">February</xsl:when>
            <xsl:when test="$month='03'">March</xsl:when>
            <xsl:when test="$month='04'">April</xsl:when>
            <xsl:when test="$month='05'">May</xsl:when>
            <xsl:when test="$month='06'">June</xsl:when>
            <xsl:when test="$month='07'">July</xsl:when>
            <xsl:when test="$month='08'">August</xsl:when>
            <xsl:when test="$month='09'">September</xsl:when>
            <xsl:when test="$month='10'">October</xsl:when>
            <xsl:when test="$month='11'">November</xsl:when>
            <xsl:when test="$month='12'">December</xsl:when>
            <xsl:otherwise/>
        </xsl:choose>
        <xsl:text>&#160;</xsl:text>
        <xsl:value-of select="$day2"/>
        <xsl:text>,&#160;</xsl:text>
        <xsl:value-of select="$year"/>
    </xsl:template>

</xsl:stylesheet>