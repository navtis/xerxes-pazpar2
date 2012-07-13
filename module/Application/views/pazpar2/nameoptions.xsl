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


<xsl:stylesheet version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="options.xsl"/>

<!-- template name used by options.xsl for tabs -->
<xsl:variable name="template">name</xsl:variable>

<xsl:template name="main">

    <h1><xsl:value-of select="$text_options_module" /></h1>
        
    <xsl:call-template name="searchbox" />

    <xsl:call-template name="user-options" >
        <xsl:with-param name="template" select="$template"/>
    </xsl:call-template>
    
    <xsl:call-template name="options-body" />

</xsl:template>

<xsl:template name="options-body">
    <p style="margin-top: 1em" ><xsl:copy-of select="$text_libraries_desc" /></p>
    <div class="region-targets">
        <xsl:call-template name="target-list" />
    </div>
</xsl:template>

<xsl:template name="target-list">
    <form id="target-form" action="" method="post">
        <input type="hidden" name="selectedby" value="names"/>
        <div id="button-column" style="float:left">
            <input type="button" id="all-button" name="all" value="Select all"/><br/>
            <input type="button" id="clear-button" name="none" value="Clear all"/><br/>
            <input type="submit" id="change-targets" name="changetargets" value="Save changes"/>
        </div>
	<div id="list-column" style="float:left">
		<xsl:call-template name="loop_columns">
			<xsl:with-param name="num_columns">2</xsl:with-param>
		</xsl:call-template>
       </div>
    </form>
</xsl:template>


<!-- 
        TEMPLATE: LOOP_COLUMNS
        
        A recursively called looping template for dynamically determined number of columns.
        produces the following logic 
        
        for ($i = $initial-value; $i<=$maxount; ($i = $i + 1)) {
                // print column
        }
-->
<xsl:template name="loop_columns">
        <xsl:param name="num_columns"/>
        <xsl:param name="iteration_value">1</xsl:param>
        
	<xsl:variable name="total" select="count(//all-targets/target)" />
	<xsl:variable name="numRows" select="ceiling($total div $num_columns)"/>
        <xsl:if test="$iteration_value &lt;= $num_columns">
                <div style="float: left">
                <xsl:attribute name="class">
                        <xsl:text>yui-u</xsl:text><xsl:if test="$iteration_value = 1"><xsl:text> first</xsl:text></xsl:if>
		</xsl:attribute>
		<ul>
			<xsl:for-each select="//all-targets/target[@position &gt; ($numRows * ($iteration_value -1)) and @position &lt;= ( $numRows * $iteration_value )]">
                <xsl:variable name="key"><xsl:value-of select="@target_id"/></xsl:variable>
                <li>
                        <input type="checkbox" name="target[]" id="{$key}" value="{$key}" class="subjectDatabaseCheckbox" >
                                <xsl:if test="boolean(//pazpar2options/user-options/targets/target = $key)">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:if>
                    </input>
                    <span class="subjectDatabaseTitle"> <a href="{library_url}" title="Go directly to {display_name}"><xsl:value-of select="display_name" /></a></span> <span class="subjectDatabaseInfo"><a title="More information about {display_name}" href="/pazpar2/library?target={@target_id}"> <img src="images/info.gif" alt="More information about {display_name}" /></a></span>
                </li>
            </xsl:for-each>
                        
    </ul>
	</div>        
                <xsl:call-template name="loop_columns">
                        <xsl:with-param name="num_columns" select="$num_columns"/>
                        <xsl:with-param name="iteration_value"  select="$iteration_value+1"/>
                </xsl:call-template>
        </xsl:if>
        
</xsl:template>


</xsl:stylesheet>
