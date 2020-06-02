const formsMsg = document.getElementsByClassName('msg-form');
const messages = document.getElementsByClassName('messages');

function Modal(target){
	let modal = document.querySelector(target);
	$(modal).modal('toggle');
	$(modal).on('shown.bs.modal', function(){
	    $(modal.children[0].children[0].children[1]).animate({
	    	scrollTop : modal.children[0].children[0].children[1].scrollHeight
	    }, 'slow');
	});
	var Id = target.substr(6);
	read(Id);
}

function read(id) {
	let Id = id
	axios.get("/chat/read/" + Id).then(function(response){
		var displays = document.querySelector(".msg-" + id + "-display");
		displays.innerHTML = "0 nouveaux messages";
	})
}

for (var i = 0; i < formsMsg.length; i++) {

	let id = formsMsg[i].getAttribute('aria-labelledby')

	let modal = "#modal" + id;
	let a = document.querySelector("#toggle" + id)
	$(a).on('click', Modal);
	// $(a).click(function(){
	//     read(id);
	// });
	// $(modal).on('shown.bs.modal', function(){
	//     $(this.children[0].children[0].children[1]).animate({
	//     	scrollTop : this.children[0].children[0].children[1].scrollHeight
	//     }, 'slow');
	// });

	formsMsg[i].addEventListener('submit', function(event){

		event.preventDefault();
		let url = this.action;

		let modalBody = document.querySelector(".modal-" + id + "-body");
		modalBody.scrollTop = modalBody.scrollHeight;

		let input = document.getElementById(id);

		let displayClass = ".msg-" + id + "-display";
		let modal = document.getElementById('modals' + id);
		
		const displays = document.querySelector(displayClass);

		var data = new FormData();
		data.append('content', input.value);

		axios({
			method: 'post',
			url: url,
			data: data,
			headers: {'Content-Type' : 'multipart/form-data'}
		}).then(function(response){
			var receivedData = response.data;
			var messagesBodyModal = document.getElementById('messages' + receivedData.disc)

			input.value = "";

			$(messagesBodyModal).animate({
		    	scrollTop : messagesBodyModal.scrollHeight
		    }, 'slow');

			modal.innerHTML = `${modal.innerHTML}
				<li class="list-group-item shadow-lg m-3 p-4">
					<strong>${receivedData.user_data.from} : </strong>${receivedData.message}
				</li>
			`
			}
		);
	});
}


const newDiscLink = document.getElementById('newDisc');
const discDiv = document.getElementById('discusssions');

newDiscLink.addEventListener('click', function(e){
	e.preventDefault();
	var url = this.href;
	axios.get(url).then(function(response){
	document.location.reload();

	});
});