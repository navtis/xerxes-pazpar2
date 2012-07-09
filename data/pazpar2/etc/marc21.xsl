<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:pz="http://www.indexdata.com/pazpar2/1.0"
    xmlns:marc="http://www.loc.gov/MARC21/slim">
  
  <xsl:output indent="yes" method="xml" version="1.0" encoding="UTF-8"/>

<!-- Extract metadata from MARC21/USMARC 
      http://www.loc.gov/marc/bibliographic/ecbdhome.html
-->  
  <xsl:template name="record-hook"/>


  <xsl:template match="/">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="marc:record">
    <xsl:variable name="thesis" select="marc:datafield[@tag='502']/marc:subfield[@code='a']"/>
    <xsl:variable name="conferencepaper" select="marc:datafield[@tag='111']/marc:subfield[@code='a']"/>
    <xsl:variable name="journal_title" select="marc:datafield[@tag='773']/marc:subfield[@code='t']"/>
    <xsl:variable name="electronic_location_url" select="marc:datafield[@tag='856']/marc:subfield[@code='u']"/>
    <xsl:variable name="leader" select="marc:leader"/>
    <!-- marc counts from zero, xsl from one -->
    <xsl:variable name="leader5" select="substring($leader,6,1)"/>
    <xsl:variable name="leader6" select="substring($leader,7,1)"/>
    <xsl:variable name="leader7" select="substring($leader,8,1)"/>

    <xsl:variable name="medium">
    <!-- values of medium set to RIS types for Xerxes -->
      <xsl:choose>
	    <xsl:when test="$thesis">
            <xsl:text>THES</xsl:text>
        </xsl:when>
	    <xsl:when test="$conferencepaper">
	        <xsl:text>CPAPER</xsl:text>
	    </xsl:when>
	    <xsl:when test="$journal_title">
            <!-- was JOUR GS -->
	        <xsl:text>JFULL</xsl:text>
	    </xsl:when>
	    <xsl:otherwise>
            <xsl:variable name="controlField008" select="marc:controlfield[@tag='008']"/>    
            <xsl:variable name="controlField008-21" select="substring($controlField008,22,1)"/>
            <xsl:variable name="controlField008-23" select="substring($controlField008,24,1)"/>
            <xsl:choose>
                <xsl:when test="($leader6 = 'a') and ($leader7 = 'm')">
                    <xsl:choose>
                        <xsl:when test="($controlField008-23 = 'o')or($controlField008-23 = 'q')or($controlField008-23 = 's')">
	                        <xsl:text>EBOOK</xsl:text>
                        </xsl:when>
                        <xsl:otherwise>
	                        <xsl:text>BOOK</xsl:text>
                        </xsl:otherwise>
                     </xsl:choose>
                </xsl:when>
                <xsl:when test="($leader6 = 'a') and ($leader7 = 's')">
                    <xsl:choose>
                        <xsl:when test="($controlField008-21 = 'd') or ($controlField008-21 = 'w')">
                            <xsl:text>ELEC</xsl:text>
                        </xsl:when>
                        <xsl:when test="($controlField008-21 = 'p') or ($controlField008-21 = 'n')">
                            <xsl:text>JFULL</xsl:text>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:text>SER</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:when test="($leader6 = 'a') and ($leader7 = 'i')">
                    <xsl:text>ELEC</xsl:text>
                </xsl:when>
                <xsl:when test="($leader6 = 'c') and ($leader7 = 'd')">
                    <xsl:text>MUSIC</xsl:text>
                </xsl:when>
                <xsl:when test="($leader6 = 'e') and ($leader7 = 'f')">
                    <xsl:text>MAP</xsl:text>
                </xsl:when>
                <xsl:when test="$leader6 = 'g'">
                    <xsl:text>VIDEO</xsl:text>
                </xsl:when>
                <xsl:when test="$leader6 = 'i'">
                    <xsl:text>SOUND</xsl:text>
                </xsl:when>
                <xsl:when test="$leader6 = 'm'">
                    <xsl:text>COMP</xsl:text>
                </xsl:when>
                <xsl:when test="$leader6 = 'o'">
                    <xsl:text>XERXES_KIT</xsl:text>
                </xsl:when>
                <xsl:when test="$leader6 = 'p'">
                    <xsl:text>XERXES_MixedMaterial</xsl:text>
                </xsl:when>
                <xsl:when test="$leader6 = 'r'">
                    <xsl:text>XERXES_PhysicalObject</xsl:text>
                </xsl:when>
                <xsl:when test="$leader6 = 't'">
                    <xsl:text>MANSCPT</xsl:text>
                </xsl:when>
                <!-- isbn -->
                <xsl:when test="marc:datafield[@tag='020']">
                    <xsl:text>BOOK</xsl:text>
                </xsl:when>
                <!-- issn -->
                <xsl:when test="marc:datafield[@tag='022']">
                    <xsl:text>JOUR</xsl:text>
                </xsl:when>
                <!-- unknown, general -->
                <xsl:otherwise>
                    <xsl:text>GEN</xsl:text> 
                </xsl:otherwise>
            </xsl:choose>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>


    <xsl:variable name="has_fulltext">
      <xsl:choose>
        <xsl:when test="marc:datafield[@tag='856']/marc:subfield[@code='q']">
          <xsl:text>yes</xsl:text>
        </xsl:when>
        <xsl:when test="marc:datafield[@tag='856']/marc:subfield[@code='i']='TEXT*'">
          <xsl:text>yes</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>no</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>

    <pz:record>
      <xsl:for-each select="marc:controlfield[@tag='001']">
        <pz:metadata type="id">
          <xsl:value-of select="."/>
        </pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='010']">
        <pz:metadata type="lccn">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='020']">
        <pz:metadata type="isbn">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='022']">
        <pz:metadata type="issn">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='027']">
        <pz:metadata type="tech-rep-nr">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='035']">
        <pz:metadata type="system-control-nr">
          <xsl:choose>
            <xsl:when test="marc:subfield[@code='a']">
              <xsl:value-of select="marc:subfield[@code='a']"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="marc:subfield[@code='b']"/>
            </xsl:otherwise>
          </xsl:choose>
	</pz:metadata>
      </xsl:for-each>
