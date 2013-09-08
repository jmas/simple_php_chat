<html>
	<head>
		<title>Simple Chat</title>

		<link id="favicon" rel="icon" type="image/png" href="favicon.png" />

		<script src="//code.jquery.com/jquery-1.10.1.min.js"></script>
		<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
		<script src="//ulogin.ru/js/ulogin.js"></script>

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
				position: relative;
				font-size:.8em;
				max-width: 760px;
				margin:0 auto;
				padding-top:20px;
				padding-bottom:200px;
				padding-right: 20px;
				padding-left: 20px;
			}

			#messagesList .item {
				position: relative;
				padding:.8em 1em;
				margin-bottom:10px;
				background:#fff;
				word-break: break-all;
				/*overflow: hidden;*/
				text-overflow: ellipsis;
			}

			#messagesList .user {
				display: block;
				position: absolute;
				left:-34px;
				bottom:0;
				width:30px;
				height:30px;
				background-size:100% 100%;
				background:#fff;
				padding: 2px;
			}

			#messagesList .user:after {
				position: absolute;
				bottom:0;
				right:-7px;
				width: 0;
			    height: 0;
			    border-bottom: 7px solid #fff; 
    			border-right: 7px solid transparent;
			    content: '';
			}

			#messagesList .photo {
				display: block;
				width:30px;
				height:30px;
				background-size:100% 100%;
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

			#userContainer {
				position: fixed;
				top:30px;
				right:30px;
				padding:15px;
				background:#fff;
			}

			#userInfoContainer {
				display:none;
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

		<div id="userContainer">
			<div id="uLogin" data-ulogin="display=small;fields=first_name,last_name,photo,photo_big;providers=vkontakte,odnoklassniki,mailru,facebook;hidden=other;redirect_uri=;callback=uloginCallbackHandler"></div>
			<div id="userInfoContainer">
				<span id="userInfoName"></span>
				<a id="userLogout" href="#">Logout</a>
			</div>
		</div>

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
				unreadMessagesCount,
				loggedUserData;

			unreadMessagesCount = 0;

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
				} else {
					messageContentFieldEl.stop().animate({
						marginLeft: -50
					}, 200).animate({
						marginLeft: 50
					}, 200).animate({
						marginLeft: -50
					}, 200).animate({
						marginLeft: 0
					}, 200);
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
				var canvas, ctx, img, linkEl, clonedLinkEl;
					
					canvas = document.createElement('canvas');
					img = document.createElement('img');
					linkEl = document.getElementById('favicon');

					if (linkEl === null) {
						linkEl = document.createElement('link');
						linkEl.id   = 'favicon';
						linkEl.rel  = 'icon';
						linkEl.type = 'image/png';
					} else {
						linkEl.parentNode.removeChild(linkEl);
					}

					clonedLinkEl = linkEl.cloneNode(true);

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
				$.ajax({
					url: 'getMessages.php',
					type: 'get',
					dataType: 'json',
					data: {
						lastMessageTime: lastMessageTime
					},
					success: function(data)
					{
						var style,
							itemEl,
							user;

						if (data.error == false) {
							if (data.messages.length > 0) {
								for (var i=0; i<data.messages.length; i++) {
									style = 'opacity:0;';

									if (data.messages[i].color !== undefined && data.messages[i].contrast_color !== undefined
										&& data.messages[i].color !== '' && data.messages[i].contrast_color !== '') {
										style += 'background-color:#'+data.messages[i].color+'; color:'+data.messages[i].contrast_color+';';									}
										user = data.messages[i].user_photo !== null ? '<span class="user"><a href="'+data.messages[i].user_profile+'" target="_blank"><span class="photo" style="background-image:url('+data.messages[i].user_photo+');"></span></a></span>': '';

									itemEl = $('<div class="item" style="'+style+'">' + user + data.messages[i].content + '</div>');
									
									messagesListEl.append(itemEl);

									itemEl.animate({
										opacity: 1
									}, 300);

									lastMessageTime = data.messages[i].unixtime;
								}

								$('html, body').animate({
									scrollTop: messagesListEl.find('.item').last().scroll().offset().top
								}, 500);

								//notify(data.messages.length + ' new messages');

								if (windowIsFocused === false) {
									unreadMessagesCount += data.messages.length;

									notifyByFavicon(unreadMessagesCount);
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
				unreadMessagesCount = 0;
				notifyByFavicon(null);
			});

			$(window).on('blur', function() {
				windowIsFocused = false;
			});

			// Login
			function getUser(callbackHandler)
			{
				$.ajax({
					url: 'getUser.php',
					type: 'get',
					dataType: 'json',
					success: function(data)
					{
						if (data.error == false) {
							callbackHandler(data.user);
						} else {
							callbackHandler(null);
							errorContainerEl.html(data.error);
						}
					},
					error: function()
					{
						callbackHandler(null);
						errorContainerEl.html('Can\'t login user.');
					}
				});
			}

			function logoutUser(callbackHandler)
			{
				$.ajax({
					url: 'logoutUser.php',
					type: 'get',
					dataType: 'json',
					success: function(data)
					{
						if (data.error == false) {
							callbackHandler(true);
						} else {
							callbackHandler(false);
							errorContainerEl.html(data.error);
						}
					},
					error: function()
					{
						callbackHandler(false);
						errorContainerEl.html('Can\'t logout user.');
					}
				});
			}

			function uloginCallbackHandler(token)
			{
				$.ajax({
					url: 'loginUser.php',
					type: 'get',
					dataType: 'json',
					data: {
						token: token
					},
					success: function(data)
					{
						if (data.error == false) {
							getUser(setLoggedUser);
						} else {
							errorContainerEl.html(data.error);
						}
					},
					error: function()
					{
						errorContainerEl.html('Can\'t login user.');
					}
				});
			}

			function setLoggedUser(userData)
			{
				var uloginContainerEl,
					userInfoContainerEl,
					userInfoNameEl;

				uloginContainerEl = $('#uLogin');
				userInfoContainerEl = $('#userInfoContainer');
				userInfoNameEl = $('#userInfoName');

				loggedUserData = userData;

				if (userData === null) {
					uloginContainerEl.show();
					userInfoContainerEl.hide();
				} else {
					uloginContainerEl.hide();
					userInfoContainerEl.show();

					userInfoNameEl.html(loggedUserData.first_name + ' ' + loggedUserData.last_name);
				}
			}

			getUser(setLoggedUser);

			$('#userLogout').on('click', function() {
				logoutUser(function(isLoggedout) {
					if (isLoggedout === true) {
						setLoggedUser(null);
					}
				});

				return false;
			});
		</script>
	</body>
</html>
				