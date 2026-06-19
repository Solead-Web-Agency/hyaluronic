<!DOCTYPE HTML>
<html>
<head>
    <title>Statistics</title>
    
    <style type="text/css">
        html, body, #wpt-container {width:100%;height:99%;}
    </style>
    
<link rel='stylesheet' href='../modules/pricestracker/pages/tableaucroise/brightsea/wpt/wpt.css'>

<script type="text/javascript" src="../modules/pricestracker/pages/tableaucroise/brightsea/dojo/dojo.js"  data-dojo-config="async:1"></script>
<script type="text/javascript" src="../modules/pricestracker/pages/tableaucroise/jquery/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="../modules/pricestracker/pages/tableaucroise/highcharts/4.0.1/highcharts-all.js"></script>
<script type="text/javascript" src="../modules/pricestracker/pages/tableaucroise/highcharts/group_categories/grouped-categories.js"></script>

    <script type="text/javascript">
		var mesOptions={
					uiFlags: {
						dataSourceBtn: 0
					},
                    filepicker:{
                        key:"A4bieoUsyR4yBrNPkFIvrz"  //bi2.io
                    }

                };
		var webPivotTable;
        require(["wpt/WebPivotTable","dojo/domReady!"], function(WebPivotTable){
            webPivotTable  = new WebPivotTable({
                customOptions:mesOptions
            },"wpt-container");
			
			webPivotTable.setCsvUrl('<?php echo $lien.'&statsAjax'; ?>');
        });
		
		function filtres()
		{
			mesOptions.filters=[1,2];
			webPivotTable.setOptions(mesOptions);
		}
	</script>
</head>

<body class="claro">
<script>
var confirmOnLeave = function(msg) {
 
    window.onbeforeunload = function (e) {
        e = e || window.event;
        msg = msg || '';
 
        // For IE and Firefox
        if (e) {e.returnValue = msg;}
 
        // For Chrome and Safari
        return msg;
    };
 
};
  
// message de confirmation personnalisé
confirmOnLeave('Vous allez perdre votre travail, êtes vous sûr(e) de vouloir quitter la page ?');
</script>
<div id="top"><a href="<?php echo $lien.'&about'; ?>">Return in PricesTracker</a></div>
	<div id="wpt-container"></div>
</body>
</html>


