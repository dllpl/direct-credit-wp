if (!dcData?.error) {
    jQuery(function ($) {
        $('body').append("" +
            "<link rel='stylesheet' href='//dcapi.direct-credit.ru/style.css' type='text/css'/> " +
            "<script src='//dcapi.direct-credit.ru/JsHttpRequest.js' type='text/javascript'></script> " +
            "<script src='//dcapi.direct-credit.ru/dc.js' charset='utf-8' type='text/javascript'></script>")

        const partnerID = dcData.partnerID
        const createOrderUri = dcData.createOrderUri
        const debug = false;

        /** ID кнопки, запускающей модальное окно первого шага кредита (наше окно) */
        var click_on_credit_id = '#click_on_credit'

        /** ID формы перового шага кредита */
        var credit_form_id = '#credit_form'

        /** ID карточки товара */
        var card_product = '#card_product'

        /** Объект формы */
        var formProps

        var phone_raw = null

        /** Телефон из формы */
        var phone
        /** Цена товара */
        var price = null
        /** Название товара */
        var name_product = null

        jQuery(function ($) {
            $(function () {
                $(document).on('mouseup touchend', click_on_credit_id, function (e) {
                    price = $(this).closest(card_product).find('.sale-price .woocommerce-Price-amount')[0].innerText.replace(/\D/g, '')
                    name_product = $(this).closest(card_product).find('.elementor-heading-title')[0].innerText
                });
            });

            jQuery(document).on('submit_success', credit_form_id, (e) => {

                $('body').prepend('<div class="preloader"><div class="preloader__row"><div class="preloader__item"></div><div class="preloader__item"></div></div></div>')

                let formData = new FormData(e.target)
                formProps = Object.fromEntries(formData);

                console.log(formProps, name_product)

                phone_raw = formProps["form_fields[Tel]"]
                price = price ?? $('#price')[0].innerText.replace(/\D/g, '')
                phone = formProps["form_fields[Tel]"].replace(/[^\d.]/g, "").replace(/^7|8/, "");
                name_product = name_product ?? formProps["form_fields[post_title_my]"]

                let regex = /^9[0-9]{9}$/;

                if (!regex.test(phone)) {
                    alert('Введите корректный номер телефона и повторите попытку')
                    return false
                }

                document.body.classList.add('loaded_hiding');

                $.ajax({
                    url: createOrderUri,
                    method: 'POST',
                    data: {
                        phone: phone_raw,
                        firstName: formProps["form_fields[name]"],
                        lastName: formProps["form_fields[familiya]"],
                        secondName: formProps["form_fields[ot4estvo]"],
                        email: formProps["form_fields[mail]"],
                        item_name: name_product,
                        birthdate: formProps["form_fields[date_rojdenija]"],
                        address: formProps["form_fields[City]"],
                        price: price,
                        metrikaclientid: yaCounter23555653.getClientID(),
                        url: formProps["form_fields[url_my]"]
                    }
                }).success(function (response) {
                    if (response.success) {

                        window.setTimeout(function () {
                            document.body.classList.add('loaded');
                            document.body.classList.remove('loaded_hiding');
                        }, 500);

                        DCLoans(partnerID, 'orderByToken', {token: response.data.token}, function (result) {
                        });
                    } else {
                        console.log('error');
                    }
                });
            })
        })
    })
} else {
    console.log(dcData.error)
}

function DCCheckStatus(result) {
    document.location.href = dcData?.finish_redirect_url ?? '/'
}



