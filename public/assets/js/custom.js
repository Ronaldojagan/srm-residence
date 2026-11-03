$(document).ready(function() {
    $(document).on('click', '.open-image-model', function(event) {
        imageModel("Image View",$(this).data('url'));
    });
    $(document).on('click', '.open-pdf-model', function(event) {
        pdfModel("Pdf View",$(this).data('url'),'modal-xl');
    });
    $(document).on('click', '.open-video-model', function(event) {
        console.log($(this).data('url'));
        videoModel("Video View",$(this).data('url'));
    });
    $(document).on('click', '.button-menu-mobile', function(event) {
        $('body').attr('data-leftbar-size','default');
        $('.left-side-menu').toggle();
    });

});
function modelOpen(title,content,className){
    className = className || 'modal-md';
    $('#image-view .modal-title').text(title);
    $('#image-view .modal-body').html(content);
    $('#image-view .modal-dialog').removeClass('modal-sm modal-xl modal-md modal-lg modal-xs');
    $('#image-view .modal-dialog').addClass(className);
    $('#image-view').modal('show');
}
function imageModel(title,url,className){
    content = '<img src="'+url+'" class="img-fluid"/>';
    modelOpen(title,content,className);
}
function pdfModel(title,url,className){
    content = '<object data="'+url+'" type="application/pdf" style="width:100%;height:70vh"/>';
    modelOpen(title,content,className);
}
function videoModel(title,url,className){
    content = '<iframe src="'+url+'" style="height:350px;width: 458px;"></iframe>';
    modelOpen(title,content,className);
}


