<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<!-- vosshe@fh-trier.de -->
	<xsl:output method="text" encoding="iso-8859-1"/>
	<xsl:variable name="datafields" select="//gruppe[@key='AutorInnen' or @key='Authors']/person[position()=1]/datenfelder[position()=1]/datenfeld/@key"/>
	<xsl:variable name="zusatzangaben" select="//gruppe[@key='AutorInnen' or @key='Authors']/person[position()=1]/zusatzangaben[position()=1]/zusatzangabe/@key"/>	<xsl:template match="/">
		<xsl:text>Titel;</xsl:text>
		<xsl:text>Vorname;</xsl:text>
		<xsl:text>Nachname;</xsl:text>
		<xsl:text>Titel2;</xsl:text>
		<xsl:text>Privatadr;</xsl:text>
		<xsl:text>Privatnr;</xsl:text>
		<xsl:text>E-Mail;</xsl:text>
		<xsl:text>Anmeldedatum;</xsl:text>
		<xsl:text>Kontingent;</xsl:text>
		<xsl:text>Studiengänge;</xsl:text>
		<xsl:if test="$datafields">
			<xsl:for-each select="$datafields">
				<xsl:value-of select="."/>
				<xsl:text>;</xsl:text>
			</xsl:for-each>
		</xsl:if>
	  <xsl:if test="$zusatzangaben">
			<xsl:for-each select="$zusatzangaben">
				<xsl:value-of select="."/>
				<xsl:text>;</xsl:text>
			</xsl:for-each>
		</xsl:if>		
		<xsl:text>Bemerkung</xsl:text>
		<xsl:text>
</xsl:text>
		
		<xsl:for-each select="studip">
			<xsl:for-each select="institut">
				<xsl:for-each select="personen">
					<xsl:for-each select="gruppe">
						<xsl:call-template name="showperson"/>
					</xsl:for-each>
				</xsl:for-each>
			</xsl:for-each>
		</xsl:for-each>
	
	</xsl:template>
	
	<xsl:template name="showperson">
		<xsl:for-each select="person">
			<xsl:text>"</xsl:text>
			
			<xsl:if test="titel">
				<xsl:value-of select="titel"/>
			</xsl:if>
			<xsl:text>";"</xsl:text>
			
			<xsl:if test="vorname">
				<xsl:value-of select="vorname"/>
			</xsl:if>
			<xsl:text>";"</xsl:text>
			
			<xsl:if test="nachname">
				<xsl:value-of select="nachname"/>
			</xsl:if>
			<xsl:text>";"</xsl:text>
			
			<xsl:if test="titel2">
				<xsl:value-of select="titel2"/>
			</xsl:if>
			<xsl:text>";"</xsl:text>
			
			<xsl:if test="privadr">
				<xsl:value-of select="privadr"/>
			</xsl:if>
			<xsl:text>";"</xsl:text>
			
			<xsl:if test="privatnr">
				<xsl:value-of select="privatnr"/>
			</xsl:if>
			<xsl:text>";"</xsl:text>
			
			<xsl:if test="email">
				<xsl:value-of select="email"/>
			</xsl:if>
			<xsl:text>";"</xsl:text>
			
			<xsl:if test="datum_anmeldung">
				<xsl:value-of select="datum_anmeldung"/>
			</xsl:if>
			<xsl:text>";"</xsl:text>
			
			<xsl:if test="kontingent">
				<xsl:value-of select="kontingent"/>
			</xsl:if>
			<xsl:text>";"</xsl:text>
			
			<xsl:if test="nutzer_studiengaenge">
				<xsl:value-of select="nutzer_studiengaenge"/>
			</xsl:if>
			
			<xsl:text>";"</xsl:text>
			<xsl:call-template name="check_datafields">
				<xsl:with-param name="daten" select="datenfelder"/>
			</xsl:call-template>

			<xsl:call-template name="check_zusatzangaben">
				<xsl:with-param name="daten" select="zusatzangaben"/>
			</xsl:call-template>			

			<xsl:if test="bemerkung">
				<xsl:value-of select="translate(bemerkung,'&quot;','&#148;')"/>
			</xsl:if>
			<xsl:text>"</xsl:text>
			
			<xsl:text>
</xsl:text>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="check_datafields">
		<xsl:param name="daten"/>
		<xsl:if test="$datafields">
			<xsl:for-each select="$datafields">
				<xsl:call-template name="show_datafields">
					<xsl:with-param name="daten" select="$daten"/>
					<xsl:with-param name="datatitel" select="."/>
				</xsl:call-template>
				<xsl:text>";"</xsl:text>
			</xsl:for-each>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="check_zusatzangaben">
		<xsl:param name="daten"/>
		<xsl:if test="$zusatzangaben">
			<xsl:for-each select="$zusatzangaben">
				<xsl:call-template name="show_zusatzangaben">
					<xsl:with-param name="daten" select="$daten"/>
					<xsl:with-param name="datatitel" select="."/>
				</xsl:call-template>
				<xsl:text>";"</xsl:text>
			</xsl:for-each>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="show_datafields">
		<xsl:param name="daten"/>
		<xsl:param name="datatitel"/>
		<xsl:value-of select="normalize-space($daten/datenfeld[@key=$datatitel])"/>
	</xsl:template>

	<xsl:template name="show_zusatzangaben">
		<xsl:param name="daten"/>
		<xsl:param name="datatitel"/>
		<xsl:value-of select="normalize-space($daten/zusatzangabe[@key=$datatitel])"/>
	</xsl:template>

</xsl:stylesheet>
