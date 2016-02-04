# Bitrix Custom Form
![version](https://img.shields.io/badge/version-2.6.0-brightgreen.svg?style=flat-square "Version")
![MIT License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)

Простой компонент, для нестандартных форм на ajax.


## Установка

### Шаг 1
#### a)
Компонент очень удобно устанавливать через composer:
```bash
composer require pafnuty/bitrix-custom-form
```
#### b)
Но можно и вручную, для этого нужно положить файлы и папаки из репозитория в папку `/bitrix/modules/cn.custom.form`. 

### Шаг 2
В админке перейти в раздел `/bitrix/admin/partner_modules.php` и выполнить установку решения **Custom Form (cn.custom.form)**.

## Использование
В нужном месте шаблона прописать вызов компонента:
```php
<?$APPLICATION->IncludeComponent(
    "codenails:cn.custom.form", 
    "", 
    array(),
    false
);?>
