<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<html>
		<form name="get_access_token" method="" action="" id="req_access_token">
			<input type="hidden" name="grant_type" value="authorization_code"/>
			<input type="hidden" name="client_id" value="5f761211014489008a92"/>
			<input type="hidden" name="client_secret" value="fcec5ac4abf50f904d295381c74f46cb7330dd3f"/>
			<input type="hidden" name="code" value="<?php echo $code; ?>"/>
			<input type="hidden" name="scope" value="read+write"/>
			<input type="hidden" name="redirect_uri" value="http://fuseddocs.com/manage-panda-account/save"/>
			<input type="hidden" name="Content-Type" value="x-www-form-urlencoded"/>
		</form>
	</html>



	<script>
		$(document).ready(function(){
			
			$.ajax({
			'type': 'post',
			'url' : 'https://api.pandadoc.com/oauth2/access_token',
			'data': $('#req_access_token').serialize(),
			'dataType': 'json',
			success: function(response){
				if( response ) {
					var access_token = response.access_token;
					var expires_in	 = response.expires_in;
					var refresh_token = response.refresh_token;
					
					$.ajax({
						
						'type': 'get',
						'url' : '<?php echo url('save-access-token'); ?>',
						'data': { 'access_token': access_token, 'expires_in':expires_in,'refresh_token':refresh_token},
						'dataType': 'text',
						success: function(response){
							if ( response == "success") {
							window.location.href="<?php echo url('manage-panda-account'); ?>";
							}
						}
						
					});
					
				} 
			}
			});
			//~ $("#req_access_token").submit();
		});
	</script>
