class ModalBox {
    constructor(modal_width = '550px', modal_height = '300px') {
        this.modal_id = Date.now();
        this.modal;
        this.modal_body;
        this.heading;
        this.modal_interior;
        this.buttons_box;
        this.cancel_button = null;
        this.submit_button = null;
        this.submit_button_action;
        this.submit_button_action_params = [];
        this.submit_button_action_text;
        this.error_msg;
        this.error_modal = null;
        this.success_modal = null;

        /* MODAL */
        this.modal = document.createElement('div');
        this.modal.setAttribute('style', 
            'position: fixed;' +
            'left: 0;' +
            'top: 0;' +
            'width: 100%;' +
            'height: 100%;' +
            'display: none;' +
            'background-color: rgba(86,116,127,0.5);' +
            'z-index: 1;' 
        );
        this.modal.setAttribute('id', this.modal_id);
        this.modal.onclick = (event) => {
            if(event.target !== event.currentTarget) {
                return;
            }
            this.removeModal();
            
            //event.stopPropagation();
        }

        /* MODAL BODY */
        this.modal_body = document.createElement('div');
        this.modal_body.setAttribute('style', 
            'width: ' + modal_width + ';' +
            'margin-left: auto;' +
            'margin-right: auto;' +
            'margin-top: 200px;' +
            'background-color: white;' +
            'box-shadow: 0px 0px 3px 0px #345879;' +
            'border-radius: 3px;' +
            'font-family: Enriqueta;' +
            'font-size: 20px;' +
            'position: relative;' +
            'overflow: hidden;'
        );
        $(this.modal).append(this.modal_body);

        /* HEADING */
        this.heading = document.createElement('p');
        this.heading.setAttribute('style', 
            'float: left;' +
            'width: 100%;' +
            'background-color: #36728e;' +
            'color: white;' +
            'font-size: 25px;' +
            'text-align: center;'
        );
        $(this.modal_body).append(this.heading);

        /* MODAL INTERIOR */
        this.modal_interior = document.createElement('div');
        this.modal_interior.setAttribute('style', 
            'max-height: ' + modal_height + ';' +
            'float: left;' +
            'background-color: #fff;' +
            'overflow-y: auto;' +
            'width: 100%;'
        );
        $(this.modal_body).append(this.modal_interior);

        /* ERROR MSG */
        this.error_msg = document.createElement('p');
        this.error_msg.setAttribute('style', 
            'float: left;' +
            'color: rgb(185, 72, 64);' +
            'width: 100%;' +
            'text-align: center;' +
            'font-size: 20px;' +
            'line-height: 100%;' +
            'min-height: 20px;' +
            'margin-bottom: 2px;'
        );
        $(this.modal_body).append(this.error_msg);

        /* BUTTONS BOX */
        this.buttons_box = document.createElement('div');
        this.buttons_box.setAttribute('style', 
            'float: left;' +
            'width: 100%;' +
            'background-color: #316279;' +
            'color: white;' +
            'font-size: 25px;' +
            'text-align: center;'
        );
        $(this.modal_body).append(this.buttons_box);

        $('body').append(this.modal);
    }

    removeModal() {
        $(this.modal).fadeOut(150, () => {this.modal.remove()});
    }

    closeModal() {
        $(this.modal).fadeOut(150);
    }

    displayModal() {
        $(this.modal).fadeIn(150);
        this.centerModal();
        this.centerButtons();
    }

    appendToModal(element) {
        $(this.modal_interior).append(element);
    }

    centerModal() {
        let from_top = (window.innerHeight - this.modal_body.offsetHeight) / 3;
        this.modal_body.style.marginTop = from_top + 'px';

        //center error icon
        if(this.error_modal) {
            let modal_interior_height = this.modal_interior.offsetHeight;
            let icon_height = this.error_modal.getElementsByTagName('span')[0].style.height;
            let margin_top = (modal_interior_height - icon_height) / 2;
            this.error_modal.getElementsByTagName('span')[0].style.marginTop = margin_top + 'px';
            this.error_modal.getElementsByTagName('span')[0].style.marginBottom = margin_top + 'px';
        }
        if(this.success_modal) {
            let modal_interior_height = this.modal_interior.offsetHeight;
            let icon_height = this.success_modal.getElementsByTagName('span')[0].style.height;
            let margin_top = (modal_interior_height - icon_height) / 2;
            this.success_modal.getElementsByTagName('span')[0].style.marginTop = margin_top + 'px';
            this.success_modal.getElementsByTagName('span')[0].style.marginBottom = margin_top + 'px';
        }
    }

    centerButtons() {
        if(this.submit_button !== null && this.cancel_button !== null) {
            this.cancel_button.style.marginLeft = 
                (
                    this.modal_body.offsetWidth 
                    - this.cancel_button.offsetWidth
                    - this.submit_button.offsetWidth
                    - 100 /* 100 is the distance between buttons */
                ) / 2 + 'px';
            this.submit_button.style.marginLeft = 100 /* 100 is the distance between buttons */ + 'px';
        }
        else if (this.cancel_button !== null) {
            this.cancel_button.style.marginLeft = 
                (
                    this.modal_body.offsetWidth - this.cancel_button.offsetWidth
                ) / 2 + 'px';
        }
    }

    setHeading(text) {
        this.heading.innerText = text;
    }