<!--
    <pz:metadata type="author">
        <xsl:choose>
-->            <!-- individual author is first choice -->
<!--            <xsl:when test="marc:datafield[@tag='100']/marc:subfield[@code='a']">
                <xsl:value-of select="marc:datafield[@tag='100']/marc:subfield[@code='a']" />
            </xsl:when>
-->            <!-- then corporate author  -->
<!--            <xsl:when test="marc:datafield[@tag='110']/marc:subfield[@code='a']">
                <xsl:value-of select="marc:datafield[@tag='110']/marc:subfield[@code='a']" />
            </xsl:when>
-->            <!-- then conference as author  -->
<!--            <xsl:when test="marc:datafield[@tag='111']/marc:subfield[@code='a']">
                <xsl:value-of select="marc:datafield[@tag='111']/marc:subfield[@code='a']" />
            </xsl:when>
-->            <!-- then responsible editors &c  -->
<!--            <xsl:when test="marc:datafield[@tag='245']/marc:subfield[@code='c']">
                <xsl:value-of select="marc:datafield[@tag='245']/marc:subfield[@code='c']" />
            </xsl:when>
-->            <!-- could maybe try 600 $a or 700 $a? -->
<!--            <xsl:otherwise/>
         </xsl:choose>
     </pz:metadata>
