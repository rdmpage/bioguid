<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xlink="http://www.w3.org/1999/xlink">

    <xsl:output method="text" encoding="utf-8" indent="yes"/>
    <xsl:template match="/">
        <xsl:text>{&#xD;</xsl:text>
        <xsl:apply-templates select="//front/article-meta"/>
        <xsl:text>&#xD;}</xsl:text>
    </xsl:template>

    <xsl:template match="//front/article-meta">

        <xsl:text>&#09;&#09;"publisher_id":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="article-id"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <xsl:text>&#xD;&#09;&#09;"title":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="//journal-title"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <xsl:text>&#xD;&#09;&#09;"issn":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="//front/journal-meta/journal-id"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <!-- construct DOI from article id -->
        <xsl:choose>
            <xsl:when test="//front/journal-meta/journal-id = '0073-4721'">
                <xsl:text>&#xD;&#09;&#09;"doi":</xsl:text>
                <xsl:text>"10.1590/</xsl:text>
                <xsl:value-of select="article-id"/>
                <xsl:text>"</xsl:text>
                <xsl:text>,</xsl:text>
            </xsl:when>
            <xsl:when test="//front/journal-meta/journal-id = '1679-6225'">
                <xsl:text>&#xD;&#09;&#09;"doi":</xsl:text>
                <xsl:text>"10.1590/</xsl:text>
                <xsl:value-of select="article-id"/>
                <xsl:text>"</xsl:text>
                <xsl:text>,</xsl:text>
            </xsl:when>
            <xsl:when test="//front/journal-meta/journal-id = '0101-8175'">
                <xsl:text>&#xD;&#09;&#09;"doi":</xsl:text>
                <xsl:text>"10.1590/</xsl:text>
                <xsl:value-of select="article-id"/>
                <xsl:text>"</xsl:text>
                <xsl:text>,</xsl:text>
            </xsl:when>
            <xsl:when test="//front/journal-meta/journal-id = '0716-078X'">
                <xsl:text>&#xD;&#09;&#09;"doi":</xsl:text>
                <xsl:text>"10.4067/</xsl:text>
                <xsl:value-of select="article-id"/>
                <xsl:text>"</xsl:text>
                <xsl:text>,</xsl:text>
            </xsl:when>
             <!-- default DOI prefix 10.1590 -->
            <xsl:otherwise>
                <xsl:text>&#xD;&#09;&#09;"doi":</xsl:text>
                <xsl:text>"10.1590/</xsl:text>
                <xsl:value-of select="article-id"/>
                <xsl:text>"</xsl:text>
                <xsl:text>,</xsl:text>
            </xsl:otherwise>
        </xsl:choose>

        <xsl:text>&#xD;&#09;&#09;"atitle":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="//title-group/article-title"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <xsl:text>&#xD;&#09;&#09;"volume":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="//volume"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <xsl:text>&#xD;&#09;&#09;"issue":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="//numero"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <xsl:text>&#xD;&#09;&#09;"spage":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="//fpage"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <xsl:text>&#xD;&#09;&#09;"epage":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="//lpage"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <xsl:text>&#xD;&#09;&#09;"year":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="//pub-date[@pub-type='pub']/year"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <xsl:text>&#xD;&#09;&#09;"date":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="//pub-date[@pub-type='pub']/year"/>
        <xsl:text>-</xsl:text>
        <xsl:value-of select="//pub-date[@pub-type='pub']/month"/>
        <xsl:text>-</xsl:text>
        <xsl:value-of select="//pub-date[@pub-type='pub']/day"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>


        <xsl:text>&#xD;&#09;&#09;"abstract":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:variable name="abstract" select="//abstract"/>
        <xsl:call-template name="cleanQuote">
            <xsl:with-param name="string" select="$abstract"/>
        </xsl:call-template>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <xsl:text>&#xD;&#09;&#09;"url":</xsl:text>
        <xsl:text>"</xsl:text>
        <xsl:value-of select="//self-uri[2]/@xlink:href"/>
        <xsl:text>"</xsl:text>
        <xsl:text>,</xsl:text>

        <!-- author list -->
        <xsl:apply-templates select="//contrib-group"/>
    </xsl:template>

    <xsl:template match="//contrib-group">
        <xsl:text>&#xD;&#09;&#09;"authors":[</xsl:text>
        <xsl:apply-templates select="contrib"/>
        <xsl:text>&#xD;&#09;&#09;]</xsl:text>
    </xsl:template>

    <xsl:template match="contrib">
        <xsl:if test="position() != 1">
            <xsl:text>,</xsl:text>
        </xsl:if>

        <xsl:text>&#xD;&#09;&#09;&#09;{</xsl:text>
        <xsl:text>"lastname":"</xsl:text>
        <xsl:value-of select="name/surname"/>
        <xsl:text>",</xsl:text>
        <xsl:text>"forename":"</xsl:text>
        <xsl:value-of select="name/given-names"/>
        <xsl:text>"</xsl:text>
        <xsl:text>}</xsl:text>

    </xsl:template>


    <!-- From http://www.dpawson.co.uk/xsl/sect2/StringReplace.html#d10992e82 -->
    <xsl:template name="cleanQuote">
        <xsl:param name="string"/>
        <xsl:if test="contains($string, '&#x22;')"><xsl:value-of
                select="substring-before($string, '&#x22;')"/>\"<xsl:call-template
                name="cleanQuote">
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
