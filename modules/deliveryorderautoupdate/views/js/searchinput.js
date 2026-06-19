/**
* 2007-2023 Helloshop
*
* Tracking Center
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

class SearchInput {
    constructor(e, onresult) {
        this.e = e;
        this.onresult = onresult;
        this.input = this.e.find('input');
        this.remove = this.e.find('.remove');
        this.wrap = this.e.find('.control-wrap');
        this.text = this.e.find('.text');
        this.tag = this.e.find('.tag');
        this.init();
    }
    getValue() {
        return this.input.attr('id-product');
    }
    init() {
        this.input.autocomplete(url_ajax, {
            extraParams: {
                ajax: 1,
                action: 'searchOrder',
                secure_key: secure_key
            },
            formatItem: function(value) { return value; },
            parse: function(data) {
                data = JSON.parse(data);
                let parsed = data.map(d => {
                    return {
                        data: `${d.id_order} - ${d.reference} - ${d.customer} - ${d.tracking_number}`,
                        value: d.id_order,
                        result: `${d.id_order} - ${d.reference} - ${d.customer} - ${d.tracking_number}`,
                    };
                });
                return parsed;
            }
        });

        this.input.on('result', (e, name, id) => {
            this.onresult();
        })
        this.remove.click(e => {
            e.preventDefault();
            this.input.val('').attr('id-product', null);
            console.log(this.input.attr('id-product'));
            this.onresult();
        })
        this.e.on('click', '.control-wrap', e => {
            if (!this.remove.is($(e.target)) && this.text.is(":visible")) {
                this.input.val(this.text.html());
            }
            this.input.show().focus();
            this.tag.hide();
        })
        $(document).on('mousedown', e => {
            let ac_result = $('.ac_results');
            let control_wrap = this.wrap;
            if (!ac_result.has($(e.target)).length && !ac_result.is($(e.target)) && this.input.attr('id-product') > 0 && !control_wrap.has($(e.target)).length && !control_wrap.is($(e.target))) {
                this.input.hide();
                this.tag.show();
            }
        });
    }
}