-->

      <xsl:for-each select="marc:datafield[@tag='100']">
	<pz:metadata type="author">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
	<pz:metadata type="author-title">
	  <xsl:value-of select="marc:subfield[@code='c']"/>
	</pz:metadata>
	<pz:metadata type="author-date">
	  <xsl:value-of select="marc:subfield[@code='d']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='110']">
	<pz:metadata type="corporate-name">
	    <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
	<pz:metadata type="corporate-location">
	    <xsl:value-of select="marc:subfield[@code='c']"/>
	</pz:metadata>
	<pz:metadata type="corporate-date">
	    <xsl:value-of select="marc:subfield[@code='d']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='111']">
	<pz:metadata type="meeting-name">
	    <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
	<pz:metadata type="meeting-location">
	    <xsl:value-of select="marc:subfield[@code='c']"/>
	</pz:metadata>
	<pz:metadata type="meeting-date">
	    <xsl:value-of select="marc:subfield[@code='d']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='260']">
	<pz:metadata type="date">
	    <xsl:value-of select="marc:subfield[@code='c']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='245']">
        <pz:metadata type="title">
          <xsl:value-of select="marc:subfield[@code='a']"/>
        </pz:metadata>
        <pz:metadata type="title-remainder">
          <xsl:value-of select="marc:subfield[@code='b']"/>
        </pz:metadata>
        <pz:metadata type="title-responsibility">
          <xsl:value-of select="marc:subfield[@code='c']"/>
        </pz:metadata>
        <pz:metadata type="title-dates">
          <xsl:value-of select="marc:subfield[@code='f']"/>
        </pz:metadata>
        <pz:metadata type="title-medium">
          <xsl:value-of select="marc:subfield[@code='h']"/>
        </pz:metadata>
        <pz:metadata type="title-number-section">
          <xsl:value-of select="marc:subfield[@code='n']"/>
        </pz:metadata>
        <pz:metadata type="title-name-section">
          <xsl:value-of select="marc:subfield[@code='p']"/>
        </pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='250']">
	<pz:metadata type="edition">
	    <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='260']">
        <pz:metadata type="publication-place">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
        <pz:metadata type="publication-name">
	  <xsl:value-of select="marc:subfield[@code='b']"/>
	</pz:metadata>
        <pz:metadata type="publication-date">
	  <xsl:value-of select="marc:subfield[@code='c']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='300']">
	<pz:metadata type="physical-extent">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
	<pz:metadata type="physical-format">
	  <xsl:value-of select="marc:subfield[@code='b']"/>
	</pz:metadata>
	<pz:metadata type="physical-dimensions">
	  <xsl:value-of select="marc:subfield[@code='c']"/>
	</pz:metadata>
	<pz:metadata type="physical-accomp">
	  <xsl:value-of select="marc:subfield[@code='e']"/>
	</pz:metadata>
	<pz:metadata type="physical-unittype">
	  <xsl:value-of select="marc:subfield[@code='f']"/>
	</pz:metadata>
	<pz:metadata type="physical-unitsize">
	  <xsl:value-of select="marc:subfield[@code='g']"/>
	</pz:metadata>
	<pz:metadata type="physical-specified">
	  <xsl:value-of select="marc:subfield[@code='3']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='440']">
	<pz:metadata type="series-title">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
      </xsl:for-each>
      <!-- 490 obsoletes 440 -->
      <xsl:for-each select="marc:datafield[@tag='490']">
	<pz:metadata type="series-title">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
      </xsl:for-each>

      <xsl:variable name="cf500a"> 
        <xsl:value-of select="marc:datafield[@tag = '500']/marc:subfield[@code='a']"/>
      </xsl:variable>

      <!-- handle toc and notes GS -->
      <xsl:choose>
        <!-- lots of hyphens should mean its a TOC -->
        <xsl:when test="string-length($cf500a) - string-length(translate($cf500a,'-','')) >= 6 ">
    	  <pz:metadata type="toc">
	        <xsl:value-of select="$cf500a"/>
          </pz:metadata> 
        </xsl:when>
        <xsl:otherwise>
            <!-- otherwise toc is where it should be and 500$a is note -->
            <pz:metadata type="xerxes-note">
                <xsl:value-of select="$cf500a"/>
            </pz:metadata>
            <xsl:for-each select="marc:datafield[@tag = '505']">
    	        <pz:metadata type="toc">
	                <xsl:value-of select="marc:subfield[@code='a']"/>
                    <xsl:if test="substring(marc:subfield[@code='a'], string-length(marc:subfield[@code='a'])) != '.'"><xsl:text>.</xsl:text></xsl:if>
                </pz:metadata>          
            </xsl:for-each>
        </xsl:otherwise>
      </xsl:choose>

      <!--<xsl:for-each select="marc:datafield[@tag &gt;= 501 and @tag &lt; 540 and @tag != 505 and @tag != 520 and @tag != 546]">
