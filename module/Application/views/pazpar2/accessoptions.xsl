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
<xsl:variable name="template">access</xsl:variable>

<xsl:template name="main">

	<h1><xsl:value-of select="$text_options_module"/></h1>
	
    <xsl:call-template name="searchbox" />

    <xsl:call-template name="user-options" >
        <xsl:with-param name="template" select="$template"/>
    </xsl:call-template>
    
    <xsl:call-template name="options-body" />

</xsl:template>

<xsl:template name="options-body">
	<p style="margin-top: 1em"><xsl:copy-of select="$text_access_desc" /></p>
	<xsl:choose>
		<xsl:when test="//pazpar2options/user-options/affiliation">
        		<div class="region-targets">
                		<xsl:call-template name="access-list" />
			</div>
		</xsl:when>
		<xsl:otherwise>
			<p>So we can work out your access rights, you will first need to to enter your home institution and your role there in the <it>Institutional membership</it> section above.</p>
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

<xsl:template name="access-list">
    <form id="target-form" action="" method="post">
        <input type="hidden" name="selectedby" value="entitlements"/>
        <div id="button-column" style="float:left">
            <input type="button" id="clear-button" name="none" value="Clear all"/><br/>
            <input type="submit" id="change-targets" name="changetargets" value="Save changes"/>
        </div>
        <div id="list-column" style="float:left">
		<div style="float:left" class="yui-u first">
			<span>Search only institutions where you can:</span>
                        <ul>
                <li>
                    <input type="checkbox" name="entitlement[]" id="reference" value="reference" class="subjectDatabaseCheckbox" >
                        <xsl:if test="boolean(//pazpar2options/user-options/entitlements/entitlement = 'reference')">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:if>
                    </input>
		    <span class="subjectDatabaseTitle"> Reference works in the library</span> 
                </li>
                <li>
                    <input type="checkbox" name="entitlement[]" id="borrow" value="borrow" class="subjectDatabaseCheckbox" >
                        <xsl:if test="boolean(//pazpar2options/user-options/entitlements/entitlement = 'borrow')">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:if>
                    </input>
		    <span class="subjectDatabaseTitle"> Borrow works from the library</span> 
                </li>
                <li>
                    <input type="checkbox" name="entitlement[]" id="reserve" value="reserve" class="subjectDatabaseCheckbox" >
                        <xsl:if test="boolean(//pazpar2options/user-options/entitlements/entitlement = 'reserve')">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:if>
                    </input>
		    <span class="subjectDatabaseTitle"> Reserve works in the library</span> 
                </li>
		</ul>
	</div>
       </div>
    </form>
</xsl:template>



</xsl:stylesheet>
