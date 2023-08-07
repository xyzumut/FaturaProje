// faturalar sayfası script

window.addEventListener('load', () => {
    
    const create_order_paymende_btn = document.getElementById('create_order_paymende_form_submit_button')
    const create_order_paymende_form_amount = document.getElementById('create_order_paymende_form_amount')
    const create_order_paymende_form_notes = document.getElementById('create_order_paymende_form_notes')

    // POPUP Kodlar
    const toastNotif = (setting) => {
        let notifs = document.getElementById("toasts");
        let toast = document.createElement("div");
        toast.style.backgroundColor = setting.color;
        toast.classList.add('toast', 'toast-show');
        // icon = document.createElement("i");
        // icon.classList.add('fa-solid', configIcons[setting.icon]);
        let text = document.createElement("p");
        text.appendChild( document.createTextNode(setting.text) );
        // toast.appendChild(icon);
        toast.appendChild(text);
        console.log(toast)
        notifs.appendChild(toast);
    
        setTimeout(() => {
            toast.classList.remove('toast-show')
            toast.classList.add('toast-hide')
            setTimeout(() => {
                toast.remove()
            }, 300)
        }, setting.timeout);
    }
    // POPUP Kodlar
    
    if(create_order_paymende_btn !== null ){
        const toast_message = document.getElementById('toast_message').innerText;
        create_order_paymende_btn.addEventListener('click', (e) => {
            if (!(create_order_paymende_form_amount.value.length > 0)) {
                e.preventDefault()
                toastNotif({
                    text: toast_message,
                    color: '#000222',
                    timeout: 5000,
                });   
            }
        })
    }
    if(create_order_paymende_form_amount !== null) {
        create_order_paymende_form_amount.addEventListener('input', () => {
            if (create_order_paymende_form_amount.value < 0) {
                create_order_paymende_form_amount.value = 0
            }
        })
    }
})