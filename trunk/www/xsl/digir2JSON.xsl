<?xml version='1.0' encoding='utf-8'?>

<!-- Id: $ -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:dwc="http://digir.net/schema/conceptual/darwin/2003/1.0" xmlns:darwin="http://digir.net/schema/conceptual/darwin/2003/1.0">
    <xsl:output method="text" encoding="utf-8" indent="yes"/>

<!-- from http://aspn.activestate.com/ASPN/Cookbook/XSLT/Recipe/65426 -->
<!-- reusable replace-string function -->
 <xsl:template name="replace-string">
    <xsl:param name="text"/>
    <xsl:param name="from"/>
    <xsl:param name="to"/>

    <xsl:choose>
      <xsl:when test="contains($text, $from)">

	<xsl:variable name="before" select="substring-before($text, $from)"/>
	<xsl:variable name="after" select="substring-after($text, $from)"/>
	<xsl:variable name="prefix" select="concat($before, $to)"/>

	<xsl:value-of select="$before"/>
	<xsl:value-of select="$to"/>
        <xsl:call-template name="replace-string">
	  <xsl:with-param name="text" select="$after"/>
	  <xsl:with-param name="from" select="$from"/>
	  <xsl:with-param name="to" select="$to"/>
	</xsl:call-template>
      </xsl:when> 
      <xsl:otherwise>
        <xsl:value-of select="$text"/>  
      </xsl:otherwise>
    </xsl:choose>            
 </xsl:template>



    <xsl:template match="/">
        <xsl:text>{&#x0D;</xsl:text>
        <xsl:text>&#x09;"status" : "ok",&#x0D;</xsl:text>
        <xsl:text>&#x09;"record" :[&#x0D;</xsl:text>
        <xsl:apply-templates select="//content"/>
        <xsl:text>&#x0D;&#x09;]</xsl:text>
        <xsl:text>&#x0D;}</xsl:text>
    </xsl:template>

 
    <xsl:template match="//content">
        <xsl:apply-templates select="//record"/>
 	</xsl:template>

    <xsl:template match="//record">
 	<!--	<xsl:if test="position() != 1"><xsl:text>,&#x0D;</xsl:text></xsl:if> -->
       <xsl:text>&#x09;&#x09;{</xsl:text>
 

        <!-- specimen code and guid -->
        <!-- 1.14 of DiGIR response -->
        <xsl:if test="darwin:CatalogNumber != ''">
            <xsl:choose>
                <xsl:when test="darwin:InstitutionCode = 'CAS'">
                    <!-- AntWeb -->
                    <xsl:text>&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"title":"</xsl:text>
                    <xsl:value-of select="darwin:CatalogNumber"/>
                    <xsl:text>"</xsl:text>
                    <!-- guid -->
                    <xsl:text>,&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"guid":"</xsl:text>
                    <xsl:choose>
                        <!-- Entomology, i.e. ants.  -->
                        <xsl:when test="darwin:CollectionCode = 'ENT'">
							<xsl:choose>
                            <xsl:when test="contains(darwin:CatalogNumber, 'casent')">
                                <xsl:variable name="number" select="substring-after(darwin:CatalogNumber, 'casent')"/>
                                <xsl:variable name="guid" select="concat('casent:', $number)"/>
                                <xsl:value-of select="$guid"/>
                                <xsl:text>"</xsl:text>
                            </xsl:when>
                            <xsl:when test="contains(darwin:CatalogNumber, 'inbiocri')">
                                <xsl:variable name="number" select="substring-after(darwin:CatalogNumber, 'inbiocri')"/>
                                <xsl:variable name="guid" select="concat('inbiocri:', $number)"/>
                                <xsl:value-of select="$guid"/>
                                <xsl:text>"</xsl:text>
                            </xsl:when>
                            <xsl:when test="contains(darwin:CatalogNumber, 'jtlc')">
                                <xsl:variable name="number" select="substring-after(darwin:CatalogNumber, 'jtlc')"/>
                                <xsl:variable name="guid" select="concat('jtlc:', $number)"/>
                                <xsl:value-of select="$guid"/>
                                <xsl:text>"</xsl:text>
                            </xsl:when>
                            <xsl:when test="contains(darwin:CatalogNumber, 'lacm ent')">
                                <xsl:text>lacment:</xsl:text>
                                <xsl:value-of select="substring-after(darwin:CatalogNumber, 'lacm ent ')"/>
                                <xsl:text>"</xsl:text>
                            </xsl:when>
                           <xsl:when test="contains(darwin:CatalogNumber, 'alas')">
                                <xsl:text>alas:</xsl:text>
                                <xsl:value-of select="substring-after(darwin:CatalogNumber, 'alas')"/>
                                <xsl:text>"</xsl:text>
                            </xsl:when>
							<xsl:otherwise>
                               <xsl:text>antweb:</xsl:text>
                                <xsl:value-of select="darwin:CatalogNumber"/>
                                <xsl:text>"</xsl:text>
							</xsl:otherwise>
						</xsl:choose>

                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="darwin:CatalogNumber"/>
                            <xsl:text>"</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:variable name="code" select="concat(darwin:InstitutionCode, darwin:CatalogNumber)"/>
                    <xsl:text>&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"title":"</xsl:text>
                    <xsl:value-of select="$code"/>
                    <xsl:text>"</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>
        <xsl:if test="darwin:CatalogNumberText != ''">
			<xsl:choose>
               <!-- KU -->
                <xsl:when test="darwin:InstitutionCode = 'KU'">
                    <xsl:text>&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"title":"KU </xsl:text>
                   <xsl:value-of select="darwin:CatalogNumberText"/>

                    <xsl:text>"</xsl:text>
                    <!-- guid -->
                    <xsl:text>,&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"guid":"</xsl:text>
                    <xsl:value-of select="darwin:CollectionCode"/>
                    <xsl:text>:</xsl:text>
                    <xsl:value-of select="darwin:CatalogNumberText"/>
                    <xsl:text>"</xsl:text>
                </xsl:when>

				<!-- USNM -->
               <xsl:when test="darwin:InstitutionCode = 'USNM'">
					<!-- title -->
                    <xsl:text>&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"title":"</xsl:text>
           			<xsl:variable name="code" select="concat(darwin:InstitutionCode, ' ', darwin:CatalogNumberText)"/>
            		<xsl:value-of select="$code"/>
                   <xsl:text>"</xsl:text>
                    <!-- guid -->
                    <xsl:text>,&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"guid":"</xsl:text>
                    <xsl:value-of select="darwin:InstitutionCode"/>
					<xsl:text>:</xsl:text>
					
					<xsl:choose>
						<xsl:when test="contains(darwin:CollectionCode, 'Amphibians')">
							<xsl:text>Herps</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="substring-after(darwin:CollectionCode, 'Vertebrate Zoology; ')"/>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:text>:</xsl:text>
                    <xsl:value-of select="darwin:CatalogNumberText"/>
                    <xsl:text>"</xsl:text>
                </xsl:when>

				<!-- MVZ -->
               <xsl:when test="darwin:InstitutionCode = 'MVZ'">
					<!-- title -->
                    <xsl:text>&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"title":"</xsl:text>
           			<xsl:variable name="code" select="concat(darwin:InstitutionCode, ' ', darwin:CatalogNumberText)"/>
            		<xsl:value-of select="$code"/>
                   <xsl:text>"</xsl:text>
                    <!-- guid -->
                    <xsl:text>,&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"guid":"</xsl:text>
                    <xsl:value-of select="darwin:InstitutionCode"/>
					<xsl:text>:</xsl:text>
					
					<xsl:choose>
						<xsl:when test="contains(darwin:CollectionCode, 'Herp')">
							<xsl:text>Herps</xsl:text>
						</xsl:when>
						<xsl:when test="contains(darwin:CollectionCode, 'Mamm')">
							<xsl:text>Mammals</xsl:text>
						</xsl:when>
						<xsl:when test="contains(darwin:CollectionCode, 'Bird')">
							<xsl:text>Birds</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="darwin:CollectionCode"/>						</xsl:otherwise>
					</xsl:choose>
					<xsl:text>:</xsl:text>
                    <xsl:value-of select="darwin:CatalogNumberText"/>
                    <xsl:text>"</xsl:text>
                </xsl:when>

				<!-- USNM -->
               <xsl:when test="darwin:InstitutionCode = 'USNM'">
					<!-- title -->
                    <xsl:text>&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"title":"</xsl:text>
           			<xsl:variable name="code" select="concat(darwin:InstitutionCode, ' ', darwin:CatalogNumberText)"/>
            		<xsl:value-of select="$code"/>
                   <xsl:text>"</xsl:text>
                    <!-- guid -->
                    <xsl:text>,&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"guid":"</xsl:text>
                    <xsl:value-of select="darwin:InstitutionCode"/>
					<xsl:text>:</xsl:text>
					
					<xsl:choose>
						<xsl:when test="contains(darwin:CollectionCode, 'Amphibians')">
							<xsl:text>Herps</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="substring-after(darwin:CollectionCode, 'Vertebrate Zoology; ')"/>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:text>:</xsl:text>
                    <xsl:value-of select="darwin:CatalogNumberText"/>
                    <xsl:text>"</xsl:text>
                </xsl:when>



				<!-- LSU -->
				<!-- Make LSU into LSUMZ -->
               <xsl:when test="darwin:InstitutionCode = 'LSU'">
					<!-- title -->
                    <xsl:text>&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"title":"</xsl:text>
           			<xsl:variable name="code" select="concat(darwin:InstitutionCode, 'MZ ', darwin:CatalogNumberText)"/>
            		<xsl:value-of select="$code"/>
                   <xsl:text>"</xsl:text>
                    <!-- guid -->
                    <xsl:text>,&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"guid":"</xsl:text>
                    <xsl:value-of select="darwin:InstitutionCode"/>
					<xsl:text>MZ:</xsl:text>
					<xsl:value-of select="darwin:CollectionCode"/>
					<xsl:text>:</xsl:text>
                    <xsl:value-of select="darwin:CatalogNumberText"/>
                    <xsl:text>"</xsl:text>
                </xsl:when>

				<!-- ozcam -->
               <xsl:when test="//source/@resource = 'ozcamDwC121'">
					<!-- title -->
                    <xsl:text>&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"title":"</xsl:text>
           			<xsl:variable name="code" select="concat(darwin:InstitutionCode, ' ', darwin:CatalogNumberText)"/>
            		<xsl:value-of select="$code"/>
                   <xsl:text>"</xsl:text>
                    <!-- guid -->
                    <xsl:text>,&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"guid":"</xsl:text>
                    <xsl:value-of select="darwin:InstitutionCode"/>
					<xsl:text>:</xsl:text>
                     <xsl:value-of select="darwin:CatalogNumberText"/>
                    <xsl:text>"</xsl:text>
                </xsl:when>



			<xsl:otherwise>

            <!-- title -->
            <xsl:text>&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"title":"</xsl:text>
            <xsl:variable name="code" select="concat(darwin:InstitutionCode, ' ', darwin:CatalogNumberText)"/>
            <xsl:value-of select="$code"/>
            <xsl:text>"</xsl:text>
            <!-- guid -->
            <xsl:variable name="guid" select="concat(darwin:InstitutionCode, ':', darwin:CollectionCode, ':', darwin:CatalogNumberText)"/>
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"guid":"</xsl:text>
            <xsl:value-of select="$guid"/>
            <xsl:text>"</xsl:text>
			</xsl:otherwise>
</xsl:choose>
        </xsl:if>
        <!-- url -->
        <xsl:choose>
            <xsl:when test="darwin:CatalogNumber != ''">
                <xsl:variable name="code" select="darwin:CatalogNumber"/>
                <xsl:choose>
                    <xsl:when test="contains($code, 'casent')">
                        <xsl:variable name="url" select="concat('http://www.antweb.org/specimen.do?name=',$code)"/>
                        <xsl:text>,&#x0D;</xsl:text>
                        <xsl:text>&#x09;&#x09;&#x09;"url":"</xsl:text>
                        <xsl:value-of select="$url"/>
                        <xsl:text>"</xsl:text>
                    </xsl:when>
                    <xsl:when test="contains($code, 'inbiocri')">
                        <xsl:variable name="url" select="concat('http://www.antweb.org/specimen.do?name=',$code)"/>
                        <xsl:text>,&#x0D;</xsl:text>
                        <xsl:text>&#x09;&#x09;&#x09;"url":"</xsl:text>
                        <xsl:value-of select="$url"/>
                        <xsl:text>"</xsl:text>
                    </xsl:when>
                    <xsl:when test="contains($code, 'jtl')">
                        <xsl:variable name="url" select="concat('http://www.antweb.org/specimen.do?name=',$code)"/>
                        <xsl:text>,&#x0D;</xsl:text>
                        <xsl:text>&#x09;&#x09;&#x09;"url":"</xsl:text>
                        <xsl:value-of select="$url"/>
                        <xsl:text>"</xsl:text>
                    </xsl:when>
                    <xsl:when test="contains($code, 'psw')">
                        <xsl:variable name="url" select="concat('http://www.antweb.org/specimen.do?name=',$code)"/>
                        <xsl:text>,&#x0D;</xsl:text>
                        <xsl:text>&#x09;&#x09;&#x09;"url":"</xsl:text>
                        <xsl:value-of select="$url"/>
                        <xsl:text>"</xsl:text>
                    </xsl:when>
                    <xsl:when test="contains($code, 'lacm ent')">
                        <xsl:variable name="url" select="concat('http://www.antweb.org/specimen.do?name=',$code)"/>
                        <xsl:text>,&#x0D;</xsl:text>
                        <xsl:text>&#x09;&#x09;&#x09;"url":"</xsl:text>
                        <xsl:value-of select="$url"/>
                        <xsl:text>"</xsl:text>
                    </xsl:when>
                    <xsl:otherwise/>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <!-- nothing -->
            </xsl:otherwise>
        </xsl:choose>


	<!-- basic triple -->

                     <xsl:text>,&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"institutionCode":"</xsl:text>
                    <xsl:value-of select="darwin:InstitutionCode"/>
                    <xsl:text>"</xsl:text>
       <xsl:apply-templates select="darwin:CollectionCode"/>

       <xsl:if test="darwin:CatalogNumber != ''">
					<xsl:text>,&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"catalogNumber":"</xsl:text>
                    <xsl:value-of select="darwin:CatalogNumber"/>
                    <xsl:text>"</xsl:text>
		</xsl:if>
      <xsl:if test="darwin:CatalogNumberText != ''">
					<xsl:text>,&#x0D;</xsl:text>
                    <xsl:text>&#x09;&#x09;&#x09;"catalogNumber":"</xsl:text>
                    <xsl:value-of select="darwin:CatalogNumberText"/>
                    <xsl:text>"</xsl:text>
		</xsl:if>



        <!-- scientific name -->
        <xsl:if test="darwin:ScientificName != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"organism":"</xsl:text>
            <xsl:value-of select="darwin:ScientificName"/>
            <xsl:text>"</xsl:text>
        </xsl:if>


<!-- classification -->
<!-- we store this just to help some queries we might want to make in the future, it's basically redundant -->

      <xsl:if test="darwin:Kingdom != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"kingdom":"</xsl:text>
            <xsl:value-of select="darwin:Kingdom"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
      <xsl:if test="darwin:Phylum != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"phylum":"</xsl:text>
            <xsl:value-of select="darwin:Phylum"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
      <xsl:if test="darwin:Class != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"class":"</xsl:text>
            <xsl:value-of select="darwin:Class"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
    <xsl:if test="darwin:Order != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"order":"</xsl:text>
            <xsl:value-of select="darwin:Order"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
 
 
     <xsl:if test="darwin:Family != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"family":"</xsl:text>
            <xsl:value-of select="darwin:Family"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
     <xsl:if test="darwin:Genus != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"genus":"</xsl:text>
            <xsl:value-of select="darwin:Genus"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
     <xsl:if test="darwin:Species != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"species":"</xsl:text>
            <xsl:value-of select="darwin:Species"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
     <xsl:if test="darwin:Subspecies != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"subspecies":"</xsl:text>
            <xsl:value-of select="darwin:Subspecies"/>
            <xsl:text>"</xsl:text>
        </xsl:if>




        <!-- country -->
        <xsl:if test="darwin:Country != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"country":"</xsl:text>
            <xsl:value-of select="darwin:Country"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
        <!-- state -->
        <xsl:if test="darwin:StateProvince != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"stateProvince":"</xsl:text>
            <xsl:value-of select="darwin:StateProvince"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
        <!-- county -->
        <xsl:if test="darwin:County != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"county":"</xsl:text>
            <xsl:value-of select="darwin:County"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
        <!-- island -->
        <xsl:if test="darwin:Island != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"island":"</xsl:text>
            <xsl:value-of select="darwin:Island"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
        <!-- island group -->
        <xsl:if test="darwin:IslandGroup != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"islandGroup":"</xsl:text>
            <xsl:value-of select="darwin:IslandGroup"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
        <!-- continent ocean -->
        <xsl:if test="darwin:ContinentOcean != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"continentOcean":"</xsl:text>
            <xsl:value-of select="darwin:ContinentOcean"/>
            <xsl:text>"</xsl:text>
        </xsl:if>


        <!-- locality -->
        <xsl:if test="darwin:Locality != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"locality":"</xsl:text>

	<!-- some ozcam records have &quot; embedded, which gets translated as " and hence breaks JSON -->
    <xsl:call-template name="replace-string">
        <xsl:with-param name="text" 
             select="darwin:Locality"/>
        <xsl:with-param name="from" select="'&quot;'"/>
        <xsl:with-param name="to" select="''"/>
    </xsl:call-template>



          <!--  <xsl:value-of select="darwin:Locality"/> -->
            <xsl:text>"</xsl:text>
        </xsl:if>



        <!-- geocoordinates, I'm relying on different Darwin Core schema not having both these tags! -->
        <!-- latitude -->
        <xsl:apply-templates select="darwin:DecimalLatitude"/>
        <xsl:apply-templates select="darwin:Latitude"/>
        <!-- longitude -->
        <xsl:apply-templates select="darwin:DecimalLongitude"/>
        <xsl:apply-templates select="darwin:Longitude"/>
        <!-- altitude -->
        <xsl:if test="darwin:MinimumElevationInMeters != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"elevation":"</xsl:text>
            <xsl:value-of select="darwin:MinimumElevationInMeters"/>
            <xsl:text>"</xsl:text>
        </xsl:if>

		<!-- some records, such as TNHC 59856, have verbatim coordinates, but no decimal ones -->
        <xsl:if test="darwin:VerbatimLatitude != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"verbatimLatitude":"</xsl:text>
 <!--           <xsl:value-of select="darwin:VerbatimLatitude"/> -->

   <xsl:call-template name="replace-string">
        <xsl:with-param name="text" 
             select="darwin:VerbatimLatitude"/>
        <xsl:with-param name="from" select="'&quot;'"/>
        <xsl:with-param name="to" select="'&#x5c;&#x22;'"/>
    </xsl:call-template>


            <xsl:text>"</xsl:text>
        </xsl:if>

        <xsl:if test="darwin:VerbatimLongitude != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"verbatimLongitude":"</xsl:text>
 <!--           <xsl:value-of select="darwin:VerbatimLongitude"/> -->

   <xsl:call-template name="replace-string">
        <xsl:with-param name="text" 
             select="darwin:VerbatimLongitude"/>
        <xsl:with-param name="from" select="'&quot;'"/>
        <xsl:with-param name="to" select="'&#x5c;&#x22;'"/>
    </xsl:call-template>


            <xsl:text>"</xsl:text>
        </xsl:if>

        <!-- dates -->
        <xsl:if test="darwin:DateLastModified != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"dateLastModified":"</xsl:text>
            <xsl:value-of select="darwin:DateLastModified"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
        <xsl:if test="darwin:VerbatimCollectingDate != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"verbatimCollectingDate":"</xsl:text>
            <xsl:value-of select="darwin:VerbatimCollectingDate"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
        <!-- types -->
        <xsl:if test="darwin:TypeStatus != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"typeStatus":"</xsl:text>
            <xsl:value-of select="darwin:TypeStatus"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
        <!-- collection info -->
        <!-- collector -->
        <xsl:if test="darwin:Collector != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"collector":"</xsl:text>
            <xsl:value-of select="darwin:Collector"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
        <xsl:if test="darwin:CollectorNumber != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"collectorNumber":"</xsl:text>
            <xsl:value-of select="darwin:CollectorNumber"/>
            <xsl:text>"</xsl:text>
        </xsl:if>
        <xsl:if test="darwin:FieldNumber != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"fieldNumber":"</xsl:text>
            <xsl:value-of select="darwin:FieldNumber"/>
            <xsl:text>"</xsl:text>
        </xsl:if>


		<xsl:apply-templates select="darwin:Remarks"/>

       <xsl:text>}</xsl:text>


    </xsl:template>



   <xsl:template match="darwin:CollectionCode">
             <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"collectionCode":"</xsl:text>
 

					<xsl:choose>
						<!-- -->
						<xsl:when test="contains(., 'Herp')">
							<xsl:text>Herps</xsl:text>
						</xsl:when>
						<xsl:when test="contains(., 'Mamm')">
							<xsl:text>Mammals</xsl:text>
						</xsl:when>
						<xsl:when test="contains(., 'Bird')">
							<xsl:text>Birds</xsl:text>
						</xsl:when>

						<!-- SAMA -->
						<xsl:when test="contains(., 'Reptiles')">
							<xsl:text>Herps</xsl:text>
						</xsl:when>


						<!-- USNM -->

						<xsl:when test="contains(., 'Amphibians')">
							<xsl:text>Herps</xsl:text>
						</xsl:when>

						<xsl:when test="contains(., 'Vertebrate Zoology;')">
							<xsl:value-of select="substring-after(., 'Vertebrate Zoology; ')"/>
						</xsl:when>


						<xsl:otherwise>
							<xsl:value-of select="."/>						</xsl:otherwise>
					</xsl:choose>



           <xsl:text>"</xsl:text>

    </xsl:template>



    <xsl:template match="darwin:DecimalLatitude">
        <xsl:if test=". != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"latitude":"</xsl:text>
            <xsl:value-of select="."/>
            <xsl:text>"</xsl:text>
        </xsl:if>
    </xsl:template>
    <xsl:template match="darwin:Latitude">
        <xsl:if test=". != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"latitude":"</xsl:text>
            <xsl:value-of select="."/>
            <xsl:text>"</xsl:text>
        </xsl:if>
    </xsl:template>
    <xsl:template match="darwin:DecimalLongitude">
        <xsl:if test=". != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"longitude":"</xsl:text>
            <xsl:value-of select="."/>
            <xsl:text>"</xsl:text>
        </xsl:if>
    </xsl:template>
    <xsl:template match="darwin:Longitude">
        <xsl:if test=". != ''">
            <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"longitude":"</xsl:text>
            <xsl:value-of select="."/>
            <xsl:text>"</xsl:text>
        </xsl:if>
    </xsl:template>

    <!-- Extract any links to other digital resources from the darwin:Remarks tag -->
    <xsl:template match="darwin:Remarks">
        <xsl:variable name="string" select="."/>
        <xsl:choose>
            <xsl:when
                test="contains($string, 'http://elib.cs.berkeley.edu/cgi-bin/mvz_query?Coll_Object_id=')">
                <xsl:comment>MVZ DiGIR records may also contain a URL to the specimen record. This
                    URL may also be used in GenBank records as a LinkOut.</xsl:comment>
                <xsl:variable name="part" select="substring-after($string, 'Coll_Object_id=')"/>
               <xsl:variable name="id" select="substring-before($part, '&quot;')"/>

             <xsl:text>,&#x0D;</xsl:text>
            <xsl:text>&#x09;&#x09;&#x09;"url":"</xsl:text>
            <xsl:text>http://mvzarctos.berkeley.edu/SpecimenDetail.cfm?collection_object_id=</xsl:text>
           <xsl:value-of select="$id"/>
            <xsl:text>"</xsl:text>

            </xsl:when>
            <xsl:otherwise/>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
