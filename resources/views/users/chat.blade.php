
<html>
<head>
	<title>Chat Box</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="{{ asset('css/frontend_css/chat.css') }}">
</head>
<body>
	<div class="col-lg-4 col-lg-offset-4">
		<h1 class="chat-header">Welcome, <span id="username">{{ $username }}</span></h1>
		<div class="chat-window col-lg-12"></div>
		<div class="col-lg-12">
			<div id="typingStatus" class="col-lg-12" style="padding: 15px"></div>
			<input type="text" id="text" class="form-control col-lg-12" autofocus="" onblur="notTyping()">
		</div>
	</div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="{{ asset('js/frontend_js/chat.js') }}"></script>
</body>
</html>