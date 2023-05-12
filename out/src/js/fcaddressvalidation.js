let form;
let errors = [];

$(document).ready(function () {
    form = $(document.getElementById('fcBillingHidden').form);
    form.on('submit', function(ev) {
        if (fcAddressesAreValid() !== true) {
            ev.preventDefault();
        }
    });
});

function fcAddressesAreValid() {
    let billingValid = fcAddressValidation('#invadr_oxuser__oxcity', '#invadr_oxuser__oxzip', '#invCountrySelect');
    let shippingValid = billingValid;

    if (fcIsShippingAddressVisible() === true) {
        shippingValid = fcAddressValidation('#deladr_oxaddress__oxcity', '#deladr_oxaddress__oxcity', '#delCountrySelect');
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
    return $('#shippingAddress').css('diplay') === 'none'
}

function fcShowCityError(zip,city, responseCity) {
    let msg = `Wir konnten die PLZ für diesen Ort nicht finden. Bitte überprüfen sie ihre Eingabe.`;

    if (responseCity !== false) {
        msg += `<br>Hinweis: für die PLZ "${zip.val()}" konnten wir den Ort "${responseCity}" finden.`;
    }

    let errorDiv = $('<div></div>');
    errorDiv.addClass('fcavErrorBox');
    errorDiv.html(`<span>${msg}</span>`);

    zip.addClass('fcavInvalid');
    city.addClass('fcavInvalid');
    city.after(errorDiv);

    form.on('submit', function () {
       errorDiv.remove();
        city.removeClass('fcavInvalid');
        zip.removeClass('fcavInvalid');
    });
}

function fcShowCountryError(country, countryTitle) {
    let msg = `Die eingegebene Addresse scheint aus diesem Land zu stammen "${countryTitle}" .`;

    country.children().each(function () {
        let option = $(this);
        if (option.html() === countryTitle) {
            option.prop('selected', true)
        }
    });

    let errorDiv = $('<div></div>');
    errorDiv.addClass('fcavErrorBox');
    errorDiv.html(`<span>${msg}</span>`);

    country.addClass('fcavInvalid');
    country.after(errorDiv);

    form.on('submit', function () {
        errorDiv.remove();
        country.removeClass('fcavInvalid');
    });
}

function fcValidateAddress(city, zip, countryId) {
    return new Promise(function(resolve) {
        $.ajax({
            url: fcBaseUrl,
            async: true,
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