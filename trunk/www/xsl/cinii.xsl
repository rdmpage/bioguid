<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:prism="http://prismstandard.org/namespaces/basic/2.0/"
    xmlns:foaf="http://xmlns.com/foaf/0.1/">

    <xsl:output method="text" version="1.0" encoding="utf-8" indent="no"/>

    <xsl:template match="/">
        <xsl:text>{
</xsl:text>

        <!-- url -->
        <xsl:text>&#x09;&#x09;"url":"</xsl:text>
        <xsl:value-of select="substring-before(/rdf:RDF/rdf:Description[1]/@rdf:about, '#')"/>
        <xsl:text>"</xsl:text>

        <xsl:apply-templates select="/rdf:RDF/rdf:Description"/>
        <xsl:text>
}</xsl:text>

    </xsl:template>

    <xsl:template match="/rdf:RDF/rdf:Description">

        <xsl:apply-templates select="prism:volume"/>
        <xsl:apply-templates select="prism:number"/>
        <xsl:apply-templates select="prism:issn"/>
        <xsl:apply-templates select="prism:publicationDate"/>
        <xsl:apply-templates select="prism:startingPage"/>
        <xsl:apply-templates select="prism:endingPage"/>
        <xsl:apply-templates select="prism:pageRange"/>

        <!-- English details name -->
        <xsl:if test="@xml:lang = 'en'">
            <xsl:text>,&#xD;&#x09;&#x09;"atitle":"</xsl:text>

            <xsl:variable name="atitle" select="dc:title"/>
            <xsl:call-template name="cleanQuote">
                <xsl:with-param name="string" select="$atitle"/>
            </xsl:call-template>
            <xsl:text>"</xsl:text>

            <xsl:text>,&#xD;&#x09;&#x09;"title":"</xsl:text>
            <xsl:value-of select="prism:publicationName"/>
            <xsl:text>"</xsl:text>

            <xsl:text>,&#xD;&#09;&#09;"authors":[</xsl:text>
            <xsl:apply-templates select="dc:creator"/>
            <xsl:text>&#xD;&#09;&#09;]</xsl:text>
            
            <!-- abstract -->
            <xsl:apply-templates select="dc:description"/>
        </xsl:if>

    </xsl:template>

    <!-- output actual publication date and year -->
    <xsl:template match="prism:publicationDate">
        <!-- date -->
        <xsl:text>,&#xD;&#x09;&#x09;"date":"</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>"</xsl:text>

        <!-- year -->
        <xsl:text>,&#xD;&#09;&#09;"year":"</xsl:text>
        <xsl:choose>
            <xsl:when test="contains(., '-')">
                <!-- assume date is YYYY-MM-DD -->
                <xsl:value-of select="substring(., 1, 4)"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="."/>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:text>"</xsl:text>

    </xsl:template>

    <xsl:template match="prism:startingPage">
        <xsl:text>,&#xD;&#x09;&#x09;"spage":"</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>"</xsl:text>
    </xsl:template>

    <xsl:template match="prism:endingPage">
        <xsl:text>,&#xD;&#x09;&#x09;"epage":"</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>"</xsl:text>
    </xsl:template>

    <!-- I think this will just have a single page, but best be careful -->
    <xsl:template match="prism:pageRange">
        <xsl:choose>
            <xsl:when test="contains(., '-')">
                <xsl:text>,&#xD;&#x09;&#x09;"spage":"</xsl:text>
                <xsl:value-of select="substring-before(.,',')"/>
                <xsl:text>"</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>,&#xD;&#x09;&#x09;"spage":"</xsl:text>
                <xsl:value-of select="."/>
                <xsl:text>"</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="prism:volume">
        <xsl:text>,&#xD;&#x09;&#x09;"volume":"</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>"</xsl:text>
    </xsl:template>

    <xsl:template match="prism:number">
        <xsl:text>,&#xD;&#x09;&#x09;"issue":"</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>"</xsl:text>
    </xsl:template>

    <xsl:template match="dc:description">
        <xsl:text>,&#xD;&#x09;&#x09;"abstract":"</xsl:text>
	   <xsl:variable name="abstract" select="."/>
		<xsl:call-template name="cleanQuote">
			<xsl:with-param name="string" select="$abstract"/>
		</xsl:call-template>
		<xsl:text>"</xsl:text>
     </xsl:template>

    <xsl:template match="prism:issn">
        <xsl:text>,&#xD;&#09;&#09;"issn":"</xsl:text>
        <xsl:choose>
            <xsl:when test="contains(., '-')">
                <xsl:value-of select="."/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="substring(., 1, 4)"/>
                <xsl:text>-</xsl:text>
                <xsl:value-of select="substring(., 5, 4)"/>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:text>"</xsl:text>
    </xsl:template>

    <!-- authors -->
    <xsl:template match="dc:creator">
        <xsl:if test="position() != 1">
            <xsl:text>,</xsl:text>
        </xsl:if>

        <xsl:text>&#xD;&#09;&#09;&#09;{</xsl:text>

        <xsl:text>"author":"</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>"</xsl:text>
        <xsl:text>}</xsl:text>

    </xsl:template>

    <!-- From http://www.dpawson.co.uk/xsl/sect2/StringReplace.html#d10992e82 -->
    <xsl:template name="cleanQuote">
        <xsl:param name="string"/>
        <xsl:if test="contains($string, '&#x22;')">
            <xsl:value-of select="substring-before($string, '&#x22;')"/>
            <xsl:text>\"</xsl:text>
            <xsl:call-template name="cleanQuote">
                <xsl:with-param name="string">
                    <xsl:value-of select="substring-after($string, '&#x22;')"/>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="not(contains($string, '&#x22;'))">
            <xsl:value-of select="$string"/>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>
