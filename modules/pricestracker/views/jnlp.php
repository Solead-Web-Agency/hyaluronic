<jnlp spec="1.0+" codebase="<?php echo $lienArchive; ?>">

 <information>
		<title>Analyzer for PricesTracker</title>
		<vendor>PricesTracker</vendor>
        <homepage href="<?php echo $lienArchive; ?>"/>		
 </information>
 
    <security>
    	<all-permissions />
    </security>
    
	<resources>
		<j2se version="1.5+"
            href="http://java.sun.com/products/autodl/j2se" 
            initial-heap-size="512m" 
            max-heap-size="1024m"
            java-vm-args="-noverify" />

		<jar href="<?php echo $lienArchive; ?>applet.jar" main="true" download="eager" />
		<jar href="<?php echo $lienArchive; ?>commons-codec-1.6.jar" download="eager" />
		<jar href="<?php echo $lienArchive; ?>commons-logging-1.1.3.jar" download="eager" />
		<jar href="<?php echo $lienArchive; ?>fluent-hc-4.3.1.jar" download="eager" />
		<jar href="<?php echo $lienArchive; ?>httpclient-4.3.1.jar" download="eager" />
		<jar href="<?php echo $lienArchive; ?>httpclient-cache-4.3.1.jar" download="eager" />
		<jar href="<?php echo $lienArchive; ?>httpcore-4.3.jar" download="eager" />
		<jar href="<?php echo $lienArchive; ?>httpmime-4.3.1.jar download="eager"" />
		<jar href="<?php echo $lienArchive; ?>jsoup-1.7.3.jar" download="eager" />	
		<jar href="<?php echo $lienArchive; ?>groovy-all-2.2.1.jar" download="eager" />	
		<jar href="<?php echo $lienArchive; ?>java-sizeof-0.0.2.jar" download="eager" />	


	</resources>
    
    
    
    <applet-desc name="jnpl" main-class="main.Applet_VeilleConcurentielle" width="600" height="400">
        <param name="lien" value="<?php echo $lienApplet; ?>">
        <param name="pasRapprochementsTextes" value="<?php echo $pasRapprochementsTextes; ?>">
        <param name="forcerMaj" value="<?php echo $forcerMaj; ?>">
        <param name="logFichier" value="<?php echo $logFichier; ?>">
    </applet-desc>
			
            

</jnlp>