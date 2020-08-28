$().ready(function() {
	(function() {
		// getElementById
		function $id(id) {
			return document.getElementById(id);
		}
	
		// file drag hover
		function FileDragHover(e) {
			e.stopPropagation();
			e.preventDefault();
			e.target.className = (e.type == "dragover" ? "hover" : "");
		}
	
		// file selection
		function FileSelectHandler(e) {
			// cancel event and hover styling
			FileDragHover(e);
			// fetch FileList object
			var files = e.target.files || e.dataTransfer.files;
			// process all File objects
			for (var i = 0, f; f = files[i]; i++) {
				UploadFile(f);
			}
		}
	
		// upload JPEG files
		function UploadFile(file) {
			var xhr = new XMLHttpRequest();
                if (xhr.upload && (file.type == "image/jpeg" || file.type == "image/png") && file.size <= 5000000) {
				// file received/failed
				xhr.onreadystatechange = function(e) {
					if (xhr.readyState == 4) {
						var name_classe = (xhr.status == 200 ? "success" : "failure");
						if(name_classe == "success"){
							window.location = 'index.php';
						}
					}
				};
	
				// start upload
				xhr.open("POST", "index.php?to=ajax_common&_ajax_action=change_background&filename="+file.name, true);
				xhr.setRequestHeader("X_FILENAME", file.name);
				xhr.send(file);
			}
		}
	
	
		// initialize
		function Init() {
	
			var filedrag = $id("background_drag");
	
			// file select
			if(filedrag){
				// is XHR2 available?
				var xhr = new XMLHttpRequest();
				if (xhr.upload) {
					// file drop
					filedrag.addEventListener("dragover", FileDragHover, false);
					filedrag.addEventListener("dragleave", FileDragHover, false);
					filedrag.addEventListener("drop", FileSelectHandler, false);
					filedrag.style.display = "block";
				}
			}
		}
	
		// call initialization file
		if (window.File && window.FileList && window.FileReader) {
			Init();
		}
	
		var unqiueId = 1;
    	function getNextId(){
        	return ++unqiueId;
    	}	
	})();
});