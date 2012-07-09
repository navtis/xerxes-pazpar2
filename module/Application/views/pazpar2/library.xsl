<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2010 California State University
 version:
 package: Worldcat
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="sidebar">
	<xsl:call-template name="account_sidebar" />
</xsl:template>

<xsl:template name="breadcrumb">
    <a href="{//request/controller}">
       <xsl:text>Library selection</xsl:text>
    </a>
</xsl:template>

<xsl:template name="main">

    <h1><xsl:value-of select="//target/title_display"/></h1>	

</xsl:template>

</xsl:stylesheet>
