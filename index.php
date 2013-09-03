<html>
	<head>
		<title>Simple Chat</title>
		<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
		<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>

		<style>
			html, body {
				width:100%;
				height:100%;
				padding:0;
				margin:0;
			}

			body {
				background:#eee;
				font-family:sans-serif;
			}

			#messagesList {
				font-size:.8em;
				width:800px;
				margin:0 auto;
				padding-bottom:200px;
				padding-top:20px;
			}

			#messagesList .item {
				padding:.8em 1em;
				margin-bottom:10px;
				background:#fff;
			}

			#errorContainer {
				color:red;
			}

			#messageForm {
				position:fixed;
				bottom:70px;
				left:50%;
				margin-left:-200px;
				padding:1em;
				width:400px;
				background:#b1c8ef;
			}

			#messageContentField {
				width:350px;
			}
		</style>
	</head>
	<body>
		
		<div id="messagesList">
			
		</div>

		<form id="messageForm">
			<input id="messageContentField" name="text" value="" placeholder="Type message here&hellip;" autofocus />
			<input type="submit" value="Send" /> 
		</form>

		<div id="errorContainer">
			
		</div>

		<script>
			var lastMessageTime,
			      messagesListEl;

			$('#messageForm').on('submit', function() {
				var messageContentFieldEl,
				      messageContent,
				      errorContainerEl;

				errorContainerEl = $('#errorContainer');
				messageContentFieldEl = $('#messageContentField');
				messageContent = messageContentFieldEl.val();

				if (messageContent !== '') {
					$.ajax({
						type: 'post',
						url: 'sendMessage.php',
						dataType: 'json',
						data: {
							content: messageContent
						},
						success: function(data)
						{
							if (data.error !== false) {
								errorContainerEl.html(data.error);
							} else {
								messageContentFieldEl.val('');
								errorContainerEl.html('');
							}
						},
						error: function()
						{
							errorContainerEl.html('Error ajax request.');
						}
					});
				}

				return false;
			});

			messagesListEl = $('#messagesList');

			function updateMessages()
			{
				$.ajax({
					url: 'getMessages.php',
					type: 'get',
					dataType: 'json',
					data: {
						lastMessageTime: lastMessageTime
					},
					success: function(data)
					{
						if (data.error == false) {

							if (data.messages.length > 0) {
								for (var i=0; i<data.messages.length; i++) {
									messagesListEl.append('<div class="item">' + data.messages[i].content + '</div>');
									lastMessageTime = data.messages[i].unixtime;
								}

								$('html, body').animate({
									scrollTop: messagesListEl.find('.item').last().scroll().offset().top
								}, 2000);
							}
						} else {
							errorContainerEl.html(data.error);
						}
					},
					error: function()
					{
						errorContainerEl.html('Can\'t update messages.');
					}
				});

				setTimeout(updateMessages, 1000);
			}

			updateMessages();
		</script>
	</body>
</html>
				