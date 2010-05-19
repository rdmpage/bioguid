<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:owl="http://www.w3.org/2002/07/owl#"
  xmlns:dcterms="http://purl.org/dc/terms/"
  xmlns:tcollection="http://rs.tdwg.org/ontology/voc/Collection#"
  xmlns:vcard="http://www.w3.org/2001/vcard-rdf/3.0#"

exclude-result-prefixes=" dcterms owl rdf tcollection vcard"
>

<xsl:output method='html' version='1.0' encoding='utf-8' indent='yes'/>

<xsl:template match="/">

<!-- operations -->
<div id="rightnav">

</div>


<div class="document">
<h1><xsl:value-of select="//dcterms:title" /></h1>
<div><xsl:value-of select="//dcterms:identifier" /></div>
<div><xsl:value-of select="//tcollection:geospatialCoverage" /></div>
</div>

</xsl:template>

</xsl:stylesheet>