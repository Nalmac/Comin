const forms =  document.getElementsByClassName('comment-form');

for (var j = 0; j < forms.length; j++) {
	forms[j].addEventListener('submit', function(event){
		event.preventDefault();

		const url = this.action;
		const id = this.getAttribute('aria-labelledby');
		let displayClass = ".comment-" + id + "-display";
		let headerClass = displayClass + "N";

		let input = document.getElementById(id);

		if (input.value == "") {
			input = document.getElementById('modal' + id);
		}

		const modal = document.getElementById('comments' + id);
		const display = document.querySelector(displayClass);
		const header = document.querySelector(headerClass)


		var data = new FormData();
		data.append('content', input.value);

		axios({
			method: 'post',
			url: url,
			data: data,
			headers: {'Content-Type' : 'multipart/form-data'}
		}).then(function(response){
			var receivedData = response.data;
			console.log(receivedData.message);
			if (receivedData.message === "success") {

				display.innerHTML = receivedData.number + " commentaires";
				input.value = "";
				header.innerHTML = receivedData.number

				comments = receivedData.comments;
				comment = comments;
				var content = escapeHtml(comment.content)
				modal.innerHTML = `
					<div class="shadow-lg m-2 p-3">
						<h4><img src="${comment.user.avatar}" class="img-fluid w-25"><br>${comment.user.username }</h4>
						<br>
						<p class="lead">
							${content}
						</p>
					</div>					
					${modal.innerHTML}
			`;
			}

		});

	});
}
