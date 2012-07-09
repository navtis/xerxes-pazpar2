<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 author: Graham Seaman
 copyright: 2010 California State University
 version: $Id$
 package: Pazpar2
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->

<!DOCTYPE xsl:stylesheet  [
<!ENTITY nbsp   "&#160;">
<!ENTITY copy   "&#169;">
<!ENTITY reg    "&#174;">
<!ENTITY trade  "&#8482;">
<!ENTITY mdash  "&#8212;">
<!ENTITY ldquo  "&#8220;">
<!ENTITY rdquo  "&#8221;"> 
<!ENTITY pound  "&#163;">
<!ENTITY yen    "&#165;">
<!ENTITY euro   "&#8364;">
]>

 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

	<!--	<xsl:import href="../search/results.xsl" /> -->
	<xsl:import href="includes.xsl" />
	<xsl:import href="search.xsl" /> 

    <xsl:template match="/*">
            <xsl:call-template name="surround" />
    </xsl:template>

    <!-- refresh to keep updating status -->
    <xsl:template name="surround_meta">
	    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	    <meta http-equiv="refresh" content="1" />
    </xsl:template>

    <xsl:template name="javascript_include"> 
	<xsl:call-template name="jslabels" /> 
	<script src="javascript/jquery/jquery-1.6.2.min.js" language="javascript" type="text/javascript"></script> 
	<script src="javascript/pz2_status.js" language="javascript" type="text/javascript"></script> 
    </xsl:template>

    <xsl:template name="sidebar">
	    <xsl:call-template name="options_sidebar" />
    </xsl:template>
	
	<xsl:template name="main">

		<h1><xsl:value-of select="$text_search_module" /></h1>
	
	<xsl:call-template name="searchbox" />

	<xsl:call-template name="search-results" /> 

	</xsl:template>

	<xsl:template name="search-results">
	     <xsl:param name="sidebar">right</xsl:param>
	     <xsl:variable name="width">
		     <xsl:text>width:</xsl:text>
		     <xsl:choose>
			     <xsl:when test="//pz2results/progress">
		     		<xsl:value-of select="//pz2results/progress"/>
			     </xsl:when>
		             <xsl:otherwise>0</xsl:otherwise>
		     </xsl:choose>
		     <xsl:text>%</xsl:text>
	     </xsl:variable>
                <div id="progress_outer">
			<div id="progress_container">
				<div id="progress" style="{$width}"></div>
                    </div>
                    <button id="terminator">Halt search now</button>
		</div> 
	     <div id="search-results" display="none">	
		<!-- results area -->
		
		<div class="">
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="$sidebar = 'right'">
						<xsl:text>yui-ge</xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>yui-gf</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			
		    <!-- insert status info in right column (see local.css) GS -->    
            <div class="sidebar status">
               <div class="box">
                   <xsl:call-template name="status_sidebar" />
               </div>
            </div>
    </div>
    </div>
	</xsl:template>

    <!-- and the new template GS -->
    <xsl:template name="status_sidebar">
        <h2>Libraries Searched</h2>
        <h3>Records fetched / found</h3>
        <ul>
            <xsl:for-each select="//pz2results/pz2result">
                <xsl:variable name="fc">
                    <xsl:choose>
                        <xsl:when test="./state='Client_Working'">
                            <xsl:text>working</xsl:text>
                        </xsl:when>
                        <xsl:when test="./state='Client_Idle'">
                            <xsl:text>succeeded</xsl:text>
                        </xsl:when>
                        <xsl:when test="./state='Client_Disconnected'">
                            <xsl:text>failed</xsl:text>
                        </xsl:when>
                        <xsl:otherwise></xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                <li id="status-{./name}"><xsl:value-of select="./title_short" />
                    <xsl:text>: </xsl:text>
                    <span class="{$fc}">
                    <xsl:choose>
                        <xsl:when test="$fc = 'failed'">
                            <xsl:value-of select="substring(./state, 8)"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:choose>
                                <xsl:when test="./records = ./hits">
                                    <span class="status-records"><xsl:value-of select="./records" /></span>
                                    <span class="status-hits"></span>
                                </xsl:when>
                                <xsl:otherwise>
                                    <span class="status-records"><xsl:value-of select="./records" /></span>
                                    <span class="status-hits">&nbsp;/&nbsp;<xsl:value-of select="./hits" /></span>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:otherwise>
                    </xsl:choose>
                    </span>
                </li>
            </xsl:for-each>
        </ul>
    </xsl:template>

</xsl:stylesheet>
