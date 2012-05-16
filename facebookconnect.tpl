<!-- Module FACEBOOK CONNECT -->
<div id="fb-root"></div>
<script>
window.fbAsyncInit = function() {
	FB.init({
		appId	 : '{$fb_connect.fb_app_id}',
		channelUrl : '{$link->getPageLink('authentication.php')}',
		status : true,
		cookie : true,
		xfbml	 : true
	});

	FB.getLoginStatus(function(response) {
		if (response.status === 'connected') {
			FB.api('/me', function(response) {
				$('#email').val(response.email);
				$('#passwd').focus();
			});
		}
	});

	FB.Event.subscribe('auth.login', function(response){
		FB.api('/me', function(response) {
			$('.fb_first_name').val(response.first_name);
			$('.fb_last_name').val(response.last_name);
			$('#email_create').val(response.email);
	
			if(response.gender == 'male')
				$('.fb_gender').val(1);
			else
				$('.fb_gender').val(2);
			if(response.birthday) {
				var birthdaySplit = response.birthday.split('/');
				$('.days').val(birthdaySplit[1]);
				$('.months').val(birthdaySplit[0]);
				$('.years').val(birthdaySplit[2]);
			}
	
			$('#create-account_form').submit();
		});
	});
};
(function(d){
	var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
	{literal}if (d.getElementById(id)) {return;}{/literal}
	js = d.createElement('script'); js.id = id; js.async = true;
	js.src = "//connect.facebook.net/{$fb_connect.fb_country}/all.js";
	ref.parentNode.insertBefore(js, ref);
}(document));

$(document).ready(function() {
	$('#facebookconnect_login').click(onClickFBConnectLogin);
});

function onClickFBConnectLogin(e){
	e.preventDefault();
	FB.login();
}
</script>
<input type="hidden" name="customer_firstname" class="fb_first_name" />
<input type="hidden" name="customer_lastname" class="fb_last_name" />
<input type="hidden" name="id_gender" class="fb_gender" />
<input type="hidden" name="days" class="days" />
<input type="hidden" name="months" class="months" />
<input type="hidden" name="years" class="years" />
<div id="facebookconnect">
	<p style="text-align: center">{l s='Ou connectez-vous via Facebook'}</p>
	<a href="#" id="facebookconnect_login">{l s='Login with Facebook' mod='facebookconnect'}</a>
	{*<div class="fb-login-button" scope="{$fb_connect.fb_perms}, email" data-show-faces="false" data-width="200" data-max-rows="1">{l s='Login with Facebook' mod='facebookconnect'}</div>*}
</div>
<!-- /Module FACEBOOK CONNECT -->