<html>
	<head>
		<title>Simple Chat</title>

		<link id="favicon" rel="icon" type="image/png" href="favicon.png" />

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
				word-break: break-all;
				overflow: hidden;
				text-overflow: ellipsis;
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
				border:2px solid #fff;
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
				background:#FFFFCC;
			}

			#messageSubmitBtn {
				position: absolute;
				top:0;
				right:0;
				height: 100%;
				width: 60px;
				cursor: pointer;
				border:none;
				background-color: #fff;
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
			    messageFormEl,
			    messagesListEl,
			    errorContainerEl,
			    messageContentFieldEl,
			    messageContentFieldPosBottom,
			    messageFormIsOpacity,
			    messagesListPosTop,
				windowIsFocused,
				notifyPremissionIsRequested,
				nofifyPermissionStatus,
				uneadMessagesCount;

			uneadMessagesCount = 0;

			windowIsFocused = true;
			notifyPremissionIsRequested = false;

			errorContainerEl = $('#errorContainer');
			messageFormEl = $('#messageForm');
			messageContentFieldEl = $('#messageContentField');

			messageFormElPosBottom = parseInt(messageFormEl.css('bottom'));
			messageFormElHeight = parseInt(messageFormEl.height());

			$('#messageForm').on('submit', function() {
				var messageContent;

				messageContent = messageContentFieldEl.val().toString();

				if (messageContent !== '') {
					messageFormEl.animate({
						bottom: - messageFormElHeight * 2
					}, 300, function() {
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

								messageFormEl.animate({
									bottom: messageFormElPosBottom
								}, 300);
							},
							error: function()
							{
								errorContainerEl.html('Error ajax request.');
							}
						});
					});
				}

				// if (notifyPremissionIsRequested === false) {
				// 	try {
				// 		Notification.requestPermission(function(permission) {
				// 			nofifyPermissionStatus = permission;
				// 		});
				// 	} catch(e) {
				// 		console.log('NOTIFICATION error: ' + e);
				// 	}
				// }

				return false;
			});

			// Notifications
			function notify(message)
			{
				try {
					if (nofifyPermissionStatus === 'granted') {
						return new Notification(message);
					}
				} catch (e) {
					console.log('NOTIFICATION error: ' + e);
				}

				return false;
			}

			// Notify by Favicon
			function notifyByFavicon(message)
			{
				var canvas = document.createElement('canvas'),
					ctx,
					img = document.createElement('img'),
					linkEl = document.getElementById('favicon'),
					clonedLinkEl = linkEl.cloneNode(true);
					linkEl.parentNode.removeChild(linkEl);

				if (canvas.getContext) {
					canvas.height = canvas.width = 16;
					ctx = canvas.getContext('2d');

					img.onload = function () {
						ctx.drawImage(this, 0, 0);

						if (message !== null) {
							ctx.font = 'bold 10px "helvetica", sans-serif';
							ctx.fillStyle = '#F0EEDD';
							ctx.fillText(message.toString(), 2, 12);
						}

						clonedLinkEl.href = canvas.toDataURL('image/png');

						document.getElementsByTagName('head')[0].appendChild(clonedLinkEl);
					};

					img.src = 'favicon.png?' + Math.random();
				}
			}

			// Updating messages
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
										style = 'style="background-color:#'+data.messages[i].color+'; color:'+data.messages[i].contrast_color+';"';
									} else {
										style = '';
									}
									
									messagesListEl.append('<div class="item" '+style+'>' + data.messages[i].content + '</div>');
									lastMessageTime = data.messages[i].unixtime;
								}

								$('html, body').animate({
									scrollTop: messagesListEl.find('.item').last().scroll().offset().top
								}, 500);

								//notify(data.messages.length + ' new messages');

								if (windowIsFocused === false) {
									uneadMessagesCount += data.messages.length;

									notifyByFavicon(uneadMessagesCount);
								}
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

				setTimeout(updateMessages, 5000);
			}

			updateMessages();

			// Scroll
			messageFormIsOpacity = false;

			$(window).on('scroll', function() {
				var windowScrollTop,
					messagesListPosTop;

				windowScrollTop = $(window).scrollTop();
				messagesListPosTop = messagesListEl.offset().top;

				if ( messagesListPosTop + messagesListEl.height() - windowScrollTop > messageFormEl.offset().top - windowScrollTop ) {
					if (messageFormIsOpacity === false) {
						messageFormEl.stop().animate({opacity: 0.4}, 300);
					}

					messageFormIsOpacity = true;
				} else {
					if (messageFormIsOpacity === true) {
						messageFormEl.stop().animate({opacity: 1}, 300);
					}

					messageFormIsOpacity = false;
				}
			});

			// Focus / blur window
			$(window).on('focus', function() {
				windowIsFocused = true;
				uneadMessagesCount = 0;
				notifyByFavicon(null);
			});

			$(window).on('blur', function() {
				windowIsFocused = false;
			});
		</script>
	</body>
</html>
				