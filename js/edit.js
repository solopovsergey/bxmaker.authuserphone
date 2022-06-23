// события комопонентиа Edit

/*
// так вызывается событие
BX.Vue.event.$emit('BXmakerAuthuserphoneEditAjaxResponse', {
    request: {}, // данные в запросе
    result: {}, // полученный результат
});
*/


// добавление обработчик события, чтобы отследить произошедшую авторизацию /
// регистрацию и отправить событие достижения цели в яндекс метрику
//  заменить XXXX на номер счетчика


BX.Vue.event.$on('BXmakerAuthuserphoneEditAjaxResponse',(data) => {

    var counterId = 'XXX';
    var rand = data.request.rand;

    //получить доступ к объекту класса, через который можно управлять комопнентом
    // переменная определяется в шаблоне комопнента
    var obj = window['BXmakerAuthuserphoneEdit' +  rand];

    // доступ к корневому vue
    var instance = obj.instance;

    // скрыть подтверждение можно так -
    // instance.hideConfirmation();
    // или показать
    // instance.showConfirmation();


    if(data.result.response && data.request.method === 'checkConfirmation')
    {
        // если есть response, значит все хорошо и в нем сообщение об успешности выполнения операции
        console.log('PHONE_IS_CHANGED');

        ym(counterId, "reachGoal", "PHONE_IS_CHANGED");

    }
});
