
function setDragEvent() {


    var draggableItems = document.querySelectorAll('div[data-id]');

    for(var i = 0; i < draggableItems.length; i++) {
    
        draggableItems[i].addEventListener('dragstart', function(e) {
            var id = '';
            if(e.target.dataset)
                id = e.target.dataset['id'];
            else
                id = e.target.getAttribute('data-id');
            if(!id) {
                return false;
            }
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/plain', id);
            document.getElementById('dropArea').setAttribute('class', 'open'); 
            return true;
        }, true);

        draggableItems[i].addEventListener('dragend', function(e) {
            document.getElementById('dropArea').setAttribute('class', ''); 
            return true;
        }, true);
    }
    
    
    var dropZone = document.querySelectorAll('p#dropFavorite, p#dropAlbum');
    
    for(var i = 0; i < dropZone.length; i++) {
    
        dropZone[i].addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = e.dataTransfer.getData('text/plain');
            if(!id)
                return false;
            switch(e.target.id) {
                case 'dropFavorite':
                    location.href = 'Favorite.Add.' + id;
                    break;
                case 'dropAlbum':
                    location.href = 'AlbumContent.Add.' + id;
                    break;
                default:
            }
            return false;
        }, false);
        
        dropZone[i].addEventListener('dragenter', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            return false;
        }, false);
        
        dropZone[i].addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            return false;
        }, false);
        
        dropZone[i].addEventListener('dragleave', function(e) {
            e.preventDefault(); 
            return false;
        }, false); 
    }

}