-->      <xsl:for-each select="marc:datafield[(@tag=501)or(@tag=502)or(@tag=507)or(@tag=515)or(@tag=516)or(@tag=518)or(@tag=525)or(@tag=536)or(@tag=538)]">
          <pz:metadata type="xerxes-note">
              <xsl:value-of select="marc:subfield[@code='a']"/>
              <xsl:if test="substring(marc:subfield[@code='a'], string-length(marc:subfield[@code='a'])) != '.'"><xsl:text>.</xsl:text></xsl:if>
          </pz:metadata>
       </xsl:for-each>

      <!-- added credits GS -->
      <xsl:for-each select="marc:datafield[@tag = '508']">
    	  <pz:metadata type="credits">
	        <xsl:value-of select="marc:subfield[@code='a']"/>
            <xsl:if test="substring(marc:subfield[@code='a'], string-length(marc:subfield[@code='a'])) != '.'"><xsl:text>.</xsl:text></xsl:if>
          </pz:metadata>          
      </xsl:for-each>
      <xsl:for-each select="marc:datafield[@tag = '511']">
    	  <pz:metadata type="performers">
	        <xsl:value-of select="marc:subfield[@code='a']"/>
            <xsl:if test="substring(marc:subfield[@code='a'], string-length(marc:subfield[@code='a'])) != '.'"><xsl:text>.</xsl:text></xsl:if>
          </pz:metadata>          
      </xsl:for-each>

      <!-- added abstract GS -->
      <xsl:for-each select="marc:datafield[@tag = '520']">
    	  <pz:metadata type="abstract">
	        <xsl:value-of select="marc:subfield[@code='a']"/>
            <xsl:if test="substring(marc:subfield[@code='a'], string-length(marc:subfield[@code='a'])) != '.'"><xsl:text>.</xsl:text></xsl:if>
          </pz:metadata>          
      </xsl:for-each>

      <!-- added geographic coverage GS -->
      <xsl:for-each select="marc:datafield[@tag = '522']">
    	  <pz:metadata type="geographic">
	        <xsl:value-of select="marc:subfield[@code='a']"/>
            <xsl:if test="substring(marc:subfield[@code='a'], string-length(marc:subfield[@code='a'])) != '.'"><xsl:text>.</xsl:text></xsl:if>
          </pz:metadata>          
      </xsl:for-each>

      <!-- added 'cite as' GS -->
      <xsl:for-each select="marc:datafield[@tag = '524']">
    	  <pz:metadata type="citation">
	        <xsl:value-of select="marc:subfield[@code='a']"/>
          </pz:metadata>          
      </xsl:for-each>

      <!-- added bio GS -->
      <xsl:for-each select="marc:datafield[@tag = '545']">
    	  <pz:metadata type="biography">
	        <xsl:value-of select="marc:subfield[@code='a']"/>
            <xsl:if test="substring(marc:subfield[@code='a'], string-length(marc:subfield[@code='a'])) != '.'"><xsl:text>.</xsl:text></xsl:if>
          </pz:metadata>          
      </xsl:for-each>

      <!-- added language GS -->
      <xsl:choose> 
        <xsl:when test="marc:datafield[@tag='546']/marc:subfield[@code='a']">
            <xsl:for-each select="marc:datafield[@tag = '546']">
    	        <pz:metadata type="language">
	                <xsl:value-of select="marc:subfield[@code='a']"/>
                </pz:metadata>          
            </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
            <xsl:variable name="controlField008" select="marc:controlfield[@tag='008']"/>    
    	    <pz:metadata type="language">
	            <xsl:value-of select="substring($controlField008,36,3)"/>
            </pz:metadata>          
        </xsl:otherwise>
      </xsl:choose>

      <!-- added issuer (serials) GS -->
      <xsl:for-each select="marc:datafield[@tag = '552']">
    	  <pz:metadata type="issuer">
	        <xsl:value-of select="marc:subfield[@code='a']"/>
          </pz:metadata>          
      </xsl:for-each>

    <!-- added genre GS -->
    <!-- could also use 006, 008 chars 18-19 (music) -->
      <xsl:for-each select="marc:datafield[@tag='650' or tag='651' or tag='654' or tag='655' or tag='656' or tag='657']">
        <xsl:if test="marc:subfield[@code='v']">
            <pz:metadata type="genre">
	            <xsl:value-of select="marc:subfield[@code='v']"/>
	        </pz:metadata>
        </xsl:if>
      </xsl:for-each>
      <!-- test for 'book' coding in 008 -->
      <xsl:if test="(($leader6 = 'a') or ($leader6 = 't')) and (($leader7 = 'a') or ($leader7 = 'c') or ($leader7 = 'd') or ($leader7 = 'm'))">
            <xsl:variable name="controlField008" select="marc:controlfield[@tag='008']"/> 
            <xsl:variable name="cF008-22" select="substring($controlField008, 23,1)"/>
            <xsl:variable name="cF008-28" select="substring($controlField008, 29,1)"/>
            <xsl:variable name="cF008-29" select="substring($controlField008, 30,1)"/>
            <xsl:variable name="cF008-30" select="substring($controlField008, 31,1)"/>
            <xsl:variable name="cF008-33" select="substring($controlField008, 34,1)"/>
            <xsl:variable name="cF008-34" select="substring($controlField008, 35,1)"/>
             <xsl:choose>
                <xsl:when test="$cF008-22 = 'a'">
                     <pz:metadata type="genre">Preschool</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-22 = 'b'">
                     <pz:metadata type="genre">Primary</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-22 = 'c'">
                    <pz:metadata type="genre">Pre-adolescent</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-22 = 'd'">
                    <pz:metadata type="genre">Adolescent</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-22 = 'e'">
                    <pz:metadata type="genre">Adult</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-22 = 'j'">
                    <pz:metadata type="genre">Juvenile</pz:metadata>
                </xsl:when>
                <xsl:otherwise />
            </xsl:choose>
            <xsl:call-template name="find-genres">
                  <xsl:with-param name="pos">25</xsl:with-param>
                  <xsl:with-param name="cF008"><xsl:value-of select="marc:controlfield[@tag='008']"/></xsl:with-param>
            </xsl:call-template>
            <xsl:if test="($cF008-28 = 'a')or($cF008-28 = 'c')or($cF008-28 = 'f')or($cF008-28 = 'i')or($cF008-28 = 'l')or($cF008-28 = 'm')or($cF008-28 = 'o')or($cF008-28 = 's')or($cF008-28 = 'u')or($cF008-28 = 'z')">
                <pz:metadata type="genre">Government</pz:metadata>
            </xsl:if>
            <xsl:if test="$cF008-29 = '1'">
                <pz:metadata type="genre">Conference</pz:metadata>
            </xsl:if>
            <xsl:if test="$cF008-30 = '1'">
                <pz:metadata type="genre">Festschrift</pz:metadata>
            </xsl:if>
            <xsl:choose>
                <xsl:when test="$cF008-33 = '1'">
                    <pz:metadata type="genre">Fiction</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-33 = 'd'">
                    <pz:metadata type="genre">Drama</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-33 = 'e'">
                    <pz:metadata type="genre">Essay</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-33 = 'f'">
                    <pz:metadata type="genre">Novel</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-33 = 'h'">
                    <pz:metadata type="genre">Humour</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-33 = 'i'">
                    <pz:metadata type="genre">Letters</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-33 = 'j'">
                    <pz:metadata type="genre">Short stories</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-33 = 'p'">
                    <pz:metadata type="genre">Poetry</pz:metadata>
                </xsl:when>
                <xsl:when test="$cF008-33 = 's'">
                    <pz:metadata type="genre">Speeches</pz:metadata>
                </xsl:when>
                <xsl:otherwise />
            </xsl:choose>
            <xsl:if test="$cF008-34 = 'a'">
                <pz:metadata type="genre">Autobiography</pz:metadata>
            </xsl:if>
            <xsl:if test="$cF008-34 = 'b'">
                <pz:metadata type="genre">Biography</pz:metadata>
            </xsl:if>

        </xsl:if>

      <!-- original pz2 description field - unused in Xerxes GS -->
