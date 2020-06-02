	const url = new URL('http://192.168.0.185:3000/.well-known/mercure');
	url.searchParams.append('topic', 'http://realtime/topics/posts');
	url.searchParams.append('topic', 'http://realtime/users/subs');
	url.searchParams.append('topic', 'http://realtime/posts/like');
	url.searchParams.append('topic', 'http://realtime/posts/comment');
	url.searchParams.append('topic', 'http://realtime/chat/msg');

	let notifsNumber = 0;

	const eventSource = new EventSource(url, {withCredentials: true});
	eventSource.onmessage = event =>{
		let data = JSON.parse(event.data);
		let notif = data.notif;
		var div = document.querySelector('#notifs');
		let html = `<li class="list-group-item">${notif}</li>${div.innerHTML}`;

		if (notif == "Like") {
			let likeDisplay = document.getElementById(data.post + "likes");
			likeDisplay.innerHTML = data.likes + " likes";
		} else {
			if (notif == "Comment") {
				let commentDisplay = document.getElementById(data.post + "comments");
				commentDisplay.innerHTML = data.comments + " commentaires";
			} else {

				if (notif == "message") {
					let message = data.message
					let displayClass = ".msg-" + message.disc + "-display";
					var display = document.querySelector(displayClass);
					var div2 = document.getElementById('modals' + message.disc)
					let overflowDivBody = document.getElementById('messages' + message.disc)
					div2.innerHTML = div2.innerHTML + `
						<li class="list-group-item shadow-lg m-3 p-4">
							<strong>${message.user} : </strong>${message.content}
						</li>
					`;
					$(overflowDivBody).animate({
				   		scrollTop : overflowDivBody.scrollHeight
				    }, 'slow');

				    if (overflowDivBody.scrollHeight == 0) {
						display.innerHTML = message.new + " nouveaux messages"
						div.innerHTML = div.innerHTML + "Vous avez un nouveau message de " + message.user
						notifsNumber++;

						var badge = document.querySelector("#notifsNumber");
						badge.innerHTML = notifsNumber;
				    }else{
				    	read(message.disc);
				    }
					


				} else {

					div.innerHTML = html;

					notifsNumber++;

					var badge = document.querySelector("#notifsNumber");
					badge.innerHTML = notifsNumber;
			}}
		}
		
		


	};

	function deleteNotifs() {
		var div = document.querySelector('#notifs');
		var badge = document.querySelector("#notifsNumber");
		div.innerHTML = "";
		notifsNumber = 0;
		badge.innerHTML = notifsNumber;
	}