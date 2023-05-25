let fcAddressForm;

$(document).ready(function () {
    fcAddressForm = $(document.getElementById('fcBillingHidden').form);
    fcAddressForm.on('submit', async function(ev) {
        ev.preventDefault();
        if ( await fcAddressesAreValid() === true) {
            fcAddressForm.unbind('submit').submit();
        }
    });
});

/**
 * Validates billing address and shipping address
 * Returns true if both are valid
 *
 * @returns {Promise<boolean>}
 */
async function fcAddressesAreValid() {
    let blBillingValid = await fcAddressValidation('#invadr_oxuser__oxcity', '#invadr_oxuser__oxzip', '#invCountrySelect');
    let blShippingValid = blBillingValid;

    if (fcIsShippingAddressVisible() === true) {
        blShippingValid = await fcAddressValidation('#deladr_oxaddress__oxcity', '#deladr_oxaddress__oxzip', '#delCountrySelect');
    }

    return blBillingValid === true && blShippingValid === true;
}

/**
 * Uses given selectors to validate form data via ajax
 * and displays error message if not valid
 *
 * @param sCitySelector {string}
 * @param sZipSelector {string}
 * @param sCountrySelector {string}
 * @returns {Promise<boolean>}
 */
async function fcAddressValidation(sCitySelector, sZipSelector, sCountrySelector) {
    let oCity = $(sCitySelector);
    let oZip = $(sZipSelector);
    let oCountry = $(sCountrySelector);

    let jResponse = await fcValidateAddress(oCity.val(),oZip.val(),oCountry.val());

    if (jResponse.status === 'valid') {
        return true;
    }

    fcShowErrorMessage(jResponse, oZip, oCity, oCountry)
    return false;
}


/**
 * Expands the form containing the passed input fields if its hidden
 * Uses response json to determine the type of error and display it
 *
 * @param jResponse {json}
 * @param oZip {jQuery|HTMLElement}
 * @param oCity {jQuery|HTMLElement}
 * @param oCountry {jQuery|HTMLElement}
 */
function fcShowErrorMessage(jResponse, oZip, oCity, oCountry) {
    let oBillingFrom = $('#addressForm');
    let oShippingForm =$('#shippingAddressForm');

    if (oBillingFrom.has(oZip).length !== 0) {
        oBillingFrom.show();
    } else if (oShippingForm.has(oZip).length !== 0) {
        oShippingForm.show();
    }

    if (jResponse.status === 'country found') {
        fcShowCountryError(oCountry, jResponse.country);
    } else if (jResponse.status === 'city found') {
        fcShowCityError(oZip, oCity, jResponse.city)
    } else {
        fcShowCityError(oZip, oCity, false)
    }
}

/**
 * Returns true if billing address is not used as shipping address
 *
 * @returns {boolean}
 */
function fcIsShippingAddressVisible() {
    return $('#shippingAddress').css('display') !== 'none'
}

/**
 * Displays zip/city error, appends hint if the zip was found in db
 *
 * @param oZip {jQuery|HTMLElement}
 * @param oCity {jQuery|HTMLElement}
 * @param sResponseCity {string}
 */
function fcShowCityError(oZip, oCity, sResponseCity) {
    let sMsg = fcErrorMsgNoZip;

    if (sResponseCity !== false) {
        sMsg += fcErrorMsgZipHint.replace(/INSERTZIP/, oZip.val());
        sMsg = sMsg.replace(/INSERTCITY/, sResponseCity);
    }

    let oErrorDiv = fcGetErrorElement(sMsg);
    let oErrorContainer = $('<div class="col-lg-3"></div><div class="col-lg-9"></div>');
    oErrorContainer.last().append(oErrorDiv);

    oZip.addClass('fcInvalid');
    oCity.addClass('fcInvalid');
    oCity.parent().after(oErrorContainer);

    fcAddressForm.on('submit', function () {
        oErrorDiv.remove();
        oCity.removeClass('fcInvalid');
        oZip.removeClass('fcInvalid');
    });
}

/**
 * Displays country error and sets country dropdown to country found in db
 *
 * @param oCountry {jQuery|HTMLElement}
 * @param sCountryId {string}
 */
function fcShowCountryError(oCountry, sCountryId) {
    let sCountryTitle;
    oCountry.children().each(function () {
        let oOption = $(this);
        if (oOption.val() === sCountryId) {
            sCountryTitle = oOption.html();
            oOption.prop('selected', true)
        }
    });

    let sMsg = fcErrorMsgCountry.replace(/INSERTCOUNTRY/g, sCountryTitle);

    let oErrorDiv = fcGetErrorElement(sMsg);
    oCountry.addClass('fcInvalid');
    oCountry.after(oErrorDiv);

    fcAddressForm.on('submit', function () {
        oErrorDiv.remove();
        oCountry.removeClass('fcInvalid');
    });
}

/**
 * Builds and returns error message as Jquery element
 *
 * @param sMsg {string}
 * @returns {*|jQuery|HTMLElement}
 */
function fcGetErrorElement(sMsg) {
    let errorDiv = $('<div class="fcErrorBox rounded"></div>');
    errorDiv.html(`<span>${sMsg}</span>`);

    return errorDiv;
}

/**
 * Validates address using ajax returns promise of response
 *
 * @param sCity {string}
 * @param sZip {string}
 * @param sCountryId {string}
 * @returns {Promise<unknown>}
 */
function fcValidateAddress(sCity, sZip, sCountryId) {
    return new Promise(function(resolve) {
        $.ajax({
            url: fcBaseUrl,
            type: 'POST',
            data: {
                'cl': 'fcAddressAjax',
                'fnc': 'fcValidateAddress',
                'countryId': sCountryId,
                'city': sCity,
                'zip': sZip
            },
            success: function (response) {
                resolve(JSON.parse(response));
            }
        });
    });
}