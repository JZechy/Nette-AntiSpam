# Nette-AntiSpam

[![Latest stable](https://img.shields.io/packagist/v/jzechy/jquery-fileupload.svg?style=flat-square)](https://packagist.org/packages/jzechy/jquery-fileupload)
[![Downloads Total](https://img.shields.io/packagist/dt/jzechy/jquery-fileupload.svg?style=flat-square)](https://packagist.org/packages/jzechy/jquery-fileupload)
[![Open Issues](https://img.shields.io/github/issues/jzechy/jquery-fileupload.svg?style=flat-square)](https://github.com/JZechy/jquery-fileupload/issues)

Nette-AntiSpam slouží jako rozšíření nette formuláře o sadu antispamových prvků a mechanismů, které tiše a neviditelně ochraňují formulář před spamem. Rozšíření používá celkem čtyři metody pro zabránění nežádoucího odeslání formuláře:

### Skrytá pole
Vygenerována jsou dvě pole do formuláře navíc, která jsou ovšem běžnému uživateli skryta pomocí CSS. Jelikož se běžný spambot pokusí vyplnit celý formulář, měl by vyplnit i tato pole.

### Kontrolní otázka
Náhodně vygerovaná a jednoduchá početní úloha. Odpovědní formulář je ovšem automaticky vyplněn JavaScriptem a uživateli skryt. Pokud v uživatelově prohlížeči chybí podpora JS nebo jej má vypnutý, bude požádán o vyplnění.

### Minimální doba čtení příspěvku
Tato metoda předpokládá, že spambot odesílá formulář téměř okamžitě. Lze si tedy nastavit minimální prodlevu ve vteřinách, během které předpokládáme, že uživatel bude příspěvek číst nebo psát odpověď.

### Prodleva mezi příspěvky
Tato prodleva určuje, za jak dlouho může uživatel znova odeslat příspěvěk.

## Composer
```
composer require jzechy/nette-antispam
```

## Instalace
Do vašeho bootstrap.php souboru stačí přidat následující řádku:
```php
Zet\AntiSpam\AntiSpamControl::register($container);
```

## Použití
Registrované rozšíření formuláře lze pak použít následovně:
```php
protected function createComponentForm() {
  $form = new \Nette\Application\UI\Form();

  // Vlastní prvky formuláře ...
  $form->addAntiSpam("spamControl", 120, 60);
}
```
Funkce addAntiSpam příjímá jako první parametr název prvku, druhým parametrem je prodleva ve vteřinách, kdy bude moci uživatel 
znova formulář odeslat a třetím parametrem je minimální doba pro čtení stránky ve vteřinách, kdy se formulář nesmí odeslat.

Jediným povinným parametrem je název prvku.

### Ověření formuláře
Formulář lze ověřit dle hodnoty, kterou prvek vrátí:
```php
$values = $form->getValues();
if($values->spamControl == 0) {
  // Všechny podmínky pro odeslání formuláře byli splněny.
}
```
Pokud budou splněny všechny podmínky pro odeslání formuláře, bude navrácena **0**. Jinak se vrací číselné označení chyby,
které lze testovat proti konstantám ze třídy **Zet\AntiSpam\ErrorType**.

## Konfigurace
### Settery
```php
$antiSpamControl->getQuestionLabelPrototype(); // Vrátí Nette\Utils\Html s definicí labelu pro kontrolní otázku
$antiSpamControl->getQuestionInputPrototype(); // Vrátí Nette\Utils\Html s definicí inputu pro kontrolní otázku.
$antiSpamControl->setHiddenClass("className"); // Vlastní třída pro schování skrytých inputů. Defaultně se vytváří atribut style.
$antiSpamControl->setBlockingTime(5); // Doba, která musí uplynout před dalším odesláním formuláře uživatelem.
$antiSpamControl->setMinimumReadTime(60); // Doba, po kterou nelze odeslat formulář po načtení stránky - bude brán jako odeslán botem.
$antiSpamControl->setNumberString(array(
  "Nula", "Jedna", "Dva", "Tři", "Čtyři", "Pět", "Šest", "Sedm", "Osm", "Devět"
)); // Pole s čísly vyjádřenými jako řetězci. Čísla pro kontrolní otázku se náhodně převádí do řetězců.
$antiSpamControl->setQuestion("Vypočítejte "); // Vlastní začátek kontrolní otázky.
```

### ErrorType Konstanty
```php
class ErrorType extends \Nette\Object {

	/**
	 * Špatný výsledek otázky.
	 * @var int
	 */
	const WRONG_RESULT = 1;

	/**
	 * Jsou vyplněná skrytá pole.
	 * @var int
	 */
	const FILLED_HIDDEN_FIELDS = 2;

	/**
	 * Neuplynula doba, po kterou nemůže uživatel posílat další příspěvěk.
	 * @var int
	 */
	const BLOCKING_TIME = 3;

	/**
	 * Neuplynula doba od načtení, po které může uživatel odeslat formulář.
	 * @var int
	 */
	const MINIMUM_READ_TIME = 4;
}
```
