$(document).on('click','[data-fs-element="clone-att"]',function (event) {
    event.preventDefault();
    console.log(event);
var node=$(this).prev().clone();
$(this).before(node);
});