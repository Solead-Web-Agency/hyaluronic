/**
 * HS Combine guest
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Object js combine guests.
 * Using
 * new HsCombineGuests().handleEvent();
 */
var HsCombineGuests = function (options)
{
    this.combineGuestUrl = typeof options.combineGuestUrl !== 'undefined' ? options.combineGuestUrl : null;
    /**
     * current customer id
     */
    this.idCustomer = typeof options.idCustomer !== 'undefined' ? options.idCustomer : null;

    /* all settings (selector)*/
    this._selectors = {
        combineGuest: '.combine_guest', /* class append content customer after return value */
        txtSearchCutomer: '.search_customer', /* class get value which to search custmer */
        submitCombineGuest: '#submitCombineGuest', /* class get value which to search custmer */
        inputCheckbox: '#hs_combine_guests input[type=checkbox]' /* class get value which to search custmer */
    };
    HsCombineGuests.instance = this;
    /**
     * Bind all handler events
     */
    this.handleEvent = function ()
    {
        $(document).on('click', HsCombineGuests.instance._selectors.inputCheckbox, function ()
        {
            var isChecked = HsCombineGuests.instance.isCheckedCheckbox(HsCombineGuests.instance._selectors.inputCheckbox);
            if (isChecked) {
                $(HsCombineGuests.instance._selectors.submitCombineGuest).attr('disabled', false);
            } else {
                $(HsCombineGuests.instance._selectors.submitCombineGuest).attr('disabled', true);
            }
        });

        $(this._selectors.txtSearchCutomer).typeWatch({
            captureLength: 2,
            highlight: false,
            wait: 200,
            callback: function () {
                HsCombineGuests.instance.searchCustomers($(HsCombineGuests.instance._selectors.txtSearchCutomer).val().trim());
            }
        });

    };

    /**
     * auto search guest customers
     * @param {int} txtSearchCutomer
     */
    this.searchCustomers = function (txtSearchCutomer)
    {
        if (HsCombineGuests.instance.idCustomer === null && HsCombineGuests.instance.combineGuestUrl === null) {
            return;
        }
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: HsCombineGuests.instance.combineGuestUrl.url.searchCustomers,
            dataType: 'json',
            data: {
                customer_search: txtSearchCutomer,
                current_customer_id: HsCombineGuests.instance.idCustomer
            },
            success: function (json) {
                if (json.success) {
                    $(HsCombineGuests.instance._selectors.combineGuest).html(json.data);
                } else {
                    alert('Errors');
                }
            },
            error: function () {
                alert('Errors');
            }
        });
    };
    this.isCheckedCheckbox = function (elements)
    {
        var isChecked = [];
        $(elements + ':checked').each(function (i) {
            isChecked[i] = $(this).val();
        });
        if ($.isEmptyObject(isChecked)) {
            return false;
        }
        return true;
    };
};
