<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">


<xsl:import href="../includes.xsl" />
<xsl:import href="eng.xsl" />

<xsl:template name="error-message">
	<xsl:param name="msg"/>
	<div class="comms-box error-box"><xsl:value-of select="$msg"/></div>
</xsl:template>

<xsl:template name="breadcrumb_start">
    <a>
        <xsl:attribute name="href">
            <xsl:value-of select="//request/controller"/>
            <xsl:text>/index?</xsl:text>
            <xsl:for-each select="//request/session/targetnames">
                <xsl:text>&amp;target=</xsl:text><xsl:value-of select="." />
            </xsl:for-each>
        </xsl:attribute>
        <xsl:text>Library selection</xsl:text>
    </a>
</xsl:template>

<!--  hidden field setting called from ../includes.xsl GS -->
    <xsl:template name="searchbox_hidden_fields_local">
        <xsl:call-template name="session-data"/>
    </xsl:template>

    <!-- include the current session values -->
    <xsl:template name="session-data">
        <!-- see http://www.w3.org/TR/html5/elements.html#embedding-custom-non-visible-data-with-the-data-attributes -->
        <!-- this is used by javascript and not a real hidden field -->
        <span id="pz2session" data-value="{//request/session/pz2session}" />
    </xsl:template>

	<xsl:template name="options_sidebar">
		<div id="account" class="box">
			<h2><xsl:copy-of select="$text_header_options" /></h2>
			<ul>
				<li id="goto-options">
					<a>
						<xsl:attribute name="href">/pazpar2/options</xsl:attribute>
						<xsl:copy-of select="$text_header_my_options" />
					</a>
				</li>
	<li id="my-saved-records" class="sidebar-folder">
		<xsl:call-template name="img_save_record">
		<xsl:with-param name="id">folder</xsl:with-param>
		<xsl:with-param name="test" select="//navbar/element[@id='saved_records']/@numSessionSavedRecords &gt; 0" />
		</xsl:call-template>
		<xsl:text> </xsl:text>
		<a>
			<xsl:attribute name="href"><xsl:value-of select="//navbar/my_account_link" /></xsl:attribute>
			<xsl:copy-of select="$text_header_savedrecords" />

		</a>
	</li>
			</ul>
		</div>
	</xsl:template>


    <!-- override search/results tab so no count unless live -->
    <xsl:template name="tab"> 
        <xsl:for-each select="option"> 
            <li id="tab-{@id}">
                <xsl:choose>
                    <xsl:when test="@current = 1"> 
                        <xsl:attribute name="class">here</xsl:attribute> 
                        <a href="{@url}"> 
                            <xsl:value-of select="@public" /> <xsl:text> </xsl:text> 
                        <!-- count is wrong anyway! -->
                        <!--    <xsl:call-template name="tab_hit" /> -->
                        </a> 
                    </xsl:when>
                    <xsl:otherwise>
                        <a href="{@url}"> 
                            <xsl:value-of select="@public" />  
                        </a> 
                    </xsl:otherwise>
                </xsl:choose>
            </li> 
	</xsl:for-each> 
    </xsl:template>

</xsl:stylesheet>
