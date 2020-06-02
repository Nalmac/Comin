
let links = document.getElementsByClassName('js-like');
let badges = document.getElementsByClassName('like-display');
		
for (k = 0; k < links.length; ++k) {
  	links[k].addEventListener('click', function(e) {
  		e.preventDefault();
		const url = this.href;

		axios.get(url).then(function(response){
			var data = response.data;
			for (var h = 0; h < badges.length; h++) {
				badges[h].innerHTML = data.likes + " likes";
			}

			for (var l = 0; l < links.length; l++) {
				if (data.action == "add") {
					links[l].innerHTML =  `<i class="fas fa-thumbs-up"></i>Je n'aime plus`;
				} else {
					links[l].innerHTML =  `<i class="far fa-thumbs-up"></i>J'aime`;
				}
			}
		});
 	});
}


