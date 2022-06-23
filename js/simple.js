// события комопонентиа Simple

/*
// так вызывается событие
BX.Vue.event.$emit('BXmakerAuthuserphoneSimpleAjaxResponse', {
    request: {}, // данные в запросе
    result: {}, // полученный результат
});
*/


// добавление обработчик события, чтобы отследить произошедшую авторизацию /
// регистрацию и отправить событие достижения цели в яндекс метрику
//  заменить XXXX на номер счетчика


BX.Vue.event.$on('BXmakerAuthuserphoneSimpleAjaxResponse',(data) => {

    var counterId = 'XXX';
    var rand = data.request.rand;

    //получить доступ к объекту класса, через который можно управлять комопнентом
    // переменная определяется в шаблоне компонента
    var obj = window['BXmakerAuthuseremailSimple' +  rand];

    // доступ к корневому vue
    var instance = obj.instance;

    // обновить капчу омжно так
    // instance.refreshCaptcha();


    if(data.result.response && data.request.method === 'checkConfirmation')
    {
        // если есть response, значит все хорошо и удалось
        // проверить подтвреждение и авторизоваться, зарегистрироваться

        if(data.result.response.type  == 'AUTH')
        {
            console.log('AUTH');
            ym(counterId, "reachGoal", "authSuccess");
        }
        else if(data.result.response.type == 'REG')
        {
            console.log('REGISTER');
            ym(counterId, "reachGoal", "registerSuccess");
        }


    }
});
