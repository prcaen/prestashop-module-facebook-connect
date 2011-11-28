<!-- Module FACEBOOK CONNECT -->
<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '{$fb_connect.fb_app_id}',
      status     : true, 
      cookie     : true,
      xfbml      : true
    });
  };
  (function(d){
     var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {
       return;
     }
     js = d.createElement('script'); js.id = id; js.async = true;
     js.src = "//connect.facebook.net/{$fb_connect.fb_country}/all.js";
     d.getElementsByTagName('head')[0].appendChild(js);
   }(document));
</script>
<div class="fb-login-button" data-perms="{$fb_connect.fb_perms}">{l s='Login with Facebook' mod='facebookconnect'}</div>
<!-- /Module FACEBOOK CONNECT -->