let fcavForm;

$(document).ready(function () {
    fcavForm = $(document.getElementById('fcBillingHidden').form);
    fcavForm.on('submit', function(ev) {
        if (fcAddressesAreValid() !== true) {
            ev.preventDefault();
        }
    });
});

//versteckte forms müssen noch gezeigt werden
function fcAddressesAreValid() {
    let billingValid = fcAddressValidation('#invadr_oxuser__oxcity', '#invadr_oxuser__oxzip', '#invCountrySelect', );
    let shippingValid = billingValid;

    if (fcIsShippingAddressVisible() === false) {
        shippingValid = fcAddressValidation('#deladr_oxaddress__oxcity', '#deladr_oxaddress__oxcity', '#delCountrySelect', );
    }

    return billingValid === true && shippingValid === true;
}

async function fcAddressValidation(citySelector, zipSelector, countrySelector) {
    let city = $(citySelector);
    let zip = $(zipSelector);
    let country = $(countrySelector);

    let response = await fcValidateAddress(city.val(),zip.val(),country.val);

    if (response.status === 'valid') {
        return true;
    }

    fcShowErrorMessage(response, zip, city, country)
    return false;
}

function fcShowErrorMessage(response, zip, city, country) {
    if (response.status === 'country found') {
        fcShowCountryError(country, response.country);
    } else if (response.status === 'city found') {
        fcShowCityError(zip, city, response.city)
    } else {
        fcShowCityError(zip, city, false)
    }
}

function fcIsShippingAddressVisible() {
    return $('#shippingAddress').css('diplay') !== 'none'
}

function fcShowCityError(zip,city, responseCity) {
    let msg = fcavErrorMsgNoZip;

    if (responseCity !== false) {
        msg += fcavErrorMsgZipHint.replace(/INSERTZIP/, zip.val());
        msg = msg.replace(/INSERTCITY/, responseCity);
    }

    let errorDiv = fcGetErrorElement(msg);
    zip.addClass('fcavInvalid');
    city.addClass('fcavInvalid');
    city.after(errorDiv);

    fcavForm.on('submit', function () {
        errorDiv.remove();
        city.removeClass('fcavInvalid');
        zip.removeClass('fcavInvalid');
    });
}

function fcShowCountryError(country, countryId) {
    let countryTitle;
    country.children().each(function () {
        let option = $(this);
        if (option.val() === countryId) {
            countryTitle = option.html();
            option.prop('selected', true)
        }
    });

    let msg = fcavErrorMsgCountry.replace(/INSERTCOUNTRY/g, countryTitle);

    let errorDiv = fcGetErrorElement(msg);
    country.addClass('fcavInvalid');
    country.after(errorDiv);

    fcavForm.on('submit', function () {
        errorDiv.remove();
        country.removeClass('fcavInvalid');
    });
}

function fcGetErrorElement(msg) {
    let errorDiv = $('<div></div>');
    errorDiv.addClass('fcavErrorBox');
    errorDiv.html(`<span>${msg}</span>`);

    return errorDiv;
}

function fcValidateAddress(city, zip, countryId) {
    return new Promise(function(resolve) {
        $.ajax({
            url: fcavBaseUrl,
            type: 'POST',
            data: {
                'cl': 'fcAddressAjax',
                'fnc': 'validateAddress',
                'countryId': countryId,
                'city': city,
                'zip': zip
            },
            success: function (response) {
                resolve(JSON.parse(response));
            }
        });
    });
}