
// здесь же можно налозить маску на поля
var mask = '7 (999) 999-99-99';

// следующая инструкция позволяет использоваться масску из настроек на решениях от Аспро
if (!!window.arAsproOptions && !!arAsproOptions['THEME'] && !!arAsproOptions['THEME']['PHONE_MASK']) {
    mask = arAsproOptions['THEME']['PHONE_MASK'];
}

var maskParams = {
    mask: mask,
    showMaskOnHover: false,
};

$('input[name=phone]').inputmask('mask', maskParams);
$('input[name=register_phone]').inputmask('mask', maskParams);
$('input[name=forget_phone]').inputmask('mask', maskParams);


// удаление маски

$('input[name=phone]').inputmask('unmaskedvalue');
$('input[name=register_phone]').inputmask('unmaskedvalue');
$('input[name=forget_phone]').inputmask('unmaskedvalue');