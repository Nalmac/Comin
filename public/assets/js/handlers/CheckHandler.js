const errorMessageDiv = document.getElementById('dialog');

document.getElementById('accountCheck').addEventListener('submit', function(e){
	e.preventDefault();
	const url = this.action;
	var form = this;
	data = new FormData(form);
	axios({
		method: 'post',
		url: url,
		data: data,
		headers: {'Content-Type' : 'multipart/form-data'}
	}).then(function(response){
		if (response.data.status == "success") {
			errorMessageDiv.innerHTML = `
				<div class="alert alert-dismissible alert-success fade show">
					Nice !
					<button class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			`;
			$(form.parentElement.parentElement.parentElement.parentElement).modal('toggle');
		} else {
			errorMessageDiv.innerHTML = `
				<div class="alert alert-dismissible alert-danger fade show">
					Le mot de passe fourni est incorrect, impossible de procéder à la suppression du compte.
					<button class="close" data-dismiss="alert">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			`;
			$(form.parentElement.parentElement.parentElement.parentElement).modal('toggle');
		}
	});
});
