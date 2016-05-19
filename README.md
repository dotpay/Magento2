# Magento-2.0
Magento 2.0

- wtyczka w fazie testów
- instrukcja instalacji w przygotowaniu

## 1. Instalacja
Wtyczka może zostać zainstalowana na dwa możliwe sposoby:

a) przy użyciu narzędzia composer: po przejściu do głównego katalogu Magento2 w konsoli systemu operacyjnego należy wpisać komendę:
```
composer require dotpay/dotpay dev-master
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