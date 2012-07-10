<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 author: Graham Seaman
 copyright: 2010 California State University
 version: $Id$
 package: pazpar2 
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

<xsl:import href="../search/results.xsl" />  
<xsl:import href="includes.xsl" />


<xsl:output method="html" encoding="utf-8" indent="yes" doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="javascript_include">
	<xsl:call-template name="jslabels" />
        <script src="javascript/jquery/jquery-1.6.2.min.js" language="javascript" type="text/javascript"></script>
	<script src="javascript/pz2.js" language="javascript" type="text/javascript"></script>
</xsl:template>	
	
	<xsl:template name="main">

		<h1>Search25</h1>
	
	<xsl:call-template name="searchbox" />
	
	<xsl:if test="//Error">
		<xsl:call-template name="error-message">
			<xsl:with-param name="msg"><xsl:value-of select="//Error"/></xsl:with-param>
		</xsl:call-template>
	</xsl:if>
</xsl:template>


</xsl:stylesheet>
