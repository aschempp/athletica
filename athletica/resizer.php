<?php
$lastsize = 350;
$width = (isset($_COOKIE['meeting_entries_list_size'])) ? $_COOKIE['meeting_entries_list_size'] : $lastsize;
?>
<html>
	<head>
		<script type="text/javascript">
			// Letzte Position des Objekts
			var last = 0;
			var lastsize = <?php echo $lastsize; ?>;

			//Das Objekt, das gerade bewegt wird.
			var dragobjekt = null;

			// Position, an der das Objekt angeklickt wurde.
			var dragx = 0;

			// Mausposition
			var posx = 0;
			
			set_size(<?php echo $width; ?>);

			function draginit() {
				// Initialisierung der Überwachung der Events

				document.onmousemove = drag;
				document.onmouseup = dragstop;
			}


			function dragstart(element) {
				//Wird aufgerufen, wenn ein Objekt bewegt werden soll.
				var BrowserName = navigator.appName;

				// Firefox packt das Verkleinern nicht, deshalb nur mit Klick!
				if(BrowserName!='Netscape'){
					dragobjekt = element;
					dragx = posx - dragobjekt.offsetLeft;
				}
			}


			function dragstop() {
				//Wird aufgerufen, wenn ein Objekt nicht mehr bewegt werden soll.

				dragobjekt = null;
				
				var cols = parent.document.getElementById('frm_list').cols.split(',');
				var col1 = parseInt(cols[0]);
				
				set_size(col1, true);
			}


			function drag(ereignis) {
				// Wird aufgerufen, wenn die Maus bewegt wird und bewegt bei Bedarf das Objekt.

				posx = document.all ? window.event.clientX : ereignis.pageX;
				posy = document.all ? window.event.clientY : ereignis.pageY;
				if(dragobjekt != null) {
					if(posx!=last){
						var cols = parent.document.getElementById('frm_list').cols.split(',');
						var col1 = parseInt(cols[0]);

						if(posx<last){
							col1 = (col1 - (last - posx));
						} else {
							col1 = (col1 + ((posx - (dragx - posx)) + last));
						}

						lastsize = col1;
						
						set_size(col1, false);
					}
				}
			}

			function minmax(){
				var cols = parent.document.getElementById('frm_list').cols.split(',');
				var col1 = parseInt(cols[0]);

				col1 = (col1==0) ? lastsize : 0;
				
				set_size(col1, true);
			}
			
			function set_size(size, setcookie){
				parent.document.getElementById('frm_list').cols = size+',7,*';
				if(setcookie){
					set_cookie('meeting_entries_list_size', size);
				}
			}
			
			function set_cookie(name, value){
				req = null;
				
				try {
					req = new ActiveXObject('Msxml2.XMLHTTP');
				} catch(e){
					try {
						req = new ActiveXObject('Microsoft.XMLHTTP');
					} catch(oc) {
						req = null;
					}
				}
				
				if(!req && typeof XMLHttpRequest!='undefined'){
					req = new XMLHttpRequest();
				}
				
				if(req!=null){
					req.onreadystatechange = res;
					req.open('GET', 'setcookie.php?name='+name+'&value='+value, true);
					req.send(null);
				}
			}

			function res(){
				// leer lassen, Kompatibilität
			}
			//-->
		</script>
	</head>
	<body onload="draginit()" style="background-color: #999999; margin: 0px;">
		<div style="background-color: #999999; background-image: url(img/resizer.gif); background-position: center; background-repeat: no-repeat; width: 7px; height: 500px; cursor: e-resize;" onmousedown="dragstart(this)" ondblclick="minmax();"></div>
	</body>
</html>