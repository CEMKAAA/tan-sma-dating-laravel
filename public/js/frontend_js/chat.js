var username;
$(document).ready(function(){
	username = $('#username').html();
	pullData();
	$(document).keyup(function(e){
		if(e.keyCode == 13){
			sendMessage(); // If user press enter
		}else{
			isTyping(); // If user just writing
		}
	});
});

function pullData(){
	retrieveChatMessages();
	retrieveTypingStatus();
	setTimeout(pullData,5000);
}

function retrieveChatMessages(){
	$.post('/chat/retrieveChatMessages',{username:username},
		function(data){
			if(data.length > 0){
				$('.chat-window').append('<br><div>'+data+'</div><br>');
			}
		});
}

function retrieveTypingStatus(){
	$.post('/chat/retrieveTypingStatus',{username:username},
		function(username){
			if(username.length > 0){
				$("#typingStatus").html(username+' is typing');
			}else{
				$("#typingStatus").html('');
			}
		})
}

function sendMessage()
{
	var text = $('#text').val();
	if(text.length > 0){
		$.post('/chat/sendMessage',{text:text,username:username},function(){
			$('.chat-window').append('<br><div style="text-align:right"><strong>'+username+': </strong>'+text+'</div><br>');
			$("#text").val("");
			notTyping();
		});
	}
}

function isTyping(){
	username = $('#username').html();
	$.post('/chat/isTyping',{username:username});
}

function notTyping(){
	$.post('/chat/notTyping',{username:username});
}