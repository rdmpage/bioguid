<?xml version='1.0' encoding='utf-8'?>

<!-- 
    $Log: $
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
version="1.0">

	<xsl:output method="text" version="1.0" encoding="utf-8" indent="yes"/>


    <xsl:template match="/">

<xsl:text>{&#x0D;</xsl:text>


    <xsl:apply-templates select="PubmedArticleSet/PubmedArticle/PubmedData/ArticleIdList"/>
<xsl:apply-templates select="PubmedArticleSet/PubmedArticle/MedlineCitation/Article"/>
<xsl:text>&#x0D;}</xsl:text>
</xsl:template>

 <xsl:template match="PubmedArticleSet/PubmedArticle/MedlineCitation/Article">

	<!-- authors -->
	<xsl:text>&#x0D;&#x09;&#x09;"authors":[</xsl:text>
    <xsl:apply-templates select="AuthorList/Author"/>
	<xsl:text>&#x0D;&#x09;&#x09;],&#x0D;</xsl:text>
 

	<!-- article title -->
	<xsl:text>&#x09;&#x09;&#x09;"atitle":"</xsl:text>

		<xsl:variable name="atitle" select="ArticleTitle"/>
		<xsl:call-template name="cleanQuote">
			<xsl:with-param name="string" select="$atitle"/>
		</xsl:call-template>
	<xsl:text>"</xsl:text>

	<!-- abstract -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;&#x09;&#x09;"abstract":"</xsl:text>
		<xsl:variable name="abstract" select="Abstract/AbstractText"/>
		<xsl:call-template name="cleanQuote">
			<xsl:with-param name="string" select="$abstract"/>
		</xsl:call-template>
	<xsl:text>"</xsl:text>

	<!-- journal -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;&#x09;&#x09;"title":"</xsl:text>
	<xsl:value-of select="Journal/Title"/>
	<xsl:text>"</xsl:text>

	<xsl:apply-templates select="Journal/ISSN"/>

	<!-- year of publication -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;&#x09;&#x09;"year":"</xsl:text>
	<xsl:value-of select="Journal/JournalIssue/PubDate/Year"/>
	<xsl:text>"</xsl:text>

	<!-- date of publication -->
	<xsl:apply-templates select="Journal/JournalIssue/PubDate" />


	<!-- volume -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;&#x09;&#x09;"volume":"</xsl:text>
	<xsl:value-of select="Journal/JournalIssue/Volume"/>
	<xsl:text>"</xsl:text>

	<!-- issue -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;&#x09;&#x09;"issue":"</xsl:text>
	<xsl:value-of select="Journal/JournalIssue/Issue"/>
	<xsl:text>"</xsl:text>

	<!-- spage -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;&#x09;&#x09;"spage":"</xsl:text>
	<xsl:value-of select="substring-before(Pagination/MedlinePgn, '-')"/>	<xsl:text>"</xsl:text>

</xsl:template>


    <!-- Identifiers  -->
    <xsl:template match="PubmedArticleSet/PubmedArticle/PubmedData/ArticleIdList">
<!--		<xsl:text>&#x09;&#x09;"identifiers":{</xsl:text>     -->   
			<xsl:apply-templates select="ArticleId"/>
<!--		<xsl:text>&#x0D;&#x09;&#x09;&#x09;},</xsl:text> -->
			<xsl:text>,</xsl:text>
    </xsl:template>

    <xsl:template match="ArticleId">
		<xsl:choose>
			<xsl:when test="@IdType='pubmed'">
				<xsl:if test="position() != 1"><xsl:text>,</xsl:text></xsl:if>
<xsl:text>&#x0D;</xsl:text>
				<xsl:text>&#x09;&#x09;&#x09;"pmid":"</xsl:text>
				<xsl:value-of select="." />
				<xsl:text>"</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:if test="position() != 1"><xsl:text>,</xsl:text></xsl:if>
<xsl:text>&#x0D;</xsl:text>
				
				<xsl:text>&#x09;&#x09;&#x09;"</xsl:text>
<xsl:value-of select="@IdType" />
				<xsl:text>":"</xsl:text>
				<xsl:value-of select="." />
				<xsl:text>"</xsl:text>
			</xsl:otherwise>
		</xsl:choose>

 
    </xsl:template>

<xsl:template match="ISSN">
	<xsl:if test="@IssnType='Print'">
		<xsl:text>,&#x0D;</xsl:text>
		<xsl:text>&#x09;&#x09;&#x09;"issn":"</xsl:text>
		<xsl:value-of select="."/>
		<xsl:text>"</xsl:text>
	</xsl:if>
</xsl:template>

<xsl:template match="PubDate">
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;&#x09;&#x09;"date":"</xsl:text>
	<xsl:value-of select="Year"/>
	<xsl:text>-</xsl:text>
	<xsl:variable name="mo" select="Month" />
	<xsl:choose>
<xsl:when test="$mo = 'Jan'">01</xsl:when>
<xsl:when test="$mo = 'Feb'">02</xsl:when>
<xsl:when test="$mo = 'Mar'">03</xsl:when>
<xsl:when test="$mo = 'Apr'">04</xsl:when>
<xsl:when test="$mo = 'May'">05</xsl:when>
<xsl:when test="$mo = 'Jun'">06</xsl:when>
<xsl:when test="$mo = 'Jul'">07</xsl:when>
<xsl:when test="$mo = 'Aug'">08</xsl:when>
<xsl:when test="$mo = 'Sep'">09</xsl:when>
<xsl:when test="$mo = 'Oct'">10</xsl:when>
<xsl:when test="$mo = 'Nov'">11</xsl:when>
<xsl:when test="$mo = 'Dec'">12</xsl:when>
    </xsl:choose>
	<xsl:text>-</xsl:text>
	<xsl:value-of select="Day" />
	<xsl:text>"</xsl:text>

</xsl:template>

   <xsl:template match="Author">

<xsl:if test="position() != 1"><xsl:text>,</xsl:text></xsl:if>
<xsl:text>&#x0D;</xsl:text>

<xsl:text>&#x09;&#x09;&#x09;{</xsl:text>				
<xsl:text>"lastname":"</xsl:text><xsl:value-of select="LastName"/><xsl:text>",</xsl:text>
<xsl:text>"forename":"</xsl:text><xsl:value-of select="ForeName"/><xsl:text>"</xsl:text>
<xsl:text>}</xsl:text>		
    </xsl:template>

    <!-- From http://www.dpawson.co.uk/xsl/sect2/StringReplace.html#d10992e82 -->
<xsl:template name="cleanQuote">
<xsl:param name="string" />
<xsl:if test="contains($string, '&#x22;')"><xsl:value-of
    select="substring-before($string, '&#x22;')" />\"<xsl:call-template
    name="cleanQuote">
                <xsl:with-param name="string"><xsl:value-of
select="substring-after($string, '&#x22;')" />
                </xsl:with-param>
        </xsl:call-template>
</xsl:if>
<xsl:if test="not(contains($string, '&#x22;'))"><xsl:value-of
select="$string" />
</xsl:if>
</xsl:template>

</xsl:stylesheet>

