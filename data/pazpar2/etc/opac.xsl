<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:pz="http://www.indexdata.com/pazpar2/1.0"
    xmlns:marc="http://www.loc.gov/MARC21/slim">
  
  <xsl:import href="marc21.xsl"/>

  <xsl:output indent="yes" method="xml" version="1.0" encoding="UTF-8"/>

<!-- Extract metadata from OPAC records with embedded MARC records
      http://www.loc.gov/marc/bibliographic/ecbdhome.html
     Returns values as attributes since pz2 can't manage nested xml
     at this point
-->  

  <xsl:template name="record-hook">

    <xsl:for-each select="/opacRecord/holdings/holding">
      
      <xsl:variable name="locallocation">
          <xsl:value-of select="localLocation"/>
          <xsl:if test="shelvingLocation">
              <xsl:text>: </xsl:text><xsl:value-of select="shelvingLocation"/>
          </xsl:if>
      </xsl:variable>

      <xsl:variable name="callnumber">
          <xsl:value-of select="callNumber"/>
      </xsl:variable>

      <xsl:choose>
      <xsl:when test="circulations">
        <xsl:for-each select="circulations/circulation">
            <pz:metadata type="opacitem">
                <xsl:attribute name="locallocation">
                    <xsl:value-of select="$locallocation"/>
                </xsl:attribute>
                <xsl:attribute name="callnumber">
                    <xsl:value-of select="$callnumber"/>
                </xsl:attribute>
                <xsl:if test="availableNow">
                    <xsl:attribute name="available">
                        <xsl:choose>
                            <xsl:when test="availableNow/@value = '1'"><xsl:text>Available</xsl:text>
                            </xsl:when>
                            <xsl:when test="availableNow/@value = '0'"><xsl:text> </xsl:text>
                            </xsl:when>
                            <xsl:otherwise></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                    <xsl:attribute name="duedate">
                        <xsl:choose>
                            <xsl:when test="availabiltyDate"> <!-- typo deliberate-->
                                <xsl:value-of select="availabiltyDate"/>
                            </xsl:when>
                            <xsl:when test="availabilityDate"> 
                                <xsl:value-of select="availabilityDate"/>
                            </xsl:when>
                            <xsl:otherwise></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </xsl:if>
                <xsl:if test="availableThru">
                    <xsl:attribute name="duration">
                        <xsl:value-of select="availableThru"/>
                    </xsl:attribute>
                </xsl:if>
                <xsl:if test="renewable">
                    <xsl:attribute name="renewable">
                        <xsl:choose>
                            <xsl:when test="renewable/@value = '1'"><xsl:text>Renewable </xsl:text>
                            </xsl:when>
                            <xsl:when test="renewable/@value = '0'"><xsl:text> </xsl:text>
                            </xsl:when>
                            <xsl:otherwise></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </xsl:if>
                <xsl:if test="onHold">
                    <xsl:attribute name="onhold">
                        <xsl:choose>
                            <xsl:when test="onHold/@value = '1'"><xsl:text>On hold</xsl:text>
                            </xsl:when>
                            <xsl:when test="onHold/@value = '0'"><xsl:text> </xsl:text>
                            </xsl:when>
                            <xsl:otherwise></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </xsl:if>
                <xsl:if test="itemId">
                    <xsl:attribute name="itemid">
                        <xsl:value-of select="itemId"/>
                    </xsl:attribute>
                </xsl:if>
                <xsl:if test="itemId"> <!-- need a value or element dropped -->
                    <xsl:value-of select="itemId"/>
                </xsl:if>
            </pz:metadata>  <!-- end of item -->
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise> <!-- no circulation data, maybe innopac? -->
        <pz:metadata type="opacholding">
            <xsl:attribute name="locallocation">
                <xsl:value-of select="$locallocation"/>
            </xsl:attribute>
            <xsl:attribute name="callnumber">
                <xsl:value-of select="$callnumber"/>
            </xsl:attribute>
            <xsl:if test="publicNote"> <!-- Wellcome do this -->
                    <xsl:attribute name="available">
                        <xsl:value-of select="publicNote"/>
                    </xsl:attribute>
            </xsl:if>
            <xsl:value-of select="$callnumber"/>
        </pz:metadata> 
    </xsl:otherwise>
    </xsl:choose>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="/">
    <xsl:choose>
      <xsl:when test="opacRecord">
        <xsl:apply-templates select="opacRecord/bibliographicRecord"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
