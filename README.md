# Magento-2.0

- wtyczka w fazie testów
- instrukcja instalacji w przygotowaniu

## 1. Instalacja
Wtyczka może zostać zainstalowana na dwa możliwe sposoby:

a) przy użyciu narzędzia composer: po przejściu do głównego katalogu Magento2 w konsoli systemu operacyjnego należy wpisać komendę:
```
composer require dotpay/magento2 dev-master
```

b) kopiując pliki wtyczki do katalogu:
```
app/code/Dotpay/Dotpay
```
(jeżeli go nie ma, to należy go utworzyć).

## 2. Aktywacja
Przed użyciem wtyczkę należy aktywować z poziomu panelu administracyjnego Magento2:

```
System > Web Setup Wizard > Component Manager
```

Z menu wtyczki należy wybrać opcję
```
Enable
```
a nastepnie przejść wszystkie kroki kreatora.

## 1. Installation
Plugin can be installed in two ways:

a) using composer: when inside Magento2 root folder enter in system command prompt:
```
composer require dotpay/magento2 dev-master
```

b) copy plugin files to:
```
app/code/Dotpay/Dotpay
```
(if it's not present create it).

## 2. Activation
Before using plugin has to be activated in Magento2 administration panel:

```
System > Web Setup Wizard > Component Manager
```

In plugin configuration select
```
Enable
```
and follow installation wizard.
