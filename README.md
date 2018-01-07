# Nette-AntiSpam

[![Latest stable](https://img.shields.io/packagist/v/jzechy/nette-antispam.svg?style=flat-square)](https://packagist.org/packages/jzechy/nette-antispam)
[![license](https://img.shields.io/github/license/jzechy/nette-antispam.svg?maxAge=2592000&style=flat-square)](https://github.com/JZechy/Nette-AntiSpam/blob/master/LICENSE)
[![Downloads Total](https://img.shields.io/packagist/dt/jzechy/nette-antispam.svg?style=flat-square)](https://packagist.org/packages/jzechy/nette-antispam)
[![Open Issues](https://img.shields.io/github/issues/jzechy/nette-antispam.svg?style=flat-square)](https://github.com/JZechy/Nette-AntiSpam/issues)

Nette-AntiSpam slouží jako formulářová komponenta, která pomocí čtyř metod ochrání formulář proti náhodnému spamu.

### Skrytá pole
Do formuláře jsou vygenerována další pole navíc, která jsou před uživatelem skryta JavaScriptem. Pokud
bude nějaké z polí vyplněno, bude odesílající identifikován jako spambot.

Skrytí JavaScriptem se dá nahradit vlastní CSS třídou po skrytí pro případ uživatelů bez JavaScriptu. 

### Časový zámek formuláře
Jelikož spamboti zpravidla odesílají formuláře téměř ihned. Dá se nastavit ve vteřinách doba, pro kterou formulář
zablokován.

### Kontrolní otázka
Náhodně vygenerovaná, jednoduchá početní úloha, kdy čísla jsou náhodně převáděna na řetězce. Tato otázka
je uživateli opět skryta a vyplněna JavaScriptem. Pokud má uživatel JavaScript vypnutý, bude vyzván k vyplnění pole.

Pro tento případ je možné labelu i inputu nastavit vlastní vykreslování.

### Prodleva mezi příspěvky
Tato prodleva určuje, za jak dlouho může uživatel znova odeslat příspěvěk.

## Composer
```
composer require jzechy/nette-antispam
```

## Instalace
Do vašeho config.neon do extensions sekce stačí přidat:
```
antispam: Zet\AntiSpam\AntiSpamExtension
```
### Konfigurace
Komponentu lze nakonfigurovat pomocí následujících nastavení:
* **lockTime** Časový zámek formuláře, během kterého se nesmí odeslat.
* **resendTime** Čas, po kterém uživatel může znova odeslat formulář.
* **numbers** Pole čísel pro náhodný převod na řetězec. Čísla jsou řazena od nuly.
* **question** Znění kontrolní otázky.

## Použití
Registrované rozšíření formuláře lze pak použít následovně:
```php
protected function createComponentForm() {
  $form = new \Nette\Application\UI\Form();

  // Vlastní prvky formuláře ...
  $form->addAntiSpam("spamControl", 5, 60);
}
```
Funkce addAntiSpam příjímá jako první parametr název prvku, tento jediný parametr je povinný.

Dále lze přidat jako druhý parametr zámek formuláře a jako třetí čas, po kterém bude uživatel moci odeslat znova formulář.

### Ověření formuláře
Formulář lze ověřit dle hodnoty, kterou prvek vrátí - Navrací true, pokud odesílatel antispamem prošel nebo false v opačném případě:
```php
$values = $form->getValues();
if($values->spamControl) {
  // Všechny podmínky pro odeslání formuláře byli splněny.
}
```

## Konfigurace
### Settery
```php
$antiSpam->setLockTime(); // Nastaví, kolik vteřin musí uplynout před odesláním formuláře.
$antiSpam->setResendTime(); // Nastaví, kolik vteřin musí uplynout, než je formulář znova odeslán.
$antiSpam->setNumbers(); // Pole čísel vyjádřených slovy pro náhodný převod na řetězec.
$antispam->setQuestion(); // Znění kontrolní otázky.
```

### Gettery
```php
$antispam->getError(); // Kod chyby.
$antispam->getHiddenFields(); // Vrátí generátor skrytých polí. Užitečné pro přepnutí schování z JS na CSS.
$antispam->getQuestionGenerator(); // Vrátí generátor kontrolní otázky s prototypy Labelu a inputu.
```
Pokud budou splněny všechny podmínky pro odeslání formuláře, bude funkcí getError() navrácena **0**. Jinak se vrací číselné označení chyby, které lze testovat proti konstantám ze třídy **Zet\AntiSpam\ErrorType**.

### ErrorType Konstanty
```php
class ErrorType {
	
	use StaticClass;
	
	const NO_ERROR = 0;
	
	const LOCK_TIME = 1;
	
	const RESEND_TIME = 2;
	
	const HIDDEN_FIELDS = 3;
	
	const QUESTION = 4;
}
```
