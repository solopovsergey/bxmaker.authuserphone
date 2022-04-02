<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

//файл для модуля bxmaker.api
// запрос на обнволение капчи

/**
 * @var \Bxmaker\Api\Handler $this
 */


if (!\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {
    $this->setError('Не установлен модуль авторизации по номеру телефона', '1');
    return false;
}

$oManagerAuthUserPhone = \BXmaker\AuthUserPhone\Manager::getInstance();


$this->setResult($oManagerAuthUserPhone->captcha()->getForJs());

