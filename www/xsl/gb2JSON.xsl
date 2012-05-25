<?xml version="1.0"?>

<!-- $Id:  $ -->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
version="1.0">

	<xsl:output method="text" version="1.0" encoding="utf-8" indent="yes"/>


	<xsl:template match="/">

<xsl:text>{&#x0D;</xsl:text>

<xsl:apply-templates select="//GBSeq_other-seqids/GBSeqid"/>

<!-- accession -->
	<xsl:text>&#x09;"accession":"</xsl:text>
	<xsl:value-of select="//GBSeq_primary-accession"/>
	<xsl:text>"</xsl:text>

<!-- accession as title -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;"title":"</xsl:text>
	<xsl:value-of select="//GBSeq_primary-accession"/>
	<xsl:text>"</xsl:text>

	
<!-- version -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;"version":"</xsl:text>
	<xsl:value-of select="//GBSeq_accession-version"/>
	<xsl:text>"</xsl:text>
	
<!-- create date -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;"created":"</xsl:text>
	<xsl:value-of select="//GBSeq_create-date"/>
	<xsl:text>"</xsl:text>
	
<!-- update date -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;"updated":"</xsl:text>
	<xsl:value-of select="//GBSeq_update-date"/>
	<xsl:text>"</xsl:text>

<!-- definition -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;"description":"</xsl:text>
	<xsl:value-of select="//GBSeq_definition"/>
	<xsl:text>"</xsl:text>

<!-- taxonomy -->
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;"taxonomy":"</xsl:text>
	<xsl:value-of select="//GBSeq_taxonomy"/>
	<xsl:text>"</xsl:text>

<!-- references -->
<xsl:text>,&#x0D;</xsl:text>
<xsl:apply-templates select="//GBSeq_references"/>

<!-- source -->
<xsl:apply-templates select="//GBFeature[1]/GBFeature_quals"/> 

<!-- features -->
<xsl:text>,&#x0D;&#x09;&#x09;"features": [</xsl:text>
<xsl:apply-templates select="//GBFeature"/> 
<xsl:text>&#x0D;&#x09;&#x09;]</xsl:text>

<!-- sequence -->
<xsl:text>,&#x0D;&#x09;&#x09;"sequence": "</xsl:text>
<xsl:value-of select="//GBSeq_sequence"/>
<xsl:text>"&#x0D;&#x09;</xsl:text>

<xsl:text>&#x0D;}</xsl:text>


</xsl:template>

	<xsl:template match="//GBFeature[1]/GBFeature_quals">
<!-- source -->
<xsl:text>,&#x0D;</xsl:text>
<xsl:text>&#x09;&#x09;"source": {</xsl:text>
<xsl:apply-templates select="GBQualifier"/>
<xsl:text>&#x0D;&#x09;&#x09;}</xsl:text>

	</xsl:template>


	<xsl:template match="GBQualifier">
<xsl:if test="position() != 1"><xsl:text>,</xsl:text></xsl:if>

	<xsl:text>&#x0D;</xsl:text>
<xsl:text>&#x09;&#x09;&#x09;"</xsl:text>
<xsl:apply-templates select="GBQualifier_name"/>		<xsl:text>":"</xsl:text>
<xsl:apply-templates select="GBQualifier_value"/>
<xsl:text>"</xsl:text>

	</xsl:template>


	<xsl:template match="GBSeq_other-seqids/GBSeqid">
		<xsl:if test="contains(., 'gi|')">
			<xsl:text>&#x09;"gi":"</xsl:text>
			<xsl:value-of select="substring-after(., 'gi|')"/>
			<xsl:text>",&#x0D;</xsl:text>    
		</xsl:if>
	</xsl:template>

	<xsl:template match="GBSeq_references">
		<xsl:text>&#x09;"references":[&#x0D;</xsl:text> 
		<!--	<xsl:apply-templates select="GBReference[1]"/> -->

		<xsl:apply-templates select="GBReference"/>

		<xsl:text>&#x09;]&#x0D;</xsl:text>		
	</xsl:template>

	<xsl:template match="GBReference">
<xsl:if test="position() != 1"><xsl:text>,</xsl:text></xsl:if>
<xsl:text>&#x0D;</xsl:text>
		<xsl:text>&#x09;&#x09;{&#x0D;</xsl:text>


	<xsl:apply-templates select="GBReference_authors"/>

	<xsl:text>&#x09;&#x09;&#x09;"atitle":"</xsl:text>
		<xsl:value-of select="GBReference_title"/>
	<xsl:text>"</xsl:text>

	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;&#x09;&#x09;"bibliographicCitation":"</xsl:text>
		<xsl:value-of select="GBReference_journal"/>
	<xsl:text>"</xsl:text>

	<xsl:if test="GBReference_pubmed != ''">
	<xsl:text>,&#x0D;</xsl:text>
	<xsl:text>&#x09;&#x09;&#x09;"pmid":"</xsl:text>
		<xsl:value-of select="GBReference_pubmed"/>
	<xsl:text>"</xsl:text>
	</xsl:if>

	<xsl:apply-templates select="GBReference_xref"/>
		
	<xsl:text>&#x09;&#x09;}</xsl:text>		
	</xsl:template>

	<xsl:template match="GBReference_authors">
		<xsl:text>&#x09;&#x09;&#x09;"authors":[</xsl:text>
			<xsl:apply-templates select="GBAuthor"/>		<xsl:text>&#x0D;&#x09;&#x09;&#x09;]</xsl:text>			<xsl:text>,&#x0D;</xsl:text>

	</xsl:template>

	<xsl:template match="GBAuthor">
<xsl:if test="position() != 1"><xsl:text>,</xsl:text></xsl:if>
<xsl:text>&#x0D;</xsl:text>

<xsl:text>{</xsl:text>
<xsl:text>"lastname":"</xsl:text><xsl:value-of select="substring-before(., ',')"/><xsl:text>",</xsl:text>
<xsl:text>"forename":"</xsl:text><xsl:value-of select="substring-after(., ',')"/><xsl:text>"</xsl:text>
<xsl:text>}</xsl:text>


	</xsl:template>

	<xsl:template match="GBReference_xref">
<xsl:apply-templates select="GBXref"/>
	</xsl:template>

	<xsl:template match="GBXref">
<xsl:text>,&#x0D;</xsl:text>

<xsl:text>&#x09;&#x09;&#x09;"</xsl:text>
<xsl:apply-templates select="GBXref_dbname"/>		<xsl:text>":"</xsl:text>
<xsl:apply-templates select="GBXref_id"/>
<xsl:text>"</xsl:text>
	</xsl:template>


	<xsl:template match="GBFeature">
<!--
	<xsl:choose>

		<xsl:when test="GBFeature_key='operon'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"intron", </xsl:text>	

			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>

			<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'number'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>



<xsl:text>"}</xsl:text>
		</xsl:when>

		<xsl:when test="GBFeature_key='intron'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"intron", </xsl:text>	

			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>

			<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'number'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>



<xsl:text>"}</xsl:text>
		</xsl:when>


		<xsl:when test="GBFeature_key='misc_RNA'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"misc_RNA", </xsl:text>	

			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>

			<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'number'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>



<xsl:text>"}</xsl:text>
		</xsl:when>

		<xsl:when test="GBFeature_key='repeat_region'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"repeat_region", </xsl:text>	

			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>

			<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'number'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>



<xsl:text>"}</xsl:text>
		</xsl:when>


		<xsl:when test="GBFeature_key='D-loop'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"D-loop", </xsl:text>	

			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>

			<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'number'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>



<xsl:text>"}</xsl:text>
		</xsl:when>


		<xsl:when test="GBFeature_key='intron'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"intron", </xsl:text>	

			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>

			<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'number'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>



<xsl:text>"}</xsl:text>
		</xsl:when>

		<xsl:when test="GBFeature_key='exon'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"exon", </xsl:text>	

			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>

			<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'number'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>



<xsl:text>"}</xsl:text>
		</xsl:when>
		<xsl:when test="GBFeature_key='tRNA'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"tRNA", </xsl:text>	


			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>

			<xsl:text>"name":"</xsl:text><xsl:value-of select="GBFeature_quals/GBQualifier[1]/GBQualifier_value" /><xsl:text>"}</xsl:text>
		</xsl:when>


		<xsl:when test="GBFeature_key='rRNA'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"rRNA", </xsl:text>	


			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>

			<xsl:text>"name":"</xsl:text><xsl:value-of select="GBFeature_quals/GBQualifier[1]/GBQualifier_value" /><xsl:text>"}</xsl:text>
		</xsl:when>

		<xsl:when test="GBFeature_key='gene'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"gene", </xsl:text>	

			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>


			<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'gene'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>