    appendCancelButton(text) {
        this.cancel_button = document.createElement('p');
        this.cancel_button.innerText = text;
        this.cancel_button.setAttribute('style',
            'margin-top: 7px;' +
            'margin-bottom: 7px;' +
            'float: left;' +
            'clear: left;' +
            'border: 1px solid #ffffff;' +
            'width: 100px;' +
            'padding: 10px 5px 10px 5px;' +
            'font-family: Enriqueta;' +
            'font-size: 20px;' +
            'text-align: center;' +
            'color: white;' +
            'line-height: 100%;' +
            'cursor: pointer;' +
            'border-radius: 4px;' 
        );

        this.cancel_button.onclick = () => {
            this.removeModal();
            //event.stopPropagation();
        }

        if(this.submit_button !== null) {
            this.submit_button.remove();
            $(this.buttons_box).append(this.cancel_button);
            $(this.buttons_box).append(this.submit_button);

            this.error_modal = null;
            this.success_modal = null;
        }
        else {
            $(this.buttons_box).append(this.cancel_button);
        }
    }

    appendSubmitButton(text) {
        this.submit_button_action_text = text;

        this.submit_button = document.createElement('p');
        this.submit_button.innerText = text;
        this.submit_button.setAttribute('style',
            'margin-top: 7px;' +
            'margin-bottom: 7px;' +
            'float: left;' +
            'background-color: #1c3846;' +
            'border: 1px solid #d4d4d4;' +
            'width: 110px;' +
            'height: 30px;' +
            'padding: 5px 0px 5px 0px;' +
            'font-family: Enriqueta;' +
            'font-size: 20px;' +
            'text-align: center;' +
            'color: white;' +
            'line-height: 30px;' +
            'cursor: pointer;' +
            'border-radius: 4px;' 
        );

        this.submit_button.onclick = () => {
            this.error_msg.innerText = '';
            this.submit_button_action(...this.submit_button_action_params);
        }

        /*this.submit_button.children[0].onclick = () => {
            this.removeModal();
            //event.stopPropagation();
        }*/

        $(this.buttons_box).append(this.submit_button);
    }

    setLoadingSubmitBtn(display) {
        if(this.submit_button == null) {
            return;
        }

        let spinner_timer;
        if(display) {
            let spinner = document.createElement('span');
            spinner.setAttribute('class', 'fa fa-spinner');
            spinner.setAttribute('style', 
                'margin-left: 40px;' +
                'font-size: 30px;'
            );
            this.submit_button.innerText = '';
            $(this.submit_button).append(spinner);

            //animate load
            let degree = 0;
            function rotateLoad() {
                if(degree >= 360) {
                    degree = 0;
                }
                $(spinner).css('-webkit-transform', 'rotate(' + degree + 'deg)',
                                '-moz-transform', 'rotate(' + degree + 'deg)',
                                '-ms-transform', 'rotate(' + degree + 'deg)',
                                '-o-transform', 'rotate(' + degree + 'deg)',
                                'transform', 'rotate(' + degree + 'deg)',
                                'marginLeft', '40px',
                                'fontSize', '30px'
                                );
                degree += 3;
                
                spinner_timer = setTimeout(rotateLoad, 15);
            };
            rotateLoad()
        }
        else {
            this.submit_button.innerHtml = '';
            this.submit_button.innerText = this.submit_button_action_text;
            clearTimeout(spinner_timer);
        }
    }

    setSubmitButtonAction(submit_button_action, params = []) {
        this.submit_button_action = submit_button_action;
        this.submit_button_action_params = params;
    }

    setErrorMsg(msg) {
        this.error_msg.innerText = msg;
    }

    removeErrorMsgTag() {
        this.error_msg.remove();
    }

    setErrorModal(msg) {
        this.cleanModal();
        this.error_msg.remove();
        this.setHeading('Error');
        this.appendCancelButton('Ok');
        this.modal_body.style.width = '500px';

        this.error_modal = document.createElement('div');
        this.error_modal.setAttribute('style', 
            'float: left;' +
            'width: 100%;' +
            'overflow: hidden;'
        );
        this.appendToModal(this.error_modal);

        //icon
        let error_icon = document.createElement('span');
        error_icon.setAttribute('class', 'fa fa-exclamation-triangle');
        error_icon.setAttribute('style', 
            'font-size: 35px;' +
            'margin-left: 25px;' +
            'margin-right: 25px;' +
            'color: rgb(185, 72, 64);'
        );
        $(this.error_modal).append(error_icon);

        //msg
        let msg_element = document.createElement('p');
        msg_element.setAttribute('style', 
            'font-size: 25px;' +
            'margin-top: 25px;' +
            'margin-right: 25px;'
        );
        msg_element.innerText = msg;
        $(this.error_modal).append(msg_element);

        this.displayModal();
    }

    setSuccessModal(msg) {
        this.cleanModal();
        this.error_msg.remove();
        this.setHeading('Success');
        this.appendCancelButton('Ok');
        this.modal_body.style.width = '500px';

        this.success_modal = document.createElement('div');
        this.success_modal.setAttribute('style', 
            'float: left;' +
            'width: 100%;' +
            'overflow: hidden;'
        );
        this.appendToModal(this.success_modal);

        //icon
        let success_icon = document.createElement('span');
        success_icon.setAttribute('class', 'glyphicon glyphicon-ok');
        success_icon.setAttribute('style', 
            'font-size: 35px;' +
            'margin-left: 25px;' +
            'margin-right: 25px;' +
            'color: rgb(76, 175, 80);'
        );
        $(this.success_modal).append(success_icon);

        //msg
        let msg_element = document.createElement('p');
        msg_element.setAttribute('style', 
            'font-size: 25px;' +
            'margin-top: 25px;' +
            'margin-right: 25px;'
        );
        msg_element.innerText = msg;
        $(this.success_modal).append(msg_element);

        this.displayModal();
    }

    cleanModal() {
        while (this.buttons_box.firstChild) {
            this.buttons_box.removeChild(this.buttons_box.firstChild);
        }
        this.submit_button = null;
        this.cancel_button = null;
        while (this.modal_interior.firstChild) {
            this.modal_interior.removeChild(this.modal_interior.firstChild);
        }
    }
}