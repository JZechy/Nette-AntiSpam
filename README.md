# Nette-AntiSpam

Jedná se o antispamovou sadu prvků, která pomáhá chránit formulář pomocí CSS, JS a PHP před spamem bez obtěžování uživatelů Captchou. 
Rozšíření nabízí následující řešení pro zabránění spamu:
* **CSS** - Pole skrytá běžnému uživateli pomocí CSS. Jelikož se typický bot snaží vyplnit celý formulář, pokusí se vyplnit i tyto pole.
* **JS kontrolní otázka** - Náhodně vygenerovaná otázka s jednoduchou početní úlohou. Tato otázka se vyplní javascriptem a následně uživateli skryje. Pokud má uživatel JS vypnutý, bude vyzván k vyplnění tohoto pole.
* **Minimální čas čtení příspěvku** - Lze si nastavit minimální dobu, pro kterou je předpokládáno, že uživatel bude číst příspěvěk po načtení stránky. Pokud bude formulář odeslán před uplynutím této doby, bude vyhodnocen jako odeslaný robotem.
* **Prodleva mezi příspěvky** - Lze též nastavit prodlevu, kdy uživatel může odeslat další příspěvěk.

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
  $form = new \Nette\Application\UI\Form

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
