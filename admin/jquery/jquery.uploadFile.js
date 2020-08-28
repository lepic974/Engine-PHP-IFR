(function($){
	$.fn.extend({ 
		uploadFile: function(options) {
			//Settings list and the default values
			var defaults = {
				idZoneDropFile: 'filedrag',
				idZoneProgress: 'progress',
				urlAjax: 'index.php',
				functionOnSuccess: '',
				fileType: ["audio/x-mp3","audio/mpeg","audio/x-mpeg","audio/mp3","audio/mpeg3","audio/x-mpeg3","audio/mpg","audio/x-mpg","audio/x-mpegaudio","image/png","image/jpeg"]
			};
			
			var options = $.extend(defaults, options);
			var unqiueId = 1;
    	
    		function getNextId(){
        		return ++unqiueId;
    		}			
			
			function inArray(needle, haystack) {
			    var length = haystack.length;
			    for(var i = 0; i < length; i++) {
			        if(haystack[i] == needle) return true;
			    }
			    return false;
			}
			
			function UploadFile(file) {
				var opt =options;
				var xhr = new XMLHttpRequest();
				if (xhr.upload && (inArray(file.type, opt.fileType)) && file.size <= 10000000) {

					var o = $("#"+opt.idZoneProgress);
					var uniqueIdTmp = "uid_"+getNextId();
					
					o.append("<p id='"+uniqueIdTmp+"'></p>");
					var progress = $("#"+uniqueIdTmp);
					progress.html("upload " + file.name);
		
					xhr.upload.addEventListener("progress", function(e) {
						var pc = parseInt(100 - (e.loaded / e.total * 100));
						progress.css("background-position", pc + "% 0");
					}, false);
					
					xhr.onreadystatechange = function(e) {
						if (xhr.readyState == 4) {
							var name_classe = (xhr.status == 200 ? "success" : "failure");
							progress.addClass(name_classe); 
							if(name_classe == "success"){
								progress.fadeOut('slow');
								eval(opt.functionOnSuccess);
							}
						}
					};
					
					xhr.open("POST", opt.urlAjax+"&filename="+file.name, true);
					xhr.setRequestHeader("X_FILENAME", file.name);
					xhr.send(file);
				}
			}
				
			function $id(id) {
				return document.getElementById(id);
			}
			
			function FileDragHover(e) {
				e.stopPropagation();
				e.preventDefault();
				e.target.className = (e.type == "dragover" ? "hover" : "");
			}
    		
    		function FileSelectHandler(e) {
				FileDragHover(e);
				var files = e.target.files || e.dataTransfer.files;
				for (var i = 0, f; f = files[i]; i++) {
					UploadFile(f);
				}
			}
			
    		return this.each(function() {
				var o =options;
				if (window.File && window.FileList && window.FileReader) {
					var filedrag = $id(o.idZoneDropFile);

					if(filedrag){
						var xhr = new XMLHttpRequest();
						if (xhr.upload) {
							filedrag.addEventListener("dragover", FileDragHover, false);
							filedrag.addEventListener("dragleave", FileDragHover, false);
							filedrag.addEventListener("drop", FileSelectHandler, false);
							filedrag.style.display = "block";
						}
					}
				}				
    		});
    	}
	});
})(jQuery);