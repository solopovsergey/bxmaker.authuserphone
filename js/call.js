// события комопонентиа Call

/*
// так вызывается событие
$(document).trigger('bxmaker.authuserphone.ajax', {
    'params': {},  // парамтеры  инифиализации комопнента
    'request': {}, // даныне отправленные в зарпосе
    'result': {}, //  полученые ответ от сервера
});
*/


// добавление обработчик события, чтобы отследить произошедшую авторизацию /
// регистрацию и отправить событие достижения цели в яндекс метрику
//  заменить XXXX на номер счетчика

$(document).on("bxmaker.authuserphone.ajax", function(event, data){

    var counterId = 'XXX';

    if(data.result && data.result.response && data.request.method === "auth") {

        if(data.result.response.type == 'AUTH') {
            console.log('AUTH');
            ym(counterId, "reachGoal", "authSuccess");
        }
        else {
            console.log('REGISTER');
            ym(counterId, "reachGoal", "registerSuccess");
        }
    }

    if(data.result && data.result.response && data.request.method === "register") {
        console.log('REGISTER');
        ym(counterId, "reachGoal", "registerSuccess");
    }
})