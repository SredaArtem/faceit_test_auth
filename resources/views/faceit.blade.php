<!DOCTYPE html>
<html>
<head>
    <title>Вход через FACEIT</title>

    <script src="https://cdn.faceit.com/oauth/faceit-oauth-sdk-1.3.0.min.js" type="text/javascript"></script>
</head>
<body>
<a href="{{ route('faceit.login') }}">Войти через FACEIT</a>

<div id="faceitLogin">
    <button class="button Faceit" onclick="FACEIT.loginWithFaceit()">Connect with FACEIT</button>
</div>

<script type="text/javascript">
    function callback(response){
        if(response.isIdTokenValid === true){
            return;
        }
        alert('The id token is not valid, something went wrong');
    }
    var initParams = {
        client_id: '0fa94d52-41ca-41b6-afcb-ecd4d5b7fa8b',
        response_type: 'token',
        state: 'https://09b5-2a02-2378-1207-d70b-a025-ce7f-3c60-65c3.ngrok-free.app'
    };
    FACEIT.init(initParams, callback);
</script>
</body>
</html>
