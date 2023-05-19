function undisabled() {
    const form = document.getElementById('frm');
    [...form.elements].forEach(e => {
        // console.log(e);

        $(e).prop('disabled', false);
    });

}

console.log('test3');
