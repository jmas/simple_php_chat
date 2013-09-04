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
				min-width: 400px;
			}

			#messagesList {
				font-size:.8em;
				max-width: 760px;
				margin:0 auto;
				padding-top:20px;
				padding-bottom:200px;
				padding-right: 20px;
				padding-left: 20px;
			}

			#messagesList .item {
				padding:.8em 1em;
				margin-bottom:10px;
				background:#fff;
			}

			#errorContainer {
				position:fixed;
				bottom:30px;
				left:50%;
				color:red;
				width:400px;
				margin:0 auto;
				margin-left:-200px;
				font-size:.8em;
				text-align:center;
			}

			#messageForm {
				position:fixed;
				bottom:70px;
				left:50%;
				margin-left:-200px;
				width:400px;
				background:#b1c8ef;
				overflow: hidden;
				padding: 0;
				border:2px solid #b1c8ef;
			}

			#messageContentField {
				width:85%;
				padding:.3em;
				padding-right: 15px;
				font-size: 1.1em;
				margin: 0;
				background-color: #fff;
				border:none;
				outline: none;
				color:#262626;
			}

			#messageContentField:focus {
				background-color: #ffe9b2;
			}

			#messageSubmitBtn {
				position: absolute;
				top:0;
				right:0;
				height: 100%;
				width: 60px;
				cursor: pointer;
				border:none;
				background-color: #b1c8ef;
				font-weight: bold;
			}
		</style>
	</head>
	<body>
		
		<div id="messagesList">
			
		</div>

		<div id="errorContainer">
			
		</div>

		<form id="messageForm">
			<input id="messageContentField" name="text" value="" placeholder="Type message here&hellip;" autofocus />
			<input id="messageSubmitBtn" type="submit" value="Send" /> 
		</form>

		<script>
			var lastMessageTime,
			      messagesListEl,
			      errorContainerEl;

			errorContainerEl = $('#errorContainer');

			$('#messageForm').on('submit', function() {
				var messageContentFieldEl,
				      messageContent;

				messageContentFieldEl = $('#messageContentField');
				messageContent = messageContentFieldEl.val().toString();

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
				var style;

				style = '';

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
									if (data.messages[i].color !== undefined && data.messages[i].contrast_color !== undefined
										&& data.messages[i].color !== '' && data.messages[i].contrast_color !== '') {
										style = 'style="background-color:#'+data.messages[i].color+'; color:'+data.messages[i].contrast_color+'"';
									} else {
										style = '';
									}
									
									messagesListEl.append('<div class="item" '+style+'>' + data.messages[i].content + '</div>');
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
				