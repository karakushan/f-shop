CodeMirror.fromTextArea(document.getElementById('fs-mail-template'), {
    mode: 'xml',
    lineNumbers: true,
    lineWrapping: true ,
    matchTags: {bothTags: true},
    extraKeys: {"Ctrl-J": "toMatchingTag"}
})