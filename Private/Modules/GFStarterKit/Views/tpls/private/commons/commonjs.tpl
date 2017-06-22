
<!-- for IE support -->
<script src="modules/GFStarterKit/components/es6-promise/es6-promise.auto.min.js"></script>

<script src="modules/GFStarterKit/components/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="modules/GFStarterKit/components/material-design-lite/material.min.js"></script>
<script src="modules/GFStarterKit/components/jquery/dist/jquery.min.js"></script>
<script src="modules/GFStarterKit/js/dashboard/dashboard.js"></script>

 <div id="js-loading-chunk" style="display: none">
 	<div class="chunk-showbox demo-graphs mdl-shadow--2dp mdl-color--white mdl-cell mdl-cell--12-col">
	  <div class="chunk-loader">
	    <svg class="chunk-circular" viewBox="25 25 50 50">
	      <circle class="chunk-path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
	    </svg>
	  </div>
	</div>
 </div>
 
 <script>
	 function showChunkLoading(element) {
		 var loading = $('#js-loading-chunk').html();
		 element.html(loading);
	 }
	 function hideChunkLoading(element) {
		 element.find('.chunk-showbox').remove();
	 }
	 function handleAjaxError(xhr) {
		  var message = "No se pudo procesar su solicitud en estos instantes.";
		  if(xhr.responseText != undefined && xhr.responseText != "") {
			  message = JSON.parse(xhr.responseText);
			  message = message.msg;
		  }
		  swal("¡Error!", message, "error");   
		
	  }
	 $( document ).ready(function() {
		 $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
			    if (originalOptions.type !== 'POST' || options.type !== 'POST') {
			        return;
			    }
			    postData = {};
			    postData[$('#csrf').attr('name')] = $('#csrf').val();
				options.data = typeof options.data === "string" ? $('#csrf').attr('name')+"="+ $('#csrf').val()+"&"+options.data : $.extend(options.data, postData);
			});
		 
	 });
	
 </script>
