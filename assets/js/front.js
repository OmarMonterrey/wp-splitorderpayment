document.addEventListener('submit', function( e ){
    let T = e.target;
    if( T.getAttribute('id') != 'omsplitorderpayment-invite' && T.getAttribute('id') != 'omsplitorderpayment-pay' ) return;
    e.preventDefault();
    T.classList.add('loading');
    FD = new FormData( T );
    if( T.getAttribute('id') == 'omsplitorderpayment-pay')
        FD.append('action', omsplitorderpayment.ajax_action_pay);
    if( T.getAttribute('id') == 'omsplitorderpayment-invite' )
        FD.append('action', omsplitorderpayment.ajax_action_invite);
    FD.append('nonce', omsplitorderpayment.ajax_nonce);
    fetch(omsplitorderpayment.ajax_url, {body: FD, method: 'POST'}).then(function(response){
        if( response.status !== 200 ){
            formError('Unknown error, please try again.', T);
            return false;
        } else {
            return response.json();
        }
    }).then(function(data){
        if(data.error)
            formError(data.error, T);
        if(data.success)
            window.location.assign( window.location.href );
        if(data.goto)
            window.location.assign( data.goto );
    }).catch(e => formError('Unknown error, please try again.', T)).finally(() => T.classList.remove('loading'));
});
document.addEventListener('click', function( e ){
    let T = e.target;
    if( T.getAttribute('id') == 'add-invite' ){
        let container = document.querySelector('#invite-list');
        if( !container ) return;
        let single = document.createElement('div');
        single.classList.add('single-invite');
        container.appendChild( single );
        single.appendChild( inviteField('email', 'email') );
        single.appendChild( inviteField('name', 'text') );

        let remove = document.createElement('div');
        remove.classList.add('remove-invite');
        remove.innerHTML = "&times;";
        single.appendChild(remove);
    }
    if( T.classList.contains("remove-invite") ){
        let single = T.closest('.single-invite');
        if( single )
            single.remove();    
    }
});
function inviteField(name, type){
    let field = document.createElement('div');
    field.classList.add('input-field');

    let ind = parseInt( [...document.querySelectorAll('[name="name[]"]')].pop().getAttribute('id').substring(5) ) + 1;

    let label = document.createElement('label');
    label.setAttribute("for", `${name}_${ind}`);
    label.innerHTML = omsplitorderpayment[`${name}_label`];
    field.appendChild(label);

    let input = document.createElement('input');
    input.id = `${name}_${ind}`;
    input.name = `${name}[]`;
    input.type = type;
    if( input.type == 'email' )
        input.required = 'required';
    field.appendChild(input);

    return field;
}
function formError(str, form){
    let error = document.createElement('div');
    error.classList.add('form-error');
    error.innerHTML = str;
    form.insertBefore( error, form.children[0] );
    setTimeout(() => error.remove(), 1500)
}