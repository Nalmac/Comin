function handleFileSelect(evt) {
    var files = evt.target.files; // FileList object


    // Loop through the FileList and render image files as thumbnails.
    for (var i = 0, f; f = files[i]; i++) {

      let ext = f.name.split('.').pop();
      let audioExts = ['mp3', 'wma', 'wav'];

      // Only process image files.
      if (!f.type.match('image.*') && !f.type.match('video.*') && !f.type.match('audio.*') && !audioExts.includes(ext)) {
        continue;
      }
      console.log('debug');
      var reader = new FileReader();

      if (f.type.match('image.*')) 
      {
        // Closure to capture the file information.
        reader.onload = (function(theFile) {
        return function(e) {
          // Render thumbnail.
          var display = document.querySelector('#displayImg');
          display.className = "img-fluid";
          display.src = e.target.result;
          var display2 = document.querySelector('#displayVideo');
          var display3 = document.querySelector('#displayAudio');
          display2.className = 'd-none';
          display3.className = 'd-none';
        };
        })(f);
        console.log('debugI');
      }
      else
      {
        if (f.type.match('video.*')) 
        {
          // Closure to capture the file information.
          reader.onload = (function(theFile) {
            return function(e) {
              // Render thumbnail.
              var display = document.querySelector('#displayVideo');
              display.className = "d-block";
              display.src = e.target.result;
              var display2 = document.querySelector('#displayImg');
              var display3 = document.querySelector('#displayAudio');
              display2.className = 'd-none';
              display3.className = 'd-none';
            };
          })(f);
          console.log('debugV');
        }
      else 
      {
        // Closure to capture the file information.
        reader.onload = (function(theFile) {
          return function(e) {
            // Render thumbnail.
            var display = document.querySelector('#displayAudio');
            display.className = "d-block";
            display.src = e.target.result;
            var display2 = document.querySelector('#displayImg');
            var display3 = document.querySelector('#displayVideo');
            display2.className = 'd-none';
            display3.className = 'd-none';
          };
        })(f);
        console.log('debugA');
      }
    }


      

      // Read in the image file as a data URL.
      reader.readAsDataURL(f);
    }
  }

document.getElementById('post_path').addEventListener('change', handleFileSelect, false);