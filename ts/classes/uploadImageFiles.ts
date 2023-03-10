let dt = new DataTransfer();

export function createImageTab() {
    let elem = document.getElementById('dropzone-file');
    elem?.addEventListener('change', getFileData);
}

function setNewFile(file: any) {
	const key = Date.now().toString(36) + Math.random().toString(36).substring(2);
	let extension = file.type.replace(/(.*)\//g, '');
	let newFile = new File([file], key + "." + extension);
	return newFile;
}

function getFileData(this: any) {
    let files = this.files;
	  for(let i = 0; i<this.files.length; i++) {
      let file = files[i];
      let newName = setNewFile(file);
      dt.items.add(newName);
      createImageList(newName.name);
	}

    const image_notif = document.querySelector('.images-notif') as HTMLDivElement;

    if(files > 0) {
        image_notif.style.display = "block";
    } else {
        const image_notif = document.querySelector('.images-notif') as HTMLDivElement;
        image_notif.style.display = "none";
    }

    console.log(dt);
}


function createImageList(file_name: string) {
    let ul_data = document.getElementById('file-image-list') as HTMLUListElement;

    let newElement = document.createElement('LI');
    newElement.setAttribute("class", "flex items-center");

    ul_data?.appendChild(newElement);

    newElement.innerHTML= `<svg data-title="${file_name}" class="w-5 h-5 mr-1.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>\
                <span class="t-ellipsis text-xs text-gray-500">${file_name}</span>`;
}


export function deleteImageList() {

    let ul_data = document.querySelector('#file-image-list') as HTMLUListElement;
    ul_data?.addEventListener('click', (e: Event) => {
        e.preventDefault();
        const target = e.target as HTMLInputElement;
        if(target && target.nodeName === "svg") {
            // target parent node bug fix
            target.parentElement?.remove();
            for(let i = 0; i < dt.items.length; i++) {
                console.log(dt.items[i].getAsFile()?.name);
                if(target.dataset.title === dt.items[i].getAsFile()?.name) {
                    dt.items.remove(i);
                    continue;
                }
            }
        }

        let ul_count = document.querySelectorAll('[id="file-image-list"] li');
        const ul_length = ul_count.length;

        if(ul_length === 0) {
            const image_notif = document.querySelector('.images-notif') as HTMLDivElement;
            image_notif.style.display = "block";
        }
    });
}

export async function postData(url = '', data = {}) {
    // Default options are marked with *
    const response = await fetch(url, {
      method: 'POST', // *GET, POST, PUT, DELETE, etc.
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data) // body data type must match "Content-Type" header
    });

    return response.json(); // parses JSON response into native JavaScript objects
  }

export function postImages() {

    // const submit = document.querySelector('#submit_data');
    // submit?.addEventListener('click', (e: Event) => {
    //     e.preventDefault();
        
    //     const filesArray = [...dt.files];
    //     const form_data = new FormData();
  
    //     for(let index = 0; index < filesArray.length; index++) {
    //         form_data.append("files[]", filesArray[index]);
    //     }

    //     console.log(filesArray);
        
    //     // fetch("/wp-content/plugins/main-nutrily/dist/upload.php", { method: 'POST', body : form_data})
    //     // .then(function(response) { 
    //     //     return response.text(); 
    //     // }).then(function(text) {
    //     //     console.log(text);
    //     // });

    // });
    const file_names = [];
    const filesArray = [...dt.files];
    const form_data = new FormData();
  
    for(let index = 0; index < filesArray.length; index++) {
        form_data.append("files[]", filesArray[index]);
    }

    for(let i = 0; i<filesArray.length; i++) {
        file_names.push({ "img_link": dt.items[i].getAsFile()?.name });
    }

    if(filesArray) {
        fetch("/wp-content/plugins/wp-nutrily-plugin-main/dist/upload.php", { method: 'POST', body : form_data})
            .then(function(response) { 
                return response.text(); 
            }).then(function(text) {
                return text;
            });
    }

    return file_names;
}