<xsl:text>"}</xsl:text>
		</xsl:when>


		<xsl:when test="GBFeature_key='mRNA'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"mRNA", </xsl:text>	

			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>


			<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'product'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>


<xsl:text>"}</xsl:text>
		</xsl:when>

		<xsl:when test="GBFeature_key='CDS'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"CDS", </xsl:text>	

			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>


			<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'product'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>

			<xsl:text>"}</xsl:text>

		</xsl:when>

		<xsl:when test="GBFeature_key='misc_feature'">
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"key":"misc_feature", </xsl:text>


			<xsl:text>"location":"</xsl:text><xsl:value-of select="GBFeature_location"/><xsl:text>",</xsl:text>



				<xsl:text>"name":"</xsl:text>

		    <xsl:for-each select="GBFeature_quals/GBQualifier/GBQualifier_value">
      			<xsl:if test="../GBQualifier_name = 'note'"> 
        			<xsl:value-of select="."/>
    			</xsl:if> 
    		</xsl:for-each>

			<xsl:text>"}</xsl:text>

		</xsl:when>


		<xsl:otherwise>
			<xsl:if test="position() != 2"><xsl:text>,&#x0D;</xsl:text></xsl:if>
			<xsl:text>{</xsl:text>
			<xsl:text>"}</xsl:text>

		</xsl:otherwise>

	</xsl:choose>

-->
	</xsl:template>





</xsl:stylesheet>