<!--      <xsl:for-each select="marc:datafield[@tag = '500' or @tag = '505' or
      		@tag = '518' or @tag = '520' or @tag = '522']">
	<pz:metadata type="description">
            <xsl:value-of select="*/text()"/>
        </pz:metadata>
      </xsl:for-each>
-->      
      <xsl:for-each select="marc:datafield[@tag='600' or @tag='610' or @tag='611' or @tag='630' or @tag='648' or @tag='650' or @tag='651' or @tag='653' or @tag='654' or @tag='655' or @tag='656' or @tag='657' or @tag='658' or @tag='662' or @tag='69X']">
        <pz:metadata type="subject">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
	<pz:metadata type="subject-long">
	  <xsl:for-each select="marc:subfield">
	      <xsl:if test="position() > 1">
	          <xsl:text>, </xsl:text>
	      </xsl:if>
	      <xsl:value-of select="."/>
	  </xsl:for-each>
	</pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='856']">
	<pz:metadata type="electronic-url">
	  <xsl:value-of select="marc:subfield[@code='u']"/>
	</pz:metadata>
	<pz:metadata type="electronic-text">
	  <xsl:value-of select="marc:subfield[@code='y' or @code='3']"/>
	</pz:metadata>
	<pz:metadata type="electronic-note">
	  <xsl:value-of select="marc:subfield[@code='z']"/>
	</pz:metadata>
	<pz:metadata type="electronic-format-instruction">
	  <xsl:value-of select="marc:subfield[@code='i']"/>
	</pz:metadata>
	<pz:metadata type="electronic-format-type">
	  <xsl:value-of select="marc:subfield[@code='q']"/>
	</pz:metadata>
      </xsl:for-each>

      <pz:metadata type="has-fulltext">
        <xsl:value-of select="$has_fulltext"/> 
      </pz:metadata>

      <xsl:for-each select="marc:datafield[@tag='773']">
    	<pz:metadata type="citation">
	      <xsl:for-each select="*">
	        <xsl:value-of select="normalize-space(.)"/>
	        <xsl:text> </xsl:text>
    	  </xsl:for-each>
        </pz:metadata>
        <xsl:if test="marc:subfield[@code='t']">
    	  <pz:metadata type="journal-title">
	        <xsl:value-of select="marc:subfield[@code='t']"/>
          </pz:metadata>          
        </xsl:if>
        <xsl:if test="marc:subfield[@code='g']">
    	  <pz:metadata type="journal-subpart">
	        <xsl:value-of select="marc:subfield[@code='g']"/>
          </pz:metadata>          
        </xsl:if>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='852']">
        <xsl:if test="marc:subfield[@code='y']">
	  <pz:metadata type="publicnote">
	    <xsl:value-of select="marc:subfield[@code='y']"/>
	  </pz:metadata>
	</xsl:if>
	<xsl:if test="marc:subfield[@code='h'] and (not /opacRecord/holdings/holding/callnumber)">
	  <pz:metadata type="callnumber">
	    <xsl:value-of select="marc:subfield[@code='h']"/>
	  </pz:metadata>
	</xsl:if>
      </xsl:for-each>

      <pz:metadata type="medium">
	    <xsl:value-of select="$medium"/>
      </pz:metadata>
      
      <xsl:for-each select="marc:datafield[@tag='900']/marc:subfield[@code='a']">
        <pz:metadata type="fulltext">
          <xsl:value-of select="."/>
        </pz:metadata>
      </xsl:for-each>

      <!-- <xsl:if test="$fulltext_a">
	<pz:metadata type="fulltext">
	  <xsl:value-of select="$fulltext_a"/>
	</pz:metadata>
      </xsl:if> -->

      <xsl:for-each select="marc:datafield[@tag='900']/marc:subfield[@code='b']">
        <pz:metadata type="fulltext">
          <xsl:value-of select="."/>
        </pz:metadata>
      </xsl:for-each>

      <!-- <xsl:if test="$fulltext_b">
	<pz:metadata type="fulltext">
	  <xsl:value-of select="$fulltext_b"/>
	</pz:metadata>
      </xsl:if> -->

      <xsl:for-each select="marc:datafield[@tag='907' or @tag='901']">
        <pz:metadata type="iii-id">
	  <xsl:value-of select="marc:subfield[@code='a']"/>
	</pz:metadata>
      </xsl:for-each>

