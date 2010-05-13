<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
xmlns:dwrec="http://rs.tdwg.org/dwc/dwrecord" xmlns:dwcore="http://rs.tdwg.org/dwc/dwcore/" xmlns:dwgeo="http://rs.tdwg.org/dwc/geospatial/" xmlns:dwcur="http://rs.tdwg.org/dwc/curatorial/"

>

<xsl:output method="text" encoding="utf-8" indent="yes"/>

<xsl:template match="/">
       <xsl:text>{&#x0D;</xsl:text>
        <xsl:text>&#x09;"status" : "ok",&#x0D;</xsl:text>
        <xsl:text>&#x09;"record" :[&#x0D;</xsl:text>
        <xsl:apply-templates select="//dwrec:DarwinRecordSet"/>
        <xsl:text>&#x0D;&#x09;]</xsl:text>
        <xsl:text>&#x0D;}</xsl:text>

</xsl:template>

   <xsl:template match="//dwrec:DarwinRecordSet">
        <xsl:apply-templates select="//dwrec:DarwinRecord"/>
 	</xsl:template>


   <xsl:template match="//dwrec:DarwinRecord">
  		<xsl:if test="position() != 1"><xsl:text>,&#x0D;</xsl:text></xsl:if>
       <xsl:text>&#x09;&#x09;{</xsl:text>
      
        <xsl:text>&#x0D;&#x09;&#x09;&#x09;"guid":"</xsl:text>
 		<xsl:value-of select="dwcore:GlobalUniqueIdentifier"/>
		<xsl:text>"</xsl:text>

           <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"dateModified":"</xsl:text>
            <xsl:value-of select="dwcore:DateLastModified"/>
            <xsl:text>"</xsl:text>

          <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"collectionCode":"</xsl:text>
            <xsl:value-of select="dwcore:CollectionCode"/>
            <xsl:text>"</xsl:text>

         <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"institutionCode":"</xsl:text>
            <xsl:value-of select="dwcore:InstitutionCode"/>
            <xsl:text>"</xsl:text>

        <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"organism":"</xsl:text>
            <xsl:value-of select="dwcore:ScientificName"/>
            <xsl:text>"</xsl:text>


          <xsl:text>,&#x0D;</xsl:text>
          <xsl:text>&#x09;&#x09;&#x09;"catalogNumber":"</xsl:text>
          <xsl:value-of select="dwcore:CatalogNumber"/>
          <xsl:text>"</xsl:text>

			<!-- locality -->
          <xsl:text>,&#x0D;</xsl:text>
          <xsl:text>&#x09;&#x09;&#x09;"country":"</xsl:text>
          <xsl:value-of select="dwcore:Country"/>
          <xsl:text>"</xsl:text>

         <xsl:text>,&#x0D;</xsl:text>
          <xsl:text>&#x09;&#x09;&#x09;"stateProvince":"</xsl:text>
          <xsl:value-of select="dwcore:StateProvince"/>
          <xsl:text>"</xsl:text>

		<!--
         <xsl:text>,&#x0D;</xsl:text>
          <xsl:text>&#x09;&#x09;&#x09;"county":"</xsl:text>
          <xsl:value-of select="dwcore:County"/>
          <xsl:text>"</xsl:text>

         <xsl:text>,&#x0D;</xsl:text>
          <xsl:text>&#x09;&#x09;&#x09;"locality":"</xsl:text>
          <xsl:value-of select="dwcore:Locality"/>
          <xsl:text>"</xsl:text>
		-->

        <xsl:text>,&#x0D;</xsl:text>
          <xsl:text>&#x09;&#x09;&#x09;"longitude":"</xsl:text>
          <xsl:value-of select="dwgeo:DecimalLongitude"/>
          <xsl:text>"</xsl:text>
        <xsl:text>,&#x0D;</xsl:text>
          <xsl:text>&#x09;&#x09;&#x09;"latitude":"</xsl:text>
          <xsl:value-of select="dwgeo:DecimalLatitude"/>
          <xsl:text>"</xsl:text>

       <xsl:text>,&#x0D;</xsl:text>
          <xsl:text>&#x09;&#x09;&#x09;"collector":"</xsl:text>
          <xsl:value-of select="dwcore:Collector"/>
          <xsl:text>"</xsl:text>

      <xsl:text>&#x0D;&#x09;&#x09;}</xsl:text>


 	</xsl:template>



</xsl:stylesheet>