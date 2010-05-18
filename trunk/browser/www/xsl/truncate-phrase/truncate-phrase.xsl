<?xml version="1.0" encoding='UTF-8'?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"   
		xmlns="http://www.w3.org/1999/xhtml"
                xmlns:doc="http://xsltsl.org/xsl/documentation/1.0"
                exclude-result-prefixes="doc"
		version = "1.0">

     <xsl:output method="xml"/>

<!-- ====================================================================== 

     ====================================================================== -->

     <doc:reference xmlns="">
      <referenceinfo>
       <releaseinfo role="meta">
	<![CDATA[$Id: truncate-phrase.xsl,v 1.1 2005/09/21 13:46:39 asc Exp $]]>
       </releaseinfo>
       <author>
	<surname>Cope</surname>
        <othername>Straup</othername>
	<firstname>Aaron</firstname>
       </author>
       <copyright>
	<year>2005</year>
	<holder>Aaron Straup Cope</holder>
       </copyright>
       <legalnotice>
        <para>Permission to use, copy, modify and distribute this
      stylesheet and its accompanying documentation for any purpose
      and without fee is hereby granted in perpetuity, provided that
      the above copyright notice and this paragraph appear in all
      copies.  The copyright holders make no representation about the
      suitability of the stylesheet for any purpose.</para>
       </legalnotice> 
     </referenceinfo>

      <title>truncate-phrase.xsl</title>

      <partintro>
       <section>
        <title>Introduction</title>

	<para>The truncate phrase stylesheet
       defines a single public template that will truncate a phrase
       at a maximum length, with the option to truncate on a word
       boundary.</para>

       <para>This template is not internationalized and assumes a
       left-to-right writing orientation. Patches are welcome.</para>
       </section>

      </partintro>

     </doc:reference>

<!-- ====================================================================== 

     ====================================================================== -->

     <doc:template xmlns="">
      <refentry>
       <refnamediv>
        <refname>truncate-phrase</refname>
        <refpurpose>Truncate a phrase at a maximum length, with the
      option to truncate on a word boundary.</refpurpose>
       </refnamediv>

       <refsynopsisdiv>
        <example>
        <title>Using the truncate-phrase template in your stylesheet</title>
	<programlisting><![CDATA[

 <xsl:include href="/path/to/truncate-phrase.xsl" />

 <xsl:call-template name="truncate_phrase">
  <xsl:with-param name="phrase">
   <xsl:value-of select="/h:html/h:head/h:link[@rel='next']/@title" />
  </xsl:with-param>
  <xsl:with-param name="length" select="25" />
  <xsl:with-param name="truncate_to_word_boundary" select="1" />
 </xsl:call-template>
        ]]></programlisting>
	</example>
       </refsynopsisdiv>

       <refsection>
        <title>Parameters</title>

       <variablelist>

        <varlistentry>
	 <term>phrase</term>
	 <listitem>
	  <para>The phrase to be truncated.</para>
         </listitem>
	</varlistentry>
        <varlistentry>
	 <term>length</term>
	 <listitem>
	  <para>The number of characters to truncate the phrase to.</para>
         </listitem>
	</varlistentry>
        <varlistentry>
	 <term>truncate_to_word_boundary</term>
	 <listitem>
	  <para>A boolean parameter to indicate whether or not the
       phrase should be truncated on a word boundary. Default is false.</para>
         </listitem>
	</varlistentry>
        <varlistentry>
	 <term>trailing_string</term>
	 <listitem>
	  <para>The string to append to the truncated phrase. Default
       is &#8220;...&#8221;</para>
         </listitem>
	</varlistentry>
       </variablelist>
      </refsection>
      </refentry>
     </doc:template>

<!-- ====================================================================== 
     
     ====================================================================== -->

     <xsl:template name="truncate_phrase">
      <xsl:param name="phrase" />
      <xsl:param name="length" />
      <xsl:param name="trailing_string" select="'...'" />
      <xsl:param name="truncate_to_word_boundary" select="0" />

      <xsl:choose>
       <xsl:when test="string-length($phrase)>number($length)">
        <xsl:choose>
	 <xsl:when test="$truncate_to_word_boundary">
          <xsl:call-template name="truncate_to_word_boundary">
	   <xsl:with-param name="str">
	    <xsl:value-of select="substring($phrase,0,number($length))" />
	   </xsl:with-param>
	  </xsl:call-template>
	 </xsl:when>
	 <xsl:otherwise>
	  <xsl:value-of select="substring($phrase,0,number($length))" />
	 </xsl:otherwise>
	</xsl:choose>
	<xsl:value-of select="$trailing_string" />
       </xsl:when>
       <xsl:otherwise>
        <xsl:value-of select="$phrase" />
       </xsl:otherwise>
      </xsl:choose>
     </xsl:template>

<!-- ====================================================================== 
     
     ====================================================================== -->

     <xsl:template name="truncate_to_word_boundary">
      <xsl:param name="str" />
      <xsl:variable name="ret" select="substring($str,0,string-length($str))" />

      <xsl:choose>
       <xsl:when test="$str=''" />
       <xsl:when test="substring($str,string-length($str),1)=' '">
	<xsl:value-of select="$ret" />
       </xsl:when>
       <xsl:otherwise>
        <xsl:call-template name="truncate_to_word_boundary">
         <xsl:with-param name="str">
          <xsl:value-of select="$ret" />
         </xsl:with-param>
        </xsl:call-template>
       </xsl:otherwise>
      </xsl:choose>
     </xsl:template>

<!-- ====================================================================== 
     $Id: truncate-phrase.xsl,v 1.1 2005/09/21 13:46:39 asc Exp $
     ====================================================================== -->

</xsl:stylesheet>