<!-- this value (in Middx Horizon records) doesn't seem to work.

      <xsl:if test="marc:datafield[@tag='999']/marc:subfield[@code='b']">
        <xsl:variable name="f999b" select="marc:datafield[@tag='999']/marc:subfield[@code='b']"/>
        <xsl:if test="$f999b='Horizon bib#'">
            <pz:metadata type="iii-id">
                <xsl:value-of select="marc:datafield[@tag='999']/marc:subfield[@code='a']"/>
	        </pz:metadata>
        </xsl:if>
     </xsl:if>
-->
      <xsl:for-each select="marc:datafield[@tag='948']">
        <pz:metadata type="holding">
	  <xsl:for-each select="marc:subfield">
	    <xsl:if test="position() > 1">
	      <xsl:text> </xsl:text>
	    </xsl:if>
	    <xsl:value-of select="."/>
	  </xsl:for-each>
        </pz:metadata>
      </xsl:for-each>

      <xsl:for-each select="marc:datafield[@tag='991']">
        <pz:metadata type="holding">
	  <xsl:for-each select="marc:subfield">
	    <xsl:if test="position() > 1">
	      <xsl:text> </xsl:text>
	    </xsl:if>
	    <xsl:value-of select="."/>
	  </xsl:for-each>
        </pz:metadata>
      </xsl:for-each>

      <!-- passthrough id data -->
      <xsl:for-each select="pz:metadata">
          <xsl:copy-of select="."/>
      </xsl:for-each>

      <!-- other stylesheets importing this might want to define this -->
      <xsl:call-template name="record-hook"/>

    </pz:record>    
  </xsl:template>

  <xsl:template name="find-genres">
    <xsl:param name="pos" />
    <xsl:param name="cF008" />

    <xsl:if test="$pos &lt; 29">
        <xsl:variable name="onechar" select="substring($cF008,$pos,1)"/>
        <xsl:choose>
            <xsl:when test="$onechar='a'">
                <pz:metadata type="genre">Abstract</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='b'">
                <pz:metadata type="genre">Bibliography</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='c'">
                <pz:metadata type="genre">Catalogue</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='d'">
                <pz:metadata type="genre">Dictionary</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='e'">
                <pz:metadata type="genre">Encyclopaedia</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='f'">
                <pz:metadata type="genre">Handbook</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='g'">
                <pz:metadata type="genre">Legal article</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='i'">
                <pz:metadata type="genre">Index</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='j'">
                <pz:metadata type="genre">Patent</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='k'">
                <pz:metadata type="genre">Discography</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='l'">
                <pz:metadata type="genre">Law</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='m'">
                <pz:metadata type="genre">Thesis</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='n'">
                <pz:metadata type="genre">Literature survey</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='o'">
                <pz:metadata type="genre">Review</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='p'">
                <pz:metadata type="genre">Programmed text</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='q'">
                <pz:metadata type="genre">Filmography</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='r'">
                <pz:metadata type="genre">Directory</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='s'">
                <pz:metadata type="genre">Statistics</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='t'">
                <pz:metadata type="genre">Technical report</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='u'">
                <pz:metadata type="genre">Standard</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='v'">
                <pz:metadata type="genre">Legal case</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='w'">
                <pz:metadata type="genre">Law report</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='y'">
                <pz:metadata type="genre">Yearbook</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='z'">
                <pz:metadata type="genre">Treaty</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='2'">
                <pz:metadata type="genre">Offprint</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='5'">
                <pz:metadata type="genre">Calendar</pz:metadata>
            </xsl:when>
            <xsl:when test="$onechar='6'">
                <pz:metadata type="genre">Graphic novel</pz:metadata>
            </xsl:when>
            <xsl:otherwise/>
        </xsl:choose>
        <xsl:call-template name="find-genres">
            <xsl:with-param name="pos"><xsl:number value="number($pos)+1" /></xsl:with-param>
            <xsl:with-param name="cF008"><xsl:value-of select="$cF008"/></xsl:with-param>
        </xsl:call-template>
    </xsl:if>
  </xsl:template>
  
  <xsl:template match="text()"/>

</xsl:stylesheet>
