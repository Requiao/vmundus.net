$(document).ready(function() {
    class DropDownList extends HTMLElement {
        constructor() {
            super();
        }
        connectedCallback () {
            //get children
            let children = this.innerHTML;
            this.innerHTML = '';

            //get/set title
            let title = '';
            if(this.hasAttribute('title')) {
                title = this.getAttribute('title');
            }
            
            let selected = document.createElement('selected');
            let title_p = document.createElement('p');
            title_p.textContent = title;
            title_p.style.width = '100%';
            title_p.style.textAlign = 'center';
            selected.appendChild(title_p);
            selected.style.float = 'left';
            selected.style.width = '100%';
            this.appendChild(selected);
            this.style.cursor = 'pointer';
            this.style.position = 'relative';
            this.style.overflow = 'visible';
            this.style.float = 'left';
            this.style.border = '1px solid black';

            let this_css = getComputedStyle(this);
            let drop_down_height = this_css.getPropertyValue('height').replace(/(?!\.)\D/gm, '');
            let drop_down_padding_top = this_css.getPropertyValue('padding-top').replace(/(?!\.)\D/gm, '');
            let drop_down_padding_bottom = this_css.getPropertyValue('padding-bottom').replace(/(?!\.)\D/gm, '');
            
            let from_top = drop_down_height ? parseInt(drop_down_height) : 0;
            from_top += drop_down_padding_top ? parseInt(drop_down_padding_top) : 0;
            from_top += drop_down_padding_bottom ? parseInt(drop_down_padding_bottom) : 0;
            from_top = String(from_top) + 'px';

            let drop_down_width = this_css.getPropertyValue('width');
            let drop_down_max_height = this_css.getPropertyValue('max-height');

            
            //set styles
            //let title_style = getComputedStyle(this);
            /*Array.from(title_style).forEach(key => 
                title_p.style.setProperty(
                    key, title_style.getPropertyValue(key), 
                    title_style.getPropertyPriority(key)
                )
            );*/
            
            //create list
            let children_div = document.createElement('div');
            children_div.innerHTML = children;
            this.appendChild(children_div);
            let children_css = 
                'display: none;' +
                'clear: both;' +
                'position: absolute;' +
                'top: ' + from_top + ';' +
                'left: -1px;' +
                'background-color: white;' +
                'width: ' + drop_down_width + ';' +
                'border: 1px solid black;' +
                'max-height: ' + drop_down_max_height + ';' +
                'overflow-y: scroll;'
            ;
            children_div.setAttribute('style', children_css);

            selected.addEventListener('click', () => {
                $(children_div).slideToggle();
            });

            this.addEventListener('mouseleave', () => {
                $(children_div).slideUp();
            });

            $(children_div).on('click', 'item', function() {
                let selected_element = $(this).clone();
                $(selected_element).removeAttr('class');
                $(selected_element).removeAttr('id');
                $(selected_element).removeAttr('style');
                $(selected).html(selected_element);
                $(children_div).slideUp();
            });
        }
    }
    customElements.define('drop-down-list', DropDownList);